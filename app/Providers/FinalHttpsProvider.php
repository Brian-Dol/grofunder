<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\UrlGenerator;

class FinalHttpsProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services - runs after all other providers
     */
    public function boot(): void
    {
        // This runs AFTER Filament and other packages have initialized
        // So we can safely patch the URL generator that's already in use
        
        if ($this->app->environment('production')) {
            // Get the already-instantiated URL generator
            $urlGenerator = $this->app['url'];
            
            if ($urlGenerator instanceof UrlGenerator) {
                // Force scheme to HTTPS
                $urlGenerator->forceScheme('https');
                
                // Force the root URL to HTTPS
                $urlGenerator->forceRootUrl('https://grofunder.onrender.com');
            }
            
            // Also ensure config values are correct
            config([
                'app.url' => 'https://grofunder.onrender.com',
                'app.asset_url' => 'https://grofunder.onrender.com',
            ]);
        }
    }
}
