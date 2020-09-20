<?php

namespace App\Http\Middleware;

use Closure;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission)
    {
        if (!$request->user()->hasPermission($permission)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
