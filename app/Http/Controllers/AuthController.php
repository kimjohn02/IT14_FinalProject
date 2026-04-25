<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('user_id')) {
            if (session('user_role') === 'Administrator') {
                return redirect('/dashboard');
            } else {
                return redirect('/pos');
            }
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $credentials['username'])
            ->where('is_active', true)
            ->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {

            session([
                'user_id'    => $user->id,
                'user_name'  => $user->f_name . ' ' . $user->l_name,
                'user_role'  => $user->role, 
                'username'   => $user->username,
                'session_timeout' => $user->session_timeout,
            ]);

            if ($user->password_changed == false) {
                session(['show_default_password_modal' => true]);
            }
            $request->replace(['password' => '', 'password_confirmation' => '']);

             $request->request->remove('password');
        $request->request->remove('password_confirmation');
        
            if ($user->role === 'Administrator') {
                return redirect('/dashboard')
                    ->with('success', 'Welcome back, ' . $user->f_name . '!');
            }

            // Cashier
            return redirect('/pos')
                ->with('success', 'Welcome, ' . $user->f_name . '!');
        }

        return back()->with('error', 'Invalid credentials.');
    }

    public function logout()
    {
        session()->flush();
        return redirect('/login')->with('success', 'Logged out successfully.');
    }
}