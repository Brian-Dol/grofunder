<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Make expense_attachment nullable since it's managed by Spatie MediaLibrary
     * and should not be a required column on the expenses table.
     */
    public function up(): void
    {
        // For PostgreSQL, use ALTER COLUMN to drop NOT NULL constraint
        if (Schema::hasColumn('expenses', 'expense_attachment')) {
            DB::statement('ALTER TABLE expenses ALTER COLUMN expense_attachment DROP NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse by adding back NOT NULL constraint
        if (Schema::hasColumn('expenses', 'expense_attachment')) {
            DB::statement('ALTER TABLE expenses ALTER COLUMN expense_attachment SET NOT NULL');
        }
    }
};
