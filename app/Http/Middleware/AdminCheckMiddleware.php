<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated
        if ($request->user() && $request->user()->role == 1) {
            // User is admin, allow the request to continue
            return $next($request);
        }

        // User is not admin, abort the request with unauthorized status
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}