<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class HttpsUrlProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            // Also update the app URL config
            $appUrl = config('app.url');
            if ($appUrl && strpos($appUrl, 'http://') === 0) {
                Config::set('app.url', 'https://' . substr($appUrl, 7));
            }
        }
    }
}
