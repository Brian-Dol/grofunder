# M-Pesa Integration Setup Guide

## Overview

Growfunder now includes M-Pesa mobile money integration for automated loan repayment processing. This guide explains how to set up and configure M-Pesa for your deployment.

## Architecture

The M-Pesa integration consists of:

- **Service Layer** (`app/Services/MpesaService.php`) - Handles all M-Pesa API calls
- **Model Methods** (`app/Models/Repayments.php`) - Initiates and tracks M-Pesa payments
- **Webhook Controller** (`app/Http/Controllers/Api/MpesaCallbackController.php`) - Receives payment confirmations
- **API Routes** (`routes/api.php`) - Webhook endpoints for M-Pesa callbacks
- **Database Fields** - M-Pesa status tracking columns in repayments table
- **Filament UI** (`app/Filament/Resources/RepaymentsResource.php`) - "Send M-Pesa" button for agents/admins

## Prerequisites

1. **Safaricom Developer Account**
   - Register at https://developer.safaricom.co.ke/
   - Accept the terms and conditions
   - Navigate to "My Apps" section

2. **M-Pesa API Credentials** (Sandbox)
   - Consumer Key
   - Consumer Secret
   - Short Code (Business/Till Code)
   - Passkey
   - Username
   - Password

3. **Production Requirements** (when ready)
   - Live M-Pesa Business Account
   - Production API credentials from Safaricom
   - SSL certificate for your domain (required by M-Pesa)
   - Registered callback URLs with M-Pesa

## Configuration Steps

### Step 1: Set Environment Variables

Add these to your `.env` file:

```bash
# M-Pesa Configuration
MPESA_ENV=sandbox  # Change to 'production' for live deployment
MPESA_CONSUMER_KEY=your_consumer_key_here
MPESA_CONSUMER_SECRET=your_consumer_secret_here
MPESA_SHORT_CODE=your_business_code_here
MPESA_PASSKEY=your_passkey_here
MPESA_USERNAME=your_api_username
MPESA_PASSWORD=your_api_password

# Callback URL (must be publicly accessible)
MPESA_CALLBACK_URL=https://yourdomain.com/api/mpesa/callback
MPESA_TIMEOUT_URL=https://yourdomain.com/api/mpesa/timeout

# Transaction Description
MPESA_TRANSACTION_DESC=Growfunder Loan Repayment
MPESA_ACCOUNT_REF=Growfunder

# Enable logging for debugging
MPESA_LOG_REQUESTS=true
```

### Step 2: Test the Connection

Test your M-Pesa credentials:

```bash
php artisan tinker

# Inside tinker:
>>> $mpesa = app(\App\Services\MpesaService::class);
>>> $mpesa->testConnection();
// Should return: true
```

### Step 3: Configure Webhook URLs in Safaricom Portal

1. Log in to https://developer.safaricom.co.ke/
2. Navigate to your M-Pesa app
3. Update callback URLs:
   - **Payment Callback URL**: `https://yourdomain.com/api/mpesa/callback`
   - **Timeout URL**: `https://yourdomain.com/api/mpesa/timeout`

### Step 4: Enable HTTPS (Production)

M-Pesa requires HTTPS for callbacks. Ensure:
- Your domain has a valid SSL certificate
- Laravel is configured to use HTTPS
- Update `APP_URL` in `.env` to use `https://`

## Using M-Pesa Payments

### From Filament Admin Panel

1. Navigate to **Repayments** resource
2. Find the repayment you want to collect
3. Click the **"Send M-Pesa"** button
4. Enter the customer's phone number (format: `+256701234567` or `0701234567`)
5. Click "Submit"
6. M-Pesa sends STK Push (popup) to customer's phone
7. Customer enters M-Pesa PIN within 40 seconds
8. Payment confirmation received via webhook

### Programmatically

```php
use App\Models\Repayments;
use App\Services\MpesaService;

// Get repayment
$repayment = Repayments::find($id);

// Initiate M-Pesa payment
$response = $repayment->initiateMpesaPayment('+256701234567');

// Check if initiated successfully
if (isset($response['CheckoutRequestID'])) {
    // Payment prompt sent to customer
    $checkoutRequestId = $response['CheckoutRequestID'];
} else {
    // Error occurred
    $error = $response['errorMessage'] ?? 'Unknown error';
}
```

### Query Payment Status

```php
// Check if payment was received
if ($repayment->isMpesaPending()) {
    // Query M-Pesa for current status
    $mpesaService = app(MpesaService::class);
    $status = $mpesaService->queryPaymentStatus($repayment->mpesa_checkout_request_id);
    
    if ($status['ResponseCode'] === 0) {
        // Payment successful
    }
}
```

## Database Schema

The following fields were added to the `repayments` table:

