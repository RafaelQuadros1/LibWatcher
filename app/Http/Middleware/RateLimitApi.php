<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RateLimitApi
{
    public function handle(Request $request, Closure $next, $maxRequests = 60)
    {
        $key = 'rate_limit:' . $request->ip();
        $requests = Cache::get($key, 0);
        
        if ($requests >= $maxRequests) {
            return response()->json([
                'success' => false,
                'message' => 'Rate limit excedido. Tente novamente em 1 minuto.'
            ], 429);
        }
        
        Cache::put($key, $requests + 1, 60);
        
        return $next($request);
    }
}