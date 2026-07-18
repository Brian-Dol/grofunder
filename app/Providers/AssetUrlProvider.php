<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AssetUrlProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Override the global asset() function to force HTTPS
        $this->registerAssetMacro();
    }

    /**
     * Register the asset macro to force HTTPS URLs
     */
    private function registerAssetMacro(): void
    {
        if (! function_exists('asset_https')) {
            function asset_https($path = '') {
                $url = \Illuminate\Support\Facades\URL::asset($path);
                
                // Force HTTPS in production or if request is secure
                if (app()->environment('production') || \request()->secure()) {
                    $url = str_replace('http://', 'https://', $url);
                }
                
                return $url;
            }
        }
    }
}
