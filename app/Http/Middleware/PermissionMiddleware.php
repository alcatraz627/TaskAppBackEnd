<?php

namespace App\Http\Middleware;

use Closure;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission)
    {
        // error_log("Checking user: " . $request->user()->id . "For permission: " . $permission);
        if (!$request->user()->hasPermission($permission)) {
            return response()->json(['message' => 'Unauthorized', 'type' => 'ERROR'], 401);
        }
        return $next($request);
    }
}
