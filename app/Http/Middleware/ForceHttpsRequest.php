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
        // Force HTTPS detection at the request level when behind a reverse proxy
        if ($request->header('X-Forwarded-Proto') === 'https') {
            // This tells Laravel the connection is HTTPS
            $request->server->set('HTTPS', 'on');
            $request->server->set('REQUEST_SCHEME', 'https');
            $request->server->set('SERVER_PORT', '443');
            
            // Ensure the URL generator uses HTTPS
            app('url')->forceScheme('https');
        }

        return $next($request);
    }
}
