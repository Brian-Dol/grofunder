<?php

namespace App\Helpers;

/**
 * HTTPS Asset URL Helper
 * 
 * Generates asset URLs with guaranteed HTTPS scheme for production deployments.
 * This replaces the standard asset() helper to prevent mixed content errors.
 */
class HttpsAssetHelper
{
    /**
     * Generate HTTPS asset URL
     * 
     * @param string $path The asset path
     * @param bool|null $secure Force HTTPS (true) or HTTP (false)
     * @return string Full HTTPS URL to the asset
     */
    public static function asset($path, $secure = null)
    {
        // Get the base URL from config
        $baseUrl = config('app.asset_url') ?: config('app.url', 'https://grofunder.onrender.com');
        
        // Ensure HTTPS scheme
        $baseUrl = self::ensureHttps($baseUrl);
        
        // Build the full asset URL
        $url = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        
        // Final HTTPS check
        return self::ensureHttps($url);
    }

    /**
     * Generate HTTPS URL
     * 
     * @param string|null $path The URL path
     * @param array $parameters Query parameters
     * @param bool|null $secure Force HTTPS (true) or HTTP (false)
     * @return string Full HTTPS URL
     */
    public static function url($path = null, $parameters = [], $secure = null)
    {
        $baseUrl = config('app.url', 'https://grofunder.onrender.com');
        
        // Ensure HTTPS
        $baseUrl = self::ensureHttps($baseUrl);
        
        if ($path === null) {
            return $baseUrl;
        }
        
        $url = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        
        // Final HTTPS check
        return self::ensureHttps($url);
    }

    /**
     * Ensure URL uses HTTPS scheme
     * 
     * @param string $url The URL to check
     * @return string URL with HTTPS scheme
     */
    private static function ensureHttps($url)
    {
        // Replace http:// with https://
        if (strpos($url, 'http://') === 0) {
            return 'https://' . substr($url, 7);
        }
        
        // If no scheme, add https://
        if (strpos($url, '://') === false) {
            return 'https://' . $url;
        }
        
        return $url;
    }
}

/**
 * Global helper function for HTTPS asset URLs
 * 
 * Usage in Blade: {{ asset_https('css/app.css') }}
 */
if (!function_exists('asset_https')) {
    function asset_https($path, $secure = null)
    {
        return \App\Helpers\HttpsAssetHelper::asset($path, $secure);
    }
}

/**
 * Global helper function for HTTPS URLs
 * 
 * Usage in Blade: {{ url_https('/dashboard') }}
 */
if (!function_exists('url_https')) {
    function url_https($path = null, $parameters = [], $secure = null)
    {
        return \App\Helpers\HttpsAssetHelper::url($path, $parameters, $secure);
    }
}
