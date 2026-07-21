<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;

/**
 * HttpsAssetUrlProvider ensures all asset URLs are HTTPS on production.
 * 
 * This provider patches the URL generator to force HTTPS scheme and
 * registers Blade macros for guaranteed HTTPS URLs.
 */
class HttpsAssetUrlProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // In production environment with Render proxy, force HTTPS early
        if (app()->environment('production')) {
            $this->app['url.generator']->forceScheme('https');
            $this->app['url.generator']->forceRootUrl(config('app.url'));
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production with Render proxy
        if (app()->environment('production')) {
            // Re-force in case it was overridden
            URL::forceScheme('https');
            URL::forceRootUrl(config('app.url'));
            
            // Patch the URL generator to ensure asset() generates HTTPS
            $this->patchAssetUrls();
        }
    }

    /**
     * Patch asset URL generation to ensure HTTPS
     */
    private function patchAssetUrls(): void
    {
        // Override the asset helper in the view environment
        if ($this->app->bound('view')) {
            $this->app['view']->addNamespace('https-assets', __DIR__);
        }

        // Register Blade directives for HTTPS URLs
        Blade::macro('httpsAsset', function ($path) {
            $url = URL::asset($path);
            // Ensure HTTPS
            if (strpos($url, 'http://') === 0) {
                $url = 'https://' . substr($url, 7);
            }
            return $url;
        });

        Blade::macro('httpsUrl', function ($path) {
            $url = URL::to($path);
            // Ensure HTTPS
            if (strpos($url, 'http://') === 0) {
                $url = 'https://' . substr($url, 7);
            }
            return $url;
        });
    }
}

