<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class SessionController extends Controller
{
    public function updateTimeout(Request $request)
    {
        // Validate input
        $request->validate([
            'timeout' => 'required|integer|min:0'
        ]);
        
        // Get the timeout value (in seconds)
        $timeout = (int)$request->input('timeout');
        $userId = session('user_id');
        
        // Update in session (immediate effect)
        session(['session_timeout' => $timeout]);
        
        // Also update in database (for persistence)
        if ($userId) {
            User::where('id', $userId)->update(['session_timeout' => $timeout]);
        }
        
        // Also update last activity so they don't immediately expire
        session(['last_activity' => time()]);
        
        // Return success message
        $message = 'Session timeout set to ';
        if ($timeout == 0) {
            $message .= 'never expire';
        } elseif ($timeout == 60) {
            $message .= '1 minute';
        } else {
            $message .= round($timeout / 60) . ' minutes';
        }   
        
        return redirect()->back()->with('success', $message);
    }
}