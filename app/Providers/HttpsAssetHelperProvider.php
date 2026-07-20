<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
     * Bootstrap the service.
     */
    public function boot(): void
    {
        // Nothing to bootstrap
    }
}

