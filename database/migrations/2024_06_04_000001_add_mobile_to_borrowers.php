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
        Schema::table('borrowers', function (Blueprint $table) {
            // Add mobile number field as unique identifier
            $table->string('mobile_number')->nullable()->after('email')->unique();
            // Add cooperative reference (for Growfunder phase)
            $table->unsignedBigInteger('cooperative_id')->nullable()->after('mobile_number');
            // Note: Foreign key to cooperatives will be added in a separate migration after Cooperative table is created
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowers', function (Blueprint $table) {
            $table->dropForeign(['cooperative_id']);
            $table->dropColumn('cooperative_id');
            $table->dropUnique(['mobile_number']);
            $table->dropColumn('mobile_number');
        });
    }
};
