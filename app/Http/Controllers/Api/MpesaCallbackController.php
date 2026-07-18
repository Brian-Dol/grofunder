<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Repayments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaCallbackController extends Controller
{
    /**
     * Handle M-Pesa payment confirmation callback
     * 
     * @param Request $request M-Pesa webhook data
     * @return \Illuminate\Http\JsonResponse
     */
    public function handlePaymentCallback(Request $request)
    {
        try {
            Log::info('M-Pesa Callback Received', $request->all());

            $body = $request->json('Body');
            
            if (!$body) {
                Log::error('M-Pesa Callback: Missing Body', $request->all());
                return response()->json(['error' => 'Invalid callback'], 400);
            }

            $stkCallback = $body['stkCallback'] ?? null;
            
            if (!$stkCallback) {
                Log::error('M-Pesa Callback: Missing stkCallback', $body);
                return response()->json(['error' => 'Invalid callback structure'], 400);
            }

            $checkoutRequestID = $stkCallback['CheckoutRequestID'] ?? null;
            $resultCode = $stkCallback['ResultCode'] ?? null;
            $resultDesc = $stkCallback['ResultDesc'] ?? null;

            // Find repayment by checkout request ID
            $repayment = Repayments::where('mpesa_checkout_request_id', $checkoutRequestID)->first();

            if (!$repayment) {
                Log::warning('M-Pesa Callback: Repayment not found', ['checkoutRequestID' => $checkoutRequestID]);
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid checkout request']);
            }

            // Process based on result code
            if ($resultCode == 0) {
                // Payment successful
                $callbackMetadata = $stkCallback['CallbackMetadata'] ?? [];
                $items = collect($callbackMetadata['Item'] ?? []);

                $mpesaReceiptNumber = $items->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;
                $transactionDate = $items->firstWhere('Name', 'TransactionDate')['Value'] ?? null;
                $phoneNumber = $items->firstWhere('Name', 'PhoneNumber')['Value'] ?? null;
                $amount = $items->firstWhere('Name', 'Amount')['Value'] ?? null;

                $repayment->markMpesaCompleted($mpesaReceiptNumber, [
                    'resultCode' => $resultCode,
                    'resultDesc' => $resultDesc,
                    'transactionDate' => $transactionDate,
                    'phoneNumber' => $phoneNumber,
                    'amount' => $amount,
                    'callbackMetadata' => $callbackMetadata,
                ]);

                Log::info('M-Pesa Payment Completed', [
                    'repayment_id' => $repayment->id,
                    'transaction_id' => $mpesaReceiptNumber,
                    'amount' => $amount,
                ]);
            } else {
                // Payment failed or cancelled
                $repayment->markMpesaFailed([
                    'resultCode' => $resultCode,
                    'resultDesc' => $resultDesc,
                    'checkoutRequestID' => $checkoutRequestID,
                ]);

                Log::warning('M-Pesa Payment Failed', [
                    'repayment_id' => $repayment->id,
                    'resultCode' => $resultCode,
                    'resultDesc' => $resultDesc,
                ]);
            }

            // Return success response to M-Pesa
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback processed successfully']);

        } catch (\Exception $e) {
            Log::error('M-Pesa Callback Processing Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Processing error'], 500);
        }
    }

    /**
     * Handle M-Pesa timeout callback (payment didn't complete within timeout)
     * 
     * @param Request $request M-Pesa timeout data
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleTimeoutCallback(Request $request)
    {
        try {
            Log::info('M-Pesa Timeout Callback Received', $request->all());

            $body = $request->json('Body');
            
            if (!$body) {
                return response()->json(['error' => 'Invalid callback'], 400);
            }

            $timeoutUrl = $body['timeoutUrl'] ?? null;
            
            if (!$timeoutUrl) {
                return response()->json(['error' => 'Invalid timeout callback'], 400);
            }

            // Handle timeout (no action needed - payment just timed out)
            Log::info('M-Pesa Payment Timeout');

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Timeout callback processed']);

        } catch (\Exception $e) {
            Log::error('M-Pesa Timeout Callback Error', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Processing error'], 500);
        }
    }

    /**
     * Query payment status (for agents to check if payment was received)
     * 
     * @param string $checkoutRequestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function queryPaymentStatus(string $checkoutRequestId)
    {
        try {
            $repayment = Repayments::where('mpesa_checkout_request_id', $checkoutRequestId)->first();

            if (!$repayment) {
                return response()->json(['error' => 'Repayment not found'], 404);
            }

            // Query M-Pesa for current status
            $mpesaService = app(\App\Services\MpesaService::class);
            $response = $mpesaService->queryPaymentStatus($checkoutRequestId);

            // Update repayment status if response available
            if ($response['ResponseCode'] == 0) {
                $repayment->update([
                    'mpesa_response' => array_merge($repayment->mpesa_response ?? [], $response),
                ]);
            }

            return response()->json([
                'repayment_id' => $repayment->id,
                'status' => $repayment->mpesa_status,
                'transaction_id' => $repayment->mpesa_transaction_id,
                'mpesa_response' => $response,
            ]);

        } catch (\Exception $e) {
            Log::error('Error querying M-Pesa payment status', [
                'checkoutRequestId' => $checkoutRequestId,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to query payment status'], 500);
        }
    }
}
