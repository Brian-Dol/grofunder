<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Force HTTPS scheme during boot phase - before any views are compiled
        if (!app()->runningInConsole()) {
            // Determine if this is an HTTPS request
            $isHttps = $this->isHttpsRequest();
            
            if ($isHttps) {
                // Force all URL generation to use HTTPS scheme
                URL::forceScheme('https');
            }
        }
    }

    /**
     * Determine if the current request is over HTTPS
     */
    private function isHttpsRequest(): bool
    {
        // Check various indicators of HTTPS
        return (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
               (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               (app()->environment('production'));
    }
}