```php
$table->string('mpesa_transaction_id')->nullable();      // M-Pesa receipt number
$table->string('mpesa_checkout_request_id')->nullable(); // STK Push request ID
$table->enum('mpesa_status', ['pending', 'completed', 'failed', 'cancelled']); 
$table->json('mpesa_response')->nullable();               // Full M-Pesa API response
$table->timestamp('mpesa_initiated_at')->nullable();     // When payment was initiated
$table->timestamp('mpesa_completed_at')->nullable();     // When payment was confirmed
$table->string('mpesa_phone_number')->nullable();         // Phone number used
```

## Payment Flow

```
1. Agent/Admin clicks "Send M-Pesa" button
   ↓
2. Phone number collected via form
   ↓
3. MpesaService.initiateSTKPush() called
   ↓
4. M-Pesa sends popup to customer's phone
   ↓
5. Customer enters PIN (40 second timeout)
   ↓
6a. PAYMENT SUCCESSFUL: M-Pesa sends callback to webhook
   ↓
7a. MpesaCallbackController processes callback
   ↓
8a. Repayment.markMpesaCompleted() updates status
   
6b. PAYMENT FAILED: M-Pesa sends error callback
   ↓
7b. MpesaCallbackController marks as failed
   ↓
8b. Status updated to 'failed'
```

## Webhook Security

### IP Whitelisting

M-Pesa callbacks come from Safaricom's servers. Safaricom recommends whitelisting these IPs (check their documentation for latest IPs).

Add to `.env`:
```bash
MPESA_ALLOWED_IPS=196.201.213.13,196.201.214.206
```

### Callback Validation

All callbacks are logged for audit trail:
```php
// View M-Pesa logs
tail storage/logs/laravel.log | grep "M-Pesa"
```

## Sandbox Testing

### Test Credentials (Provided by Safaricom)

The sandbox environment allows you to test without real money:

```bash
# Sandbox auth endpoint
https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials

# STK Push endpoint
https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest
```

### Testing Steps

1. Get test account credentials from Safaricom
2. Set `MPESA_ENV=sandbox` in `.env`
3. Use test phone numbers provided by Safaricom
4. Monitor logs for webhook delivery
5. Verify payment status in Filament UI

## Troubleshooting

### "Invalid Consumer Key/Secret"

- Verify credentials match your app in Safaricom portal
- Check for extra whitespace in `.env`
- Regenerate credentials if needed

### "Failed to generate access token"

- Check internet connectivity
- Verify API endpoint URLs in config
- Check if M-Pesa API is experiencing issues

### "Webhook not being received"

- Verify callback URL is publicly accessible: `curl https://yourdomain.com/api/mpesa/callback`
- Check HTTPS certificate is valid: `openssl s_client -connect yourdomain.com:443`
- Verify IP whitelisting rules allow M-Pesa IPs
- Check Laravel logs: `tail storage/logs/laravel.log`

### Payment shows "pending" forever

- Query status manually: Click "Check Status" button in Filament
- Verify M-Pesa callback URL configuration in Safaricom portal
- Check firewall doesn't block incoming webhooks

## Monitoring & Logging

All M-Pesa activities are logged:

```bash
# View all M-Pesa operations
grep "M-Pesa" storage/logs/laravel.log

# Watch real-time logs
tail -f storage/logs/laravel.log | grep "M-Pesa"

# Search for specific repayment
grep "repayment_id.*123" storage/logs/laravel.log
```

## API Endpoints

### Webhook Endpoints (Public - No Authentication)

```
POST /api/mpesa/callback
  - Receives payment confirmation/failure
  - Called by M-Pesa servers automatically
  
POST /api/mpesa/timeout
  - Receives timeout notification
  - Called if customer doesn't enter PIN within 40 seconds
```

### Agent/Admin Endpoints (Authenticated)

```
GET /api/mpesa/query-status/{checkoutRequestId}
  - Query current payment status
  - Requires Sanctum token authentication
```

## Production Deployment

1. **Environment**
   - Change `MPESA_ENV=production`
   - Use production API credentials from Safaricom
   - Update callback URLs to production domain

2. **Security**
   - Ensure HTTPS certificate is valid
   - Enable IP whitelisting for M-Pesa IPs
   - Set `MPESA_LOG_REQUESTS=false` for production (optional)

3. **Monitoring**
   - Set up alerts for failed payments
   - Monitor webhook delivery success rate
   - Regular audit of transaction logs

4. **Support**
   - Contact Safaricom for production API support
   - Keep merchant account active
   - Monitor M-Pesa service status page

## Cost

M-Pesa charges transaction fees:
- **Sandbox**: No charges (testing only)
- **Production**: Per-transaction fees apply (check Safaricom rates)

Factor these into your loan pricing model.

## Support & Resources

- **Safaricom Documentation**: https://developer.safaricom.co.ke/docs
- **M-Pesa API Specs**: https://developer.safaricom.co.ke/apis
- **Growfunder Support**: Contact your development team

## Next Steps

1. ✅ Configure environment variables
2. ✅ Test connection with `testConnection()`
3. ✅ Send test payment from Filament UI
4. ✅ Verify webhook delivery in logs
5. ✅ Deploy to production when ready
