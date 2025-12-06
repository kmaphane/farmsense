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
        Schema::create('daily_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->date('log_date');
            $table->integer('mortality_count')->default(0);
            $table->decimal('feed_consumed_kg', 10, 2);
            $table->decimal('water_consumed_liters', 10, 2)->nullable();
            $table->decimal('temperature_celsius', 5, 1)->nullable();
            $table->decimal('humidity_percent', 5, 1)->nullable();
            $table->decimal('ammonia_ppm', 5, 1)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['batch_id', 'log_date']);
            $table->index(['team_id', 'log_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_logs');
    }
};
