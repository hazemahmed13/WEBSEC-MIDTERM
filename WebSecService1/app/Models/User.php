<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /** @use HasFactory<\Database\Factories\UserFactory> */

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'provider',
        'provider_id',
        'last_password_change',
        'failed_login_attempts',
        'last_failed_login',
        'is_locked',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_password_change' => 'datetime',
        'last_failed_login' => 'datetime',
        'is_locked' => 'boolean',
    ];

    // Add eager loading for commonly accessed relationships
    protected $with = ['credit'];

    public function credit()
    {
        return $this->hasOne(UserCredit::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function getCreditBalance()
    {
        return Cache::remember('user_credit_'.$this->id, 60, function () {
            if (!$this->credit) {
                $this->credit()->create(['credit_balance' => 0]);
                $this->refresh();
            }
            return $this->credit->credit_balance;
        });
    }

    public function addCredit($amount)
    {
        if (!$this->credit) {
            $this->credit()->create(['credit_balance' => 0]);
            $this->refresh();  // Refresh the model to load the new relationship
        }
        return $this->credit->addCredit($amount);
    }

    public function deductCredit($amount)
    {
        if (!$this->credit) {
            return false;
        }
        return $this->credit->deductCredit($amount);
    }

    public function purchaseProduct(Product $product, $quantity)
    {
        if (!$this->hasRole('customer')) {
            return false;
        }

        $totalPrice = $product->price * $quantity;

        if (!$product->isInStock() || $quantity > $product->stock_quantity) {
            return false;
        }

        if ($this->getCreditBalance() < $totalPrice) {
            return false;
        }

        return \DB::transaction(function () use ($product, $quantity, $totalPrice) {
            if (!$product->decreaseStock($quantity)) {
                return false;
            }

            if (!$this->deductCredit($totalPrice)) {
                return false;
            }

            return $this->purchases()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'total_price' => $totalPrice
            ]);
        });
    }

    /**
     * Check if password needs to be changed (older than 90 days)
     */
    public function passwordNeedsChange(): bool
    {
        if (!$this->last_password_change) {
            return true;
        }
        return $this->last_password_change->addDays(90)->isPast();
    }

    /**
     * Update password with security checks
     */
    public function updatePassword(string $newPassword): bool
    {
        // Check password complexity
        if (!$this->isPasswordComplex($newPassword)) {
            return false;
        }

        // Check if password was used before
        if ($this->wasPasswordUsedBefore($newPassword)) {
            return false;
        }

        $this->password = Hash::make($newPassword);
        $this->last_password_change = now();
        return $this->save();
    }

    /**
     * Check password complexity
     */
    protected function isPasswordComplex(string $password): bool
    {
        return strlen($password) >= 12 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password) &&
               preg_match('/[^A-Za-z0-9]/', $password);
    }

    /**
     * Check if password was used before
     */
    protected function wasPasswordUsedBefore(string $password): bool
    {
        // Implementation would require a password_history table
        return false;
    }

    /**
     * Handle failed login attempt
     */
    public function handleFailedLogin(): void
    {
        $this->failed_login_attempts = ($this->failed_login_attempts ?? 0) + 1;
        $this->last_failed_login = now();
        
        if ($this->failed_login_attempts >= 5) {
            $this->is_locked = true;
        }
        
        $this->save();
    }

    /**
     * Reset failed login attempts
     */
    public function resetFailedLoginAttempts(): void
    {
        $this->failed_login_attempts = 0;
        $this->is_locked = false;
        $this->save();
    }

    /**
     * Check if account is locked
     */
    public function isLocked(): bool
    {
        if (!$this->is_locked) {
            return false;
        }

        // Auto unlock after 30 minutes
        if ($this->last_failed_login->addMinutes(30)->isPast()) {
            $this->resetFailedLoginAttempts();
            return false;
        }

        return true;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            if ($user->email === 'admin@example.com') {
                $user->assignRole('admin');
            }
            // Initialize security-related fields
            $user->last_password_change = now();
            $user->failed_login_attempts = 0;
            $user->is_locked = false;
        });
    }
}
