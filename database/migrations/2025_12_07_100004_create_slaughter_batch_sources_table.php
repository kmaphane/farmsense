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
        Schema::create('slaughter_batch_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slaughter_record_id')->constrained('slaughter_records')->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->integer('expected_quantity')->comment('Planned birds from this batch');
            $table->integer('actual_quantity')->comment('Actual birds slaughtered');
            $table->string('discrepancy_reason')->nullable()->comment('theft, death, escape, counting_error, etc.');
            $table->text('discrepancy_notes')->nullable();
            $table->timestamps();

            $table->index('slaughter_record_id');
            $table->index('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slaughter_batch_sources');
    }
};
