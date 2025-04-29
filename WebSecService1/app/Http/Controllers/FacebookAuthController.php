<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class FacebookAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function callback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();
            
            $user = User::where('email', $facebookUser->email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $facebookUser->name,
                    'email' => $facebookUser->email,
                    'password' => bcrypt(Str::random(24)), // Generate a random password
                    'provider' => 'facebook',
                    'provider_id' => $facebookUser->id,
                    'email_verified_at' => now(), // Facebook users are automatically verified
                ]);

                // Assign customer role by default
                $user->assignRole('customer');

                // Create initial credit balance
                $user->credit()->create(['credit_balance' => 0]);
            }

            Auth::login($user);

            return redirect()->route('products.index')->with('success', 'Logged in successfully with Facebook!');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Failed to login with Facebook. Please try again.');
        }
    }
} 