<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('repayments', function (Blueprint $table) {
            // M-Pesa transaction tracking
            $table->string('mpesa_transaction_id')->nullable()->after('reference_number')->comment('M-Pesa transaction ID');
            $table->string('mpesa_checkout_request_id')->nullable()->after('mpesa_transaction_id')->comment('M-Pesa checkout request ID for STK Push');
            $table->enum('mpesa_status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending')->after('mpesa_checkout_request_id')->comment('M-Pesa payment status');
            $table->json('mpesa_response')->nullable()->after('mpesa_status')->comment('M-Pesa API response data');
            $table->timestamp('mpesa_initiated_at')->nullable()->after('mpesa_response')->comment('When M-Pesa payment was initiated');
            $table->timestamp('mpesa_completed_at')->nullable()->after('mpesa_initiated_at')->comment('When M-Pesa payment was completed');
            $table->string('mpesa_phone_number')->nullable()->after('mpesa_completed_at')->comment('Phone number used for M-Pesa payment');
            
            // Indices for performance
            $table->index('mpesa_transaction_id');
            $table->index('mpesa_checkout_request_id');
            $table->index('mpesa_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repayments', function (Blueprint $table) {
            $table->dropIndex(['mpesa_transaction_id']);
            $table->dropIndex(['mpesa_checkout_request_id']);
            $table->dropIndex(['mpesa_status']);
            
            $table->dropColumn([
                'mpesa_transaction_id',
                'mpesa_checkout_request_id',
                'mpesa_status',
                'mpesa_response',
                'mpesa_initiated_at',
                'mpesa_completed_at',
                'mpesa_phone_number',
            ]);
        });
    }
};
