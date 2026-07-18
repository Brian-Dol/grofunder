<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

Route::get('/debug-config', function () {
    return [
        'APP_URL' => config('app.url'),
        'APP_ENV' => config('app.env'),
        'APP_DEBUG' => config('app.debug'),
        'URL_scheme' => URL::getRootUrl(),
        'asset_url' => asset('css/app.css'),
        'HTTPS' => $_SERVER['HTTPS'] ?? 'not set',
        'HTTP_X_FORWARDED_PROTO' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'not set',
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'not set',
        'REQUEST_SCHEME' => $_SERVER['REQUEST_SCHEME'] ?? 'not set',
        'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'not set',
    ];
});
