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

        // Get the content type
        $contentType = $response->headers->get('content-type', '');
        
        // Only modify HTML/JSON responses
        if (strpos($contentType, 'text/html') === false && 
            strpos($contentType, 'application/json') === false) {
            return $response;
        }

        try {
            $content = $response->getContent();
            
            // Make sure we have content
            if (empty($content)) {
                return $response;
            }

            // SIMPLE AND EFFECTIVE: Replace all http://grofunder with https://grofunder
            // This is the most direct approach that will always work
            $appDomain = 'grofunder.onrender.com';
            
            // Direct string replacement for the specific domain
            $content = str_replace(
                "http://{$appDomain}",
                "https://{$appDomain}",
                $content
            );
            
            // Also catch any other domain variations
            $content = preg_replace(
                '/http:\/\/([a-z0-9\-\.]+\.)?grofunder\.onrender\.com/',
                'https://${1}grofunder.onrender.com',
                $content
            );

            // Set the modified content
            $response->setContent($content);
        } catch (\Exception $e) {
            // If something fails, just return original response
            return $response;
        }

        return $response;
    }
}
