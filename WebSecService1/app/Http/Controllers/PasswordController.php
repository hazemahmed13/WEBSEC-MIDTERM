<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UpdatePasswordRequest;

class PasswordController extends Controller
{
    public function showChangeForm()
    {
        return view('auth.passwords.change');
    }

    public function update(UpdatePasswordRequest $request)
    {
        $user = auth()->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
        }

        // Update password with security checks
        if (!$user->updatePassword($request->password)) {
            return back()->withErrors(['password' => 'The password does not meet the security requirements.']);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Password has been updated successfully.');
    }
} 