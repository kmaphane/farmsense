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
        Schema::create('portioning_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->date('portioning_date');
            $table->integer('whole_birds_used')->comment('Deducted from whole bird stock');
            $table->integer('packs_produced')->comment('Chicken piece packs created');
            $table->decimal('pack_weight_kg', 5, 2)->default(0.50)->comment('Weight per pack');
            $table->foreignId('recorded_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('team_id');
            $table->index('portioning_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portioning_records');
    }
};
