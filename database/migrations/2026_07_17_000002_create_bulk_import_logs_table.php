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
        Schema::create('bulk_import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('import_type'); // 'borrower', 'loan'
            $table->string('file_name');
            $table->integer('total_rows');
            $table->integer('successful_imports');
            $table->integer('failed_imports');
            $table->decimal('success_rate', 5, 2);
            $table->json('errors')->nullable();
            $table->json('warnings')->nullable();
            $table->string('status'); // 'pending', 'completed', 'failed'
            $table->timestamps();

            $table->index('user_id');
            $table->index('import_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_import_logs');
    }
};
