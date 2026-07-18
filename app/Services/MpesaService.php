<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class MpesaService
{
    private $environment;
    private $authUrl;
    private $baseUrl;
    private $consumerKey;
    private $consumerSecret;
    private $accessToken;

    public function __construct()
    {
        $this->environment = config('mpesa.environment');
        $config = $this->environment === 'production' 
            ? config('mpesa.production')
            : config('mpesa.sandbox');

        $this->authUrl = $config['auth_url'];
        $this->baseUrl = $config['base_url'];
        $this->consumerKey = config('mpesa.consumer_key');
        $this->consumerSecret = config('mpesa.consumer_secret');
    }

    /**
     * Generate M-Pesa access token
     */
    public function generateAccessToken(): string
    {
        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get($this->authUrl);

            if ($response->failed()) {
                throw new Exception('Failed to generate M-Pesa access token: ' . $response->body());
            }

            $this->accessToken = $response->json('access_token');
            return $this->accessToken;
        } catch (Exception $e) {
            Log::error('M-Pesa Access Token Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Initiate STK Push (payment request popup)
     * 
     * @param string $phoneNumber Customer phone number (format: 254701234567)
     * @param float $amount Amount to charge
     * @param string $accountRef Account reference/description
     * @return array M-Pesa response
     */
    public function initiateSTKPush(string $phoneNumber, float $amount, string $accountRef = null): array
    {
        try {
            // Ensure phone number is in correct format
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);
            $accountRef = $accountRef ?? config('mpesa.account_reference');

            $token = $this->generateAccessToken();
            $timestamp = now()->format('YmdHis');
            $shortCode = config('mpesa.short_code');
            $passkey = config('mpesa.passkey');

            // Generate password
            $password = base64_encode($shortCode . $passkey . $timestamp);

            $payload = [
                'BusinessShortCode' => $shortCode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => (int)$amount,
                'PartyA' => $phoneNumber,
                'PartyB' => $shortCode,
                'PhoneNumber' => $phoneNumber,
                'CallBackURL' => config('mpesa.callback_url'),
                'AccountReference' => $accountRef,
                'TransactionDesc' => config('mpesa.transaction_description'),
            ];

            if (config('mpesa.log_requests')) {
                Log::info('M-Pesa STK Push Request', ['payload' => $payload]);
            }

            $response = Http::withToken($token)
                ->post($this->baseUrl . '/stkpush/v1/processrequest', $payload);

            if (config('mpesa.log_requests')) {
                Log::info('M-Pesa STK Push Response', ['response' => $response->json()]);
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('M-Pesa STK Push Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Query payment status
     * 
     * @param string $checkoutRequestId Checkout request ID from STK Push
     * @return array M-Pesa response
     */
    public function queryPaymentStatus(string $checkoutRequestId): array
    {
        try {
            $token = $this->generateAccessToken();
            $timestamp = now()->format('YmdHis');
            $shortCode = config('mpesa.short_code');
            $passkey = config('mpesa.passkey');

            $password = base64_encode($shortCode . $passkey . $timestamp);

            $payload = [
                'BusinessShortCode' => $shortCode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId,
            ];

            if (config('mpesa.log_requests')) {
                Log::info('M-Pesa Query Request', ['payload' => $payload]);
            }

            $response = Http::withToken($token)
                ->post($this->baseUrl . '/stkpushquery/v1/query', $payload);

            if (config('mpesa.log_requests')) {
                Log::info('M-Pesa Query Response', ['response' => $response->json()]);
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('M-Pesa Query Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Validate webhook callback signature
     * 
     * @param array $data Callback data
     * @return bool
     */
    public function validateCallback(array $data): bool
    {
        // M-Pesa doesn't use HMAC signature for callbacks
        // Validation is based on IP whitelisting (done in middleware)
        // Additional validation can be done by checking required fields
        
        $requiredFields = ['Body', 'storeId'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                Log::warning('M-Pesa callback missing required field', ['field' => $field]);
                return false;
            }
        }

        return true;
    }

    /**
     * Format phone number to M-Pesa format (254XXXXXXXXX)
     * 
     * @param string $phoneNumber Phone number in various formats
     * @return string Formatted phone number
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // If it starts with 0 (domestic format), replace with 254
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = '254' . substr($phoneNumber, 1);
        }

        // If it doesn't start with 254, prepend it
        if (substr($phoneNumber, 0, 3) !== '254') {
            $phoneNumber = '254' . $phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * Extract customer phone from E.164 format to M-Pesa format
     * 
     * @param string $e164Phone Phone in E.164 format (+256...)
     * @return string Phone in M-Pesa format (254...)
     */
    public function convertE164ToMpesa(string $e164Phone): string
    {
        // Remove + and leading zeros
        $phone = ltrim($e164Phone, '+0');
        
        // Handle different country codes
        // +256 (Uganda) -> 254 prefix needed if in Kenya region
        // For now, just normalize to 254 prefix for M-Pesa compatibility
        if (strpos($phone, '256') === 0) {
            // Uganda number - convert to Kenya M-Pesa format
            $phone = '254' . substr($phone, 3);
        }
        
        return $this->formatPhoneNumber($phone);
    }

    /**
     * Get current environment
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Test M-Pesa credentials
     */
    public function testConnection(): bool
    {
        try {
            $this->generateAccessToken();
            return true;
        } catch (Exception $e) {
            Log::error('M-Pesa Connection Test Failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
