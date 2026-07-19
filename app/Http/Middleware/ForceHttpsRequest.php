<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttpsRequest
{
    /**
     * Handle an incoming request - runs BEFORE routing to force HTTPS detection
     */
    public function handle(Request $request, Closure $next)
    {
        // CRITICAL: Force HTTPS detection at the request level BEFORE routing
        if (app()->environment('production')) {
            // Set the request to be secure if we detect reverse proxy HTTPS
            if ($request->header('X-Forwarded-Proto') === 'https') {
                // This tells Laravel the connection is HTTPS
                $request->server->set('HTTPS', 'on');
                $request->server->set('REQUEST_SCHEME', 'https');
                $request->server->set('SERVER_PORT', '443');
            }
            
            // Also ensure the URL generator knows about HTTPS
            if ($request->secure() || $request->header('X-Forwarded-Proto') === 'https') {
                // Force the URL generator to use HTTPS
                app('url')->forceScheme('https');
                app('url')->forceRootUrl('https://grofunder.onrender.com');
            }
        }

        return $next($request);
    }
}
