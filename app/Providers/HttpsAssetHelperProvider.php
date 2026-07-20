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
        // Load and register the HTTPS helper functions early
        $this->registerHttpsAssetHelper();
    }

    /**
     * Bootstrap the service.
     */
    public function boot(): void
    {
        // Nothing to bootstrap
    }

    /**
     * Register HTTPS asset helper functions
     */
    private function registerHttpsAssetHelper(): void
    {
        // Load the HttpsAssetHelper class and global functions
        require_once base_path('app/helpers/HttpsAssetHelper.php');
    }
}
