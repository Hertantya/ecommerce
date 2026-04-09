<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Override the default RedirectIfAuthenticated so that when a logged-in user
 * tries to visit /login or /register, they are sent to the correct dashboard
 * based on their role (admin → admin dashboard, customer → customer dashboard).
 */
class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // Role-based redirect
                if ($user->role === 'admin') {
                    return redirect()->route('admin.dashboard');
                }

                return redirect()->route('customer.dashboard');
            }
        }

        return $next($request);
    }
}
