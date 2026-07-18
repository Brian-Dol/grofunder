<?php

return [
    /*
    |--------------------------------------------------------------------------
    | M-Pesa Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for M-Pesa API integration for Growfunder
    |
    */

    // Environment: 'sandbox' or 'production'
    'environment' => env('MPESA_ENV', 'sandbox'),

    // Sandbox API endpoints
    'sandbox' => [
        'auth_url' => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
        'base_url' => 'https://sandbox.safaricom.co.ke/mpesa',
    ],

    // Production API endpoints
    'production' => [
        'auth_url' => 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
        'base_url' => 'https://api.safaricom.co.ke/mpesa',
    ],

    // M-Pesa Consumer Key (from Safaricom developer portal)
    'consumer_key' => env('MPESA_CONSUMER_KEY', ''),

    // M-Pesa Consumer Secret (from Safaricom developer portal)
    'consumer_secret' => env('MPESA_CONSUMER_SECRET', ''),

    // M-Pesa Short Code (business code)
    'short_code' => env('MPESA_SHORT_CODE', ''),

    // M-Pesa Passkey (for STK Push)
    'passkey' => env('MPESA_PASSKEY', ''),

    // M-Pesa Username (for API calls)
    'username' => env('MPESA_USERNAME', ''),

    // M-Pesa Password (for API calls)
    'password' => env('MPESA_PASSWORD', ''),

    // Callback URL for payment confirmations
    'callback_url' => env('MPESA_CALLBACK_URL', env('APP_URL') . '/api/mpesa/callback'),

    // Timeout URL for incomplete transactions
    'timeout_url' => env('MPESA_TIMEOUT_URL', env('APP_URL') . '/api/mpesa/timeout'),

    // Transaction description
    'transaction_description' => env('MPESA_TRANSACTION_DESC', 'Growfunder Loan Repayment'),

    // Account reference (merchant identifier)
    'account_reference' => env('MPESA_ACCOUNT_REF', 'Growfunder'),

    // Enable logging of M-Pesa requests/responses
    'log_requests' => env('MPESA_LOG_REQUESTS', true),
];
