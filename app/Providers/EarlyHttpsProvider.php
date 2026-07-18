<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class EarlyHttpsProvider extends ServiceProvider
{
    /**
     * Register services - runs BEFORE other services boot
     */
    public function register(): void
    {
        // Force HTTPS as early as possible
        if ($this->app->environment('production')) {
            // Override the URL generator to always use HTTPS
            $this->app->singleton('url', function ($app) {
                $routes = $app['router']->getRoutes();
                
                // Create the URL generator instance
                $urlGenerator = new \Illuminate\Routing\UrlGenerator(
                    $routes,
                    $app->make('request'),
                );
                
                // Force HTTPS scheme
                $urlGenerator->forceScheme('https');
                
                // Force the root URL
                $appUrl = 'https://grofunder.onrender.com';
                $urlGenerator->forceRootUrl($appUrl);
                
                // Set it back in the container for consistency
                $app['url'] = $urlGenerator;
                
                return $urlGenerator;
            });
            
            // Also configure the app URL early
            Config::set('app.url', 'https://grofunder.onrender.com');
            Config::set('app.asset_url', 'https://grofunder.onrender.com');
        }
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Additional confirmation in boot phase
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            URL::forceRootUrl('https://grofunder.onrender.com');
        }
    }
}
