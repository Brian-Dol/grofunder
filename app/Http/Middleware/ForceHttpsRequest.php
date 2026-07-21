<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttpsRequest
{
    /**
     * Handle an incoming request - trust Render's reverse proxy HTTPS headers
     */
    public function handle(Request $request, Closure $next)
    {
        // Render sends X-Forwarded-Proto header for HTTPS
        // The TrustProxies middleware already handles this
        // This middleware just ensures HTTPS URLs are generated in production
        if (app()->environment('production')) {
            app('url')->forceScheme('https');
        }

        return $next($request);
    }
}

