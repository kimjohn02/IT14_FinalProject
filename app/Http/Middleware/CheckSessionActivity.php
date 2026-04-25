<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log; 

class CheckSessionActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('=== CHECK SESSION MIDDLEWARE RUNNING ===');
    
        // Get timeout from session or use default (10 minutes)
        $timeout = session('session_timeout', 10 * 60); // Default 10 minutes
        
        Log::info("Session timeout setting: {$timeout} seconds (" . ($timeout/60) . " minutes)");
    
        // If timeout is 0, disable session expiration
        if ($timeout === 0) {
            Log::info('=== SESSION TIMEOUT DISABLED BY USER ===');
            session(['last_activity' => time()]);
            return $next($request);
        }
        
        $lastActivity = session('last_activity');
        
        if ($lastActivity && (time() - $lastActivity > $timeout)) {
            Log::info('=== EXPIRING SESSION ===');
            Log::info("Timeout: {$timeout}s, Last activity: {$lastActivity}, Current: " . time());
            session()->flush();
            return redirect('/login')->with('message', 'Session expired due to inactivity.');
        }
        
        // Update last activity time
        session(['last_activity' => time()]);
        
        return $next($request);
    }
}