<?php

// Override the global asset() function to force HTTPS
if (!function_exists('asset_https')) {
    function asset_https($path = '')
    {
        $url = app('url')->asset($path);
        
        // Force HTTPS in production
        if (app()->environment('production')) {
            $url = str_replace('http://', 'https://', $url);
        }
        
        return $url;
    }
}

// Most importantly: Override the GLOBAL asset() function at the very beginning
if (!function_exists('asset_original')) {
    // Save the original if it exists
    if (function_exists('asset')) {
        // We can't directly override built-in helpers, but we can use a service provider
    }
}
