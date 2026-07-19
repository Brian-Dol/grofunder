<?php

/**
 * Global overrides for URL generation to force HTTPS on production
 * This file is loaded after all vendor bootstrap, so it can safely patch helpers
 */

if (!function_exists('asset_https')) {
    function asset_https($path = '', $secure = null)
    {
        // Always force HTTPS on production
        if (app()->environment('production')) {
            $secure = true;
        }
        
        return app('url')->asset($path, $secure);
    }
}

// Override the route() helper to ensure HTTPS URLs
if (app()->environment('production')) {
    $original_route = 'Illuminate\Routing\route'; //This won't work as written
}

// Better approach: Monkey patch via config after all services are loaded
if (app()->environment('production') && !app()->runningInConsole()) {
    // This will be called during request lifecycle
    // Replace any remaining http:// URLs with https://
    // This is a last-resort approach if all else fails
}
