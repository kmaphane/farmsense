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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('other')->comment('feed, medicine, packaging, equipment, live_bird, whole_chicken, chicken_pieces, offal, by_product, other');
            $table->string('unit')->default('bag')->comment('bag, liter, kg, etc.');
            $table->bigInteger('quantity_on_hand')->default(0)->comment('Current stock in units');
            $table->bigInteger('reorder_level')->default(0)->comment('Trigger for reordering');
            $table->bigInteger('unit_cost')->nullable()->comment('Cost in cents/thebe');

            // Poultry product fields
            $table->bigInteger('selling_price_cents')->nullable()->comment('Selling price in cents/thebe');
            $table->integer('units_per_package')->default(1)->comment('How many units per package (e.g., 10 feet per pack)');
            $table->string('package_unit')->nullable()->comment('single, pack, cup, bag, kg, liter, box');
            $table->integer('yield_per_bird')->default(1)->comment('How many of this item per bird (feet=2, others=1)');

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('team_id');
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
