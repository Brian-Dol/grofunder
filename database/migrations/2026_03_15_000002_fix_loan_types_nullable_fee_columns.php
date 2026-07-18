<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Make service_fee and penalty_fee columns nullable so that
     * loan types without these fees can be saved without errors.
     * Works with PostgreSQL using ALTER TABLE.
     */
    public function up(): void
    {
        // For PostgreSQL, use ALTER COLUMN to drop NOT NULL constraints
        if (Schema::hasColumn('loan_types', 'service_fee_percentage')) {
            DB::statement('ALTER TABLE loan_types ALTER COLUMN service_fee_percentage DROP NOT NULL');
        }
        if (Schema::hasColumn('loan_types', 'service_fee_custom_amount')) {
            DB::statement('ALTER TABLE loan_types ALTER COLUMN service_fee_custom_amount DROP NOT NULL');
        }
        if (Schema::hasColumn('loan_types', 'penalty_fee_percentage')) {
            DB::statement('ALTER TABLE loan_types ALTER COLUMN penalty_fee_percentage DROP NOT NULL');
        }
        if (Schema::hasColumn('loan_types', 'penalty_fee_custom_amount')) {
            DB::statement('ALTER TABLE loan_types ALTER COLUMN penalty_fee_custom_amount DROP NOT NULL');
        }
        if (Schema::hasColumn('loan_types', 'early_repayment_percent')) {
            DB::statement('ALTER TABLE loan_types ALTER COLUMN early_repayment_percent DROP NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse by adding back NOT NULL constraints
        if (Schema::hasColumn('loan_types', 'service_fee_percentage')) {
            DB::statement('ALTER TABLE loan_types ALTER COLUMN service_fee_percentage SET NOT NULL');
        }
        if (Schema::hasColumn('loan_types', 'service_fee_custom_amount')) {
            DB::statement('ALTER TABLE loan_types ALTER COLUMN service_fee_custom_amount SET NOT NULL');
        }
        if (Schema::hasColumn('loan_types', 'penalty_fee_percentage')) {
            DB::statement('ALTER TABLE loan_types ALTER COLUMN penalty_fee_percentage SET NOT NULL');
        }
        if (Schema::hasColumn('loan_types', 'penalty_fee_custom_amount')) {
            DB::statement('ALTER TABLE loan_types ALTER COLUMN penalty_fee_custom_amount SET NOT NULL');
        }
        if (Schema::hasColumn('loan_types', 'early_repayment_percent')) {
            DB::statement('ALTER TABLE loan_types ALTER COLUMN early_repayment_percent SET NOT NULL');
        }
    }
};
