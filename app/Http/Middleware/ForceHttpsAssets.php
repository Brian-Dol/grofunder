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

            // Get the app URL from configuration
            $appUrl = config('app.url');
            
            // Replace all http:// scheme URLs with https:// for production
            if (app()->environment('production') && $appUrl) {
                // Extract domain from APP_URL
                $domain = parse_url($appUrl, PHP_URL_HOST);
                
                if ($domain) {
                    $httpUrl = 'http://' . $domain;
                    $httpsUrl = 'https://' . $domain;
                    
                    // Replace HTTP with HTTPS for this domain
                    $newContent = str_replace($httpUrl, $httpsUrl, $content);
                    
                    // Only update if content actually changed
                    if ($newContent !== $content) {
                        $response->setContent($newContent);
                        $response->header('X-Https-Conversion', 'applied');
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail - don't break the response
        }

        return $response;
    }
}
