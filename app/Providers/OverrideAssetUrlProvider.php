<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class OverrideAssetUrlProvider extends ServiceProvider
{
    /**
     * Register services - runs FIRST, before anything accesses the URL service
     */
    public function register(): void
    {
        // Override the URL service binding before Filament or anything else uses it
        $this->app->bind('url', function ($app) {
            $url = new \Illuminate\Routing\UrlGenerator(
                $app['router']->getRoutes(),
                $app->make('request'),
            );

            // Always force HTTPS on production
            if ($app->environment('production')) {
                $url->forceScheme('https');
                $url->forceRootUrl(config('app.url'));
            }

            return $url;
        });
    }

    /**
     * Bootstrap services - ensure HTTPS is still forced after all providers load
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            // Get and re-configure the URL generator after all providers have loaded
            $url = $this->app->make('url');
            $url->forceScheme('https');
            $url->forceRootUrl(config('app.url'));
        }
    }
}
