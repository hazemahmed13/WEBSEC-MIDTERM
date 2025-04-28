<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserSecurity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return $next($request);
        }

        $user = $request->user();

        // Check if account is locked
        if ($user->isLocked()) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Your account has been locked due to multiple failed login attempts. Please try again after 30 minutes.');
        }

        // Check if password needs to be changed
        if ($user->passwordNeedsChange() && !$request->is('password/*')) {
            return redirect()->route('password.change')
                ->with('warning', 'Your password has expired. Please change it to continue.');
        }

        return $next($request);
    }
} 