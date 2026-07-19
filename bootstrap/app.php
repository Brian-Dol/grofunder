<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

// CRITICAL: Force HTTPS configuration at the earliest possible point
// This runs BEFORE service providers and after the app is created
if (getenv('APP_ENV') === 'production' || getenv('FORCE_HTTPS') === '1') {
    $_SERVER['HTTPS'] = 'on';
    $_ENV['APP_URL'] = $_ENV['APP_URL'] ?? 'https://grofunder.onrender.com';
    $_ENV['ASSET_URL'] = $_ENV['ASSET_URL'] ?? 'https://grofunder.onrender.com';
    
    // Ensure REQUEST_SCHEME is detected correctly
    if (!isset($_SERVER['REQUEST_SCHEME'])) {
        $_SERVER['REQUEST_SCHEME'] = 'https';
        $_SERVER['SERVER_PORT'] = '443';
    }
    
    // If reverse proxy headers indicate HTTPS, set them
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['REQUEST_SCHEME'] = 'https';
        $_SERVER['SERVER_PORT'] = '443';
    }
}

return $app;
