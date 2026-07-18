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
        
        // Force HTTPS in production or if request is already HTTPS
        if (app()->environment('production') || request()->secure()) {
            $url = str_replace('http://grofunder.onrender.com', 'https://grofunder.onrender.com', $url);
            $url = preg_replace('#^http://#', 'https://', $url);
        }
        
        return $url;
    }
}
