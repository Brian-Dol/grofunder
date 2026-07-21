<?php

namespace App\Helpers;

use Illuminate\Support\Facades\URL;

class AssetHelper
{
    /**
     * Override the asset() helper to force HTTPS URLs
     */
    public static function asset(string $path = ''): string
    {
        $url = \Illuminate\Support\Facades\URL::asset($path);
        
        // Laravel should handle HTTPS with config + TrustProxies middleware
        // No need to hardcode domain or force replace
        return $url;
    }
}
