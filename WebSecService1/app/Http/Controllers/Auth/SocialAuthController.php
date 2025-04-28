<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SocialAuthController extends Controller
{
    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            
            $user = User::where([
                'provider' => $provider,
                'provider_id' => $socialUser->getId()
            ])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'password' => bcrypt(Str::random(16)),
                    'email_verified_at' => now() // Since social login is verified by the provider
                ]);
            }

            Auth::login($user);
            return redirect('/products')->with('success', 'Successfully logged in!');

        } catch (\Exception $e) {
            Log::error('Social login error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Something went wrong with social login. Please try again.');
        }
    }
} 