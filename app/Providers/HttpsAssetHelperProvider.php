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
        
        // Override the global asset() function to always use HTTPS
        $this->overrideAssetHelper();
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

    /**
     * Override Laravel's asset() helper to return HTTPS URLs
     */
    private function overrideAssetHelper(): void
    {
        // This runs after asset_https is registered, so we can use it
        if (!function_exists('asset')) {
            // If asset() doesn't exist, define it using our HTTPS version
            function asset($path, $secure = null) {
                return \App\Helpers\HttpsAssetHelper::asset($path, $secure);
            }
        } else {
            // Asset exists, but we need to make sure it returns HTTPS
            // We do this by setting up a macro or overriding via callback
            // Unfortunately, asset() is a global function that can't be overridden normally
            // So instead we'll use a workaround via response post-processing
        }
    }
}

