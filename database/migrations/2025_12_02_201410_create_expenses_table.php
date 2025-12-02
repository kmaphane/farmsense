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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->bigInteger('amount')->comment('Stored as cents/thebe');
            $table->string('currency')->default('BWP');
            $table->string('category');
            $table->string('description')->nullable();

            // Polymorphic relationship for allocation (e.g., to a Batch)
            $table->string('allocatable_type')->nullable()->comment('e.g., Domains\\Broiler\\Models\\Batch');
            $table->unsignedBigInteger('allocatable_id')->nullable();

            // OCR Data
            $table->json('ocr_data')->nullable()->comment('Raw OCR scan results');
            $table->string('receipt_path')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('team_id');
            $table->index(['allocatable_type', 'allocatable_id']);
            $table->index('category');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
