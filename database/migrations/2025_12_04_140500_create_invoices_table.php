<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('status')->default('draft')->comment('draft, sent, paid, overdue, cancelled');
            $table->bigInteger('subtotal')->default(0)->comment('Subtotal in cents/thebe');
            $table->bigInteger('tax_amount')->default(0)->comment('Tax in cents/thebe');
            $table->bigInteger('total_amount')->default(0)->comment('Total in cents/thebe');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('team_id');
            $table->index('customer_id');
            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
