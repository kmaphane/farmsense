<?php

declare(strict_types=1);

namespace Domains\Finance\Seeders;

use Domains\CRM\Models\Customer;
use Domains\Finance\Enums\InvoiceStatus;
use Domains\Finance\Models\Invoice;
use Domains\Finance\Models\InvoiceLineItem;
use Domains\Inventory\Models\Product;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::all();
        $invoiceCounter = 1000 + (int) (microtime(true) * 1000) % 10000;

        foreach ($customers as $customer) {
            // Create 3-5 invoices per customer
            for ($i = 0; $i < rand(3, 5); $i++) {
                $invoice = Invoice::query()->create([
                    'team_id' => $customer->team_id,
                    'customer_id' => $customer->id,
                    'invoice_number' => 'INV-'.date('Y').'-'.($invoiceCounter++),
                    'status' => InvoiceStatus::cases()[array_rand(InvoiceStatus::cases())],
                    'subtotal' => 0,
                    'tax_amount' => rand(5000, 25000),
                    'total_amount' => 0,
                    'description' => 'Invoice for farm products',
                    'due_date' => now()->addDays(rand(15, 60)),
                    'notes' => 'Payment terms: Net '.(rand(1, 3) * 15).' days',
                ]);

                // Add 2-4 line items per invoice
                $subtotal = 0;
                for ($j = 0; $j < rand(2, 4); $j++) {
                    $product = $products->where('team_id', $customer->team_id)->random();
                    $quantity = rand(1, 20);
                    $unitPrice = rand(50000, 250000); // BWP 500-2500
                    $lineTotal = $quantity * $unitPrice;
                    $subtotal += $lineTotal;

                    InvoiceLineItem::query()->create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $product->id,
                        'description' => $product->name.' - '.$product->unit,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ]);
                }

                // Update invoice totals
                $invoice->update([
                    'subtotal' => $subtotal,
                    'total_amount' => $subtotal + $invoice->tax_amount,
                ]);
            }
        }
    }
}
