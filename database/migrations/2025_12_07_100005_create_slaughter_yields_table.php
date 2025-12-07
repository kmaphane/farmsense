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
        Schema::create('slaughter_yields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slaughter_record_id')->constrained('slaughter_records')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('estimated_quantity')->comment('Auto-calculated from birds × yield ÷ units_per_pack');
            $table->integer('actual_quantity')->comment('User-entered actual count');
            $table->integer('household_consumed')->default(0)->comment('Estimated - Actual (kept for household)');
            $table->timestamps();

            $table->index('slaughter_record_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slaughter_yields');
    }
};
