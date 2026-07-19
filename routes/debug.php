<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

Route::get('/debug-config', function () {
    return [
        'environment_vars' => [
            'APP_ENV' => env('APP_ENV'),
            'APP_URL' => env('APP_URL'),
            'ASSET_URL' => env('ASSET_URL'),
            'APP_DEBUG' => env('APP_DEBUG'),
            'TRUSTED_PROXIES' => env('TRUSTED_PROXIES'),
        ],
        'config_values' => [
            'app.env' => config('app.env'),
            'app.url' => config('app.url'),
            'app.asset_url' => config('app.asset_url'),
            'app.debug' => config('app.debug'),
        ],
        'url_helpers' => [
            'URL::to("test")' => URL::to('test'),
            'URL::asset("css/test.css")' => URL::asset('css/test.css'),
            'asset("css/test.css")' => asset('css/test.css'),
            'URL::getScheme()' => URL::getScheme(),
            'URL::getRootUrl()' => URL::getRootUrl(),
        ],
        'server_variables' => [
            'HTTPS' => $_SERVER['HTTPS'] ?? 'not set',
            'REQUEST_SCHEME' => $_SERVER['REQUEST_SCHEME'] ?? 'not set',
            'SERVER_PORT' => $_SERVER['SERVER_PORT'] ?? 'not set',
            'X-Forwarded-Proto' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'not set',
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'not set',
        ],
        'request_helpers' => [
            'request()->secure()' => request()->secure() ? 'true' : 'false',
            'request()->getScheme()' => request()->getScheme(),
            'request()->getRootUrl()' => request()->getRootUrl(),
        ],
    ];
});
