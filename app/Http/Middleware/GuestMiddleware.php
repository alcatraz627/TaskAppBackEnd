<?php

namespace App\Http\Middleware;

use Closure;

class GuestMiddleware
{
    public function handle($request, Closure $next)
    {
        if ($request->user()) {
            return response()->json(['message' => 'Already logged in'], 401);
        }
        return $next($request);
    }
}
