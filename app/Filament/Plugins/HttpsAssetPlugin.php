<?php

namespace App\Filament\Plugins;

use Filament\Contracts\Plugin;
use Filament\Facade as Filament;

class HttpsAssetPlugin implements Plugin
{
    public function register(): void
    {
        // Register any macros or bindings needed
    }

    public function boot(): void
    {
        // Hook into Filament's asset generation
        // This runs after Filament is loaded
        if (app()->environment('production')) {
            // Force the URL service to use HTTPS
            $url = app('url');
            $url->forceScheme('https');
            $url->forceRootUrl('https://grofunder.onrender.com');
        }
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function provides(): array
    {
        return [];
    }

    public static function resolveUsing(callable $callback): void
    {
        // Not used
    }
}
