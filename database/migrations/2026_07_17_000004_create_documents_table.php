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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_category_id')->constrained('document_categories')->onDelete('restrict');
            $table->foreignId('loan_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('borrower_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->string('file_path');
            $table->string('file_name');
            $table->bigInteger('file_size');
            $table->string('file_mime_type');
            $table->string('status')->default('active'); // active, archived, expired
            $table->text('description')->nullable();
            $table->dateTime('expiry_date')->nullable();
            $table->timestamps();

            // Indices for performance
            $table->index('document_category_id');
            $table->index('loan_id');
            $table->index('borrower_id');
            $table->index('organization_id');
            $table->index('branch_id');
            $table->index('status');
            $table->index('uploaded_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
