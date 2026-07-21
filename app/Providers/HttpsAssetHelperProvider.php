<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class HttpsAssetHelperProvider extends ServiceProvider
{
    /**
     * Register the service.
     */
    public function register(): void
    {
        // Load the HTTPS asset helper
        require_once base_path('app/helpers/HttpsAssetHelper.php');
    }

    /**
     * Bootstrap the service - override asset() function to always use HTTPS
     */
    public function boot(): void
    {
        // CRITICAL: Force HTTPS scheme so asset() calls generate HTTPS URLs
        if ($this->app->environment('production') || 
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
            URL::forceScheme('https');
        }
    }
}


