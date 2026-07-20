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

        // Add a debug header to prove the middleware ran
        $response->header('X-Force-Https-Assets', 'middleware-ran');

        // Get the content type
        $contentType = $response->headers->get('content-type', '');
        
        // Only modify HTML responses (not JSON, not images, etc)
        if (strpos($contentType, 'text/html') === false) {
            return $response;
        }

        try {
            // Get the response content
            $content = (string) $response->getContent();
            
            // Make sure we have content
            if (strlen($content) === 0) {
                return $response;
            }

            // Check if there are HTTP URLs to fix
            if (strpos($content, 'http://grofunder.onrender.com') !== false) {
                // Replace all http://grofunder.onrender.com with https://
                $newContent = str_replace(
                    'http://grofunder.onrender.com',
                    'https://grofunder.onrender.com',
                    $content
                );
                
                // Only update if content actually changed
                if ($newContent !== $content) {
                    $response->setContent($newContent);
                    $response->header('X-Https-Conversion', 'applied');
                }
            }
        } catch (\Exception $e) {
            // Silently fail - don't break the response
        }

        return $response;
    }
}
