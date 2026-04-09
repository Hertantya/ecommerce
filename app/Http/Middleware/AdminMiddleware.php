<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AdminMiddleware
 * Blocks access to admin routes if the user is not an admin.
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // auth()->check() is already guaranteed by the 'auth' middleware
        // applied before this one in the route group.
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Admins only.');
        }

        return $next($request);
    }
}
