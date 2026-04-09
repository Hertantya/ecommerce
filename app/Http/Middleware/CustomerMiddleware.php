<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CustomerMiddleware
 * Blocks access to customer routes if the user is not a customer.
 */
class CustomerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()->role !== 'customer') {
            abort(403, 'Access denied. Customers only.');
        }

        return $next($request);
    }
}
