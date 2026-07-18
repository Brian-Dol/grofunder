<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * M-Pesa Payment Gateway Routes
 * 
 * These routes handle payment processing and webhook callbacks from M-Pesa
 */
Route::prefix('mpesa')->group(function () {
    // Public webhook endpoints (no auth required - called by M-Pesa servers)
    Route::post('/callback', [\App\Http\Controllers\Api\MpesaCallbackController::class, 'handlePaymentCallback'])
        ->name('mpesa.callback');
    
    Route::post('/timeout', [\App\Http\Controllers\Api\MpesaCallbackController::class, 'handleTimeoutCallback'])
        ->name('mpesa.timeout');

    // Authenticated endpoints (agents/admins can query payment status)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/query-status/{checkoutRequestId}', [\App\Http\Controllers\Api\MpesaCallbackController::class, 'queryPaymentStatus'])
            ->name('mpesa.query-status');
    });
});
