<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AccountSettingsController extends Controller
{
    public function edit()
    {
        // Get user ID from session
        $userId = session('user_id');
        
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please log in to access account settings.');
        }
        
        // Get user with role relationship
        $user = User::find($userId);
        
        if (!$user) {
            session()->flush();
            return redirect()->route('login')->with('error', 'User not found. Please log in again.');
        }
        
        return view('account.settings', compact('user'));
    }

    public function update(Request $request)
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please log in to update your profile.');
        }

        $user = User::find($userId);
        
        if (!$user) {
            session()->flush();
            return redirect()->route('login')->with('error', 'User not found. Please log in again.');
        }

        try {
            $request->validate([
                'f_name' => 'required|string|max:100',
                'm_name' => 'nullable|string|max:100',
                'l_name' => 'required|string|max:100',
                'contactNo' => 'nullable|string|max:11|regex:/^[0-9]{0,11}$/',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            ]);

            $updateData = [
                'f_name' => ucwords(strtolower($request->f_name)),
                'm_name' => $request->m_name ? ucwords(strtolower($request->m_name)) : null,
                'l_name' => ucwords(strtolower($request->l_name)),
                'contactNo' => $request->contactNo,
            ];

            // If email is changing, mark it as unverified
            if ($request->email !== $user->email) {
                $updateData['email'] = $request->email;
                $updateData['email_verified_at'] = null;
                // Here you would typically send a verification email
            }

            $user->update($updateData);

            // Update session name if first or last name changed
            if ($request->f_name !== $user->f_name || $request->l_name !== $user->l_name) {
                session(['user_name' => $request->f_name . ' ' . $request->l_name]);
            }

            return redirect()->route('account.settings')->with('success', 'Profile updated successfully.' . ($request->email !== $user->email ? ' Please verify your new email address.' : ''));
            
        } catch (\Exception $e) {
            return redirect()->route('account.settings')->with('error', 'Error updating profile: ' . $e->getMessage());
        }
    }

    public function updatePassword(Request $request)
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please log in to update your password.');
        }

        $user = User::find($userId);
        
        if (!$user) {
            session()->flush();
            return redirect()->route('login')->with('error', 'User not found. Please log in again.');
        }

        try {
            $request->validate([
                'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('The current password is incorrect.');
                    }
                }],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $user->update([
                'password' => Hash::make($request->password),
                'password_changed' => true, 
            ]);

            return redirect()->route('account.settings')->with('success', 'Password updated successfully.');
            
        } catch (\Exception $e) {
            return redirect()->route('account.settings')->with('error', 'Error updating password: ' . $e->getMessage());
        }
    }
}