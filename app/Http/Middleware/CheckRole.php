<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $userRole = session('user_role');

        if ($userRole !== $role) {

            // Cashier trying to access admin-only pages
            if ($userRole === 'Cashier') {
                return redirect('/pos')
                    ->with('error', 'Access denied. This section is for administrators only.');
            }

            // Admin accessing cashier-only pages (if any)
            return redirect('/dashboard')
                ->with('error', 'You do not have permission to access that page.');
        }

        return $next($request);
    }
}