<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Routing\UrlGenerator;

class HttpsUrlProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            // Method 1: Force scheme via URL facade
            URL::forceScheme('https');
            
            // Method 2: Update app config directly
            $appUrl = config('app.url');
            
            // Check if APP_URL is already correct
            if ($appUrl && strpos($appUrl, 'http://') === 0) {
                $httpsUrl = 'https://' . substr($appUrl, 7);
                Config::set('app.url', $httpsUrl);
                URL::forceRootUrl($httpsUrl);
            } else if (!$appUrl || strpos($appUrl, 'https://') !== 0) {
                // Fallback to default if not set properly
                $defaultUrl = 'https://grofunder.onrender.com';
                Config::set('app.url', $defaultUrl);
                URL::forceRootUrl($defaultUrl);
            }
            
            // Method 3: Macro to fix asset() calls
            URL::macro('assetWithHttps', function ($path = '') {
                $url = URL::asset($path);
                return str_replace('http://', 'https://', $url);
            });
        }
    }
}

