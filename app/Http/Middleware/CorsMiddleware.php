<?php

namespace App\Http\Middleware;

use Closure;

// Taken from https://www.codementor.io/@chiemelachinedum/steps-to-enable-cors-on-a-lumen-api-backend-e5a0s1ecx

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        $headers = [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With'
        ];

        if($request->isMethod('OPTIONS')) {
            return response()->json(['method' => 'OPTIONS'], 200, $headers);
        }

        $response = $next($request);

        foreach($headers as $key => $val) {
            $response->header($key, $val);
        }

        return $response;
        // if ($request->user()) {
        //     return response()->json(['message' => 'Already logged in'], 401);
        // }
        // return $next($request);
    }
}
