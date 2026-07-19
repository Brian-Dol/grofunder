<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\UrlGenerator;

class AssetUrlProvider extends ServiceProvider
{
    /**
     * Register services - runs before boot
     */
    public function register(): void
    {
        // This is too early - Filament hasn't been registered yet
    }

    /**
     * Bootstrap services - runs after all services registered
     */
    public function boot(): void
    {
        // CRITICAL: Force HTTPS on the URL generator AFTER it's been fully initialized
        if ($this->app->environment('production')) {
            $this->forceHttpsOnUrlGenerator();
        }
    }

    /**
     * Force HTTPS on the URL generator by re-binding it with forced HTTPS
     */
    private function forceHttpsOnUrlGenerator(): void
    {
        // Get the current URL generator
        $current = $this->app->make('url');
        
        if ($current instanceof UrlGenerator) {
            // Apply force HTTPS settings
            $current->forceScheme('https');
            $current->forceRootUrl('https://grofunder.onrender.com');
        }
    }
}
