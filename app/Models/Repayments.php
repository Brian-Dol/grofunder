<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Builder;

class Repayments extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'loan_id',
        'loan_number',
        'balance',
        'payments',
        'principal',
        'payments_method',
        'reference_number',
        'repayment_date',
        'organization_id',
        'branch_id',
        'mpesa_transaction_id',
        'mpesa_checkout_request_id',
        'mpesa_status',
        'mpesa_response',
        'mpesa_initiated_at',
        'mpesa_completed_at',
        'mpesa_phone_number',
    ];

    protected $casts = [
        'repayment_date' => 'date',
        'mpesa_response' => 'json',
        'mpesa_initiated_at' => 'datetime',
        'mpesa_completed_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id', 'id');
    }

    public function loan_number()
    {
        return $this->belongsTo(Loan::class, 'loan_id', 'id');
    }

    /**
     * Initiate M-Pesa payment for this repayment
     * 
     * @param string $phoneNumber Customer phone number (E.164 format: +256701234567)
     * @return array M-Pesa response
     */
    public function initiateMpesaPayment(string $phoneNumber): array
    {
        try {
            $mpesaService = app(\App\Services\MpesaService::class);
            
            // Convert E.164 format to M-Pesa format
            $formattedPhone = $mpesaService->convertE164ToMpesa($phoneNumber);
            
            // Create account reference with repayment ID
            $accountRef = "REP-{$this->id}-LN{$this->loan_number}";
            
            // Initiate STK Push
            $response = $mpesaService->initiateSTKPush(
                $formattedPhone,
                $this->payments, // Amount to collect
                $accountRef
            );

            // Store M-Pesa response data
            if (isset($response['CheckoutRequestID'])) {
                $this->update([
                    'mpesa_checkout_request_id' => $response['CheckoutRequestID'],
                    'mpesa_phone_number' => $formattedPhone,
                    'mpesa_status' => 'pending',
                    'mpesa_response' => $response,
                    'mpesa_initiated_at' => now(),
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to initiate M-Pesa payment', [
                'repayment_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Mark repayment as completed via M-Pesa
     * 
     * @param string $transactionId M-Pesa transaction ID
     * @param array $responseData Full M-Pesa callback data
     */
    public function markMpesaCompleted(string $transactionId, array $responseData = []): void
    {
        $this->update([
            'mpesa_transaction_id' => $transactionId,
            'mpesa_status' => 'completed',
            'mpesa_response' => array_merge($this->mpesa_response ?? [], $responseData),
            'mpesa_completed_at' => now(),
            'payments_method' => 'M-Pesa',
            'reference_number' => $transactionId,
        ]);

        // Log activity
        \Illuminate\Support\Facades\Log::info('Repayment completed via M-Pesa', [
            'repayment_id' => $this->id,
            'transaction_id' => $transactionId,
            'amount' => $this->payments,
        ]);
    }

    /**
     * Mark repayment as failed via M-Pesa
     * 
     * @param array $responseData M-Pesa error response
     */
    public function markMpesaFailed(array $responseData = []): void
    {
        $this->update([
            'mpesa_status' => 'failed',
            'mpesa_response' => array_merge($this->mpesa_response ?? [], $responseData),
        ]);

        \Illuminate\Support\Facades\Log::warning('M-Pesa payment failed', [
            'repayment_id' => $this->id,
            'response' => $responseData,
        ]);
    }

    /**
     * Get M-Pesa payment status badge
     */
    public function getMpesaStatusBadge(): string
    {
        return match($this->mpesa_status) {
            'completed' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Check if payment is M-Pesa enabled
     */
    public function isMpesaPayment(): bool
    {
        return $this->payments_method === 'M-Pesa' && !is_null($this->mpesa_transaction_id);
    }

    /**
     * Check if M-Pesa payment is pending
     */
    public function isMpesaPending(): bool
    {
        return $this->mpesa_status === 'pending' && !is_null($this->mpesa_checkout_request_id);
    }

    // public function getCreatedAtAttribute($value) {
//     return date('d,F Y H:m:i', strtotime($value));
// }


    protected static function booted(): void
    {

        static::addGlobalScope('org', function (Builder $query) {



            $query->where('organization_id', auth()->user()->organization_id)
                ->where('branch_id', auth()->user()->branch_id)
                ->orWhere('organization_id', "=", NULL);

        });
    }

}
