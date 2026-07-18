<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class BladeAssetProvider extends ServiceProvider
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
        // Override Blade's asset() function globally
        Blade::directive('asset', function ($path) {
            return "<?php echo e(app(\App\\Helpers\\AssetHelper::class)->asset($path)); ?>";
        });

        // Also override URL::asset via a macro
        \Illuminate\Support\Facades\URL::macro('assetForce', function ($path = '') {
            $url = \Illuminate\Support\Facades\URL::asset($path);
            // Force HTTPS in production
            if (app()->environment('production')) {
                $url = str_replace('http://', 'https://', $url);
            }
            return $url;
        });
    }
}
