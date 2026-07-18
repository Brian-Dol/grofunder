<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttpsAssets
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only modify HTML responses
        if ($response->headers->get('content-type') && str_contains($response->headers->get('content-type'), 'text/html')) {
            // Get the response content
            $content = $response->getContent();

            // Replace http://grofunder.onrender.com with https://grofunder.onrender.com in the response
            $content = str_replace(
                'http://grofunder.onrender.com',
                'https://grofunder.onrender.com',
                $content
            );

            // Also handle dynamic domain replacement for any domain
            if ($request->getHost() !== 'localhost' && $request->getHost() !== '127.0.0.1') {
                $content = preg_replace(
                    '/http:\/\/(' . preg_quote($request->getHost(), '/') . ')/i',
                    'https://$1',
                    $content
                );
            }

            $response->setContent($content);
        }

        return $response;
    }
}
