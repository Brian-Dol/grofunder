<?php

use Illuminate\Support\Facades\Route;

Route::get('/diagnostic', function () {
    return response()->json([
        'APP_URL' => config('app.url'),
        'APP_ENV' => config('app.env'),
        'APP_DEBUG' => config('app.debug'),
        'REQUEST_SCHEME' => request()->getScheme(),
        'REQUEST_HOST' => request()->getHost(),
        'HTTP_HOST' => request()->getHttpHost(),
        'REQUEST_URI' => request()->getRequestUri(),
        'X_FORWARDED_PROTO' => request()->header('X-Forwarded-Proto'),
        'X_FORWARDED_HOST' => request()->header('X-Forwarded-Host'),
        'X_FORWARDED_PORT' => request()->header('X-Forwarded-Port'),
        'HTTPS' => $_SERVER['HTTPS'] ?? 'not-set',
        'SERVER_PROTOCOL' => $_SERVER['SERVER_PROTOCOL'] ?? 'not-set',
        'ROUTE_URL_TEST' => route('filament.admin.auth.register', [], false),
    ]);
});
