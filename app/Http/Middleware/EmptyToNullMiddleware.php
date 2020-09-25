<?php

namespace App\Http\Middleware;

use Closure;

class EmptyToNullMiddleware
{
    public function handle($request, Closure $next)
    {
        foreach ($request->input() as $key => $value) {
            if (is_string($value) && $value == "") {
                $request->request->set($key, null);
            }
        }
        return $next($request);
    }
}
