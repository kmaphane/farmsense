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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            // NOTE: Suppliers are GLOBAL/SHARED across all teams
            // This enables supplier pricing insights across the platform
            // Future: API integration for live pricing and order placement
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('category')->default('feed');
            $table->decimal('performance_rating', 3, 2)->nullable()->comment('1.00 to 5.00 scale');
            $table->decimal('current_price_per_unit', 10, 2)->nullable()->comment('Current market price (Phase 2+)');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
