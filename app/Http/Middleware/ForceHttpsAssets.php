<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ForceHttpsAssets
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only modify text-based responses
        $contentType = $response->headers->get('content-type', '');
        if (!$this->isTextContent($contentType)) {
            return $response;
        }

        try {
            $content = $response->getContent();
            
            // Only process if we have content
            if (!$content || empty($content)) {
                return $response;
            }

            // Replace all http:// URLs with https:// for the app domain
            // This ensures mixed content is never served
            $appDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'grofunder.onrender.com';
            $content = str_replace(
                "http://{$appDomain}",
                "https://{$appDomain}",
                $content
            );

            // Also handle any remaining http:// URLs in responses
            // Always replace HTTP with HTTPS in production
            if (app()->environment('production')) {
                // Replace href="http:// with href="https://
                $content = preg_replace_callback(
                    '#href="(http://[^"]*)"#i',
                    function($matches) {
                        return 'href="' . str_replace('http://', 'https://', $matches[1]) . '"';
                    },
                    $content
                );
                // Replace src="http:// with src="https://
                $content = preg_replace_callback(
                    '#src="(http://[^"]*)"#i',
                    function($matches) {
                        return 'src="' . str_replace('http://', 'https://', $matches[1]) . '"';
                    },
                    $content
                );
                // Also replace URLs in data attributes and other places
                $content = preg_replace(
                    '#http://([a-z0-9\-._~:/?#\[\]@!$&\'()*+,;=]+)#i',
                    'https://$1',
                    $content
                );
            }

            $response->setContent($content);
        } catch (\Exception $e) {
            // If something goes wrong, just return the response as-is
            // Don't let the middleware break the application
        }

        return $response;
    }

    /**
     * Check if content is text-based and should be processed
     */
    private function isTextContent(string $contentType): bool
    {
        return str_contains($contentType, 'text/html') ||
               str_contains($contentType, 'application/json') ||
               str_contains($contentType, 'text/plain');
    }
}
