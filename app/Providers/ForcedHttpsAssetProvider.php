<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ForcedHttpsAssetProvider extends ServiceProvider
{
    /**
     * Register the service.
     */
    public function register(): void
    {
        // Nothing to register
    }

    /**
     * Bootstrap the service.
     */
    public function boot(): void
    {
        // This provider is mainly a placeholder for now
        // The actual HTTPS forcing is done in bootstrap/app.php
        // and via the ForceHttpsAssets middleware
    }
}
