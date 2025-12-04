<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete()->comment('Optional product reference');
            $table->string('description');
            $table->bigInteger('quantity')->default(1);
            $table->bigInteger('unit_price')->comment('Price in cents/thebe');
            $table->bigInteger('line_total')->comment('quantity × unit_price in cents/thebe');

            $table->index('invoice_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_line_items');
    }
};
