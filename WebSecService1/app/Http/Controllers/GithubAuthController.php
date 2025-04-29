<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GithubAuthController extends Controller
{
    public function redirect()
    {
        try {
            return Socialite::driver('github')->redirect();
        } catch (Exception $e) {
            Log::error('GitHub redirect error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Could not connect to GitHub.');
        }
    }

    public function callback()
    {
        try {
            $githubUser = Socialite::driver('github')->user();
            
            if (!$githubUser->email) {
                return redirect()->route('login')
                    ->with('error', 'No email address provided from GitHub. Please make sure your GitHub email is public.');
            }
            
            $user = User::updateOrCreate(
                ['email' => $githubUser->email],
                [
                    'name' => $githubUser->name ?? $githubUser->nickname,
                    'password' => bcrypt(Str::random(16)),
                    'email_verified_at' => now(),
                    'provider' => 'github',
                    'provider_id' => $githubUser->id,
                ]
            );
            
            Auth::login($user);
            
            Log::info('User logged in via GitHub: ' . $user->email);
            
            return redirect()->route('products.index');
            
        } catch (Exception $e) {
            Log::error('GitHub callback error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Something went wrong with GitHub authentication. Please try again.');
        }
    }
} 