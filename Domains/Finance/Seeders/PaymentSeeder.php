<?php

declare(strict_types=1);

namespace Domains\Finance\Seeders;

use Domains\Auth\Models\User;
use Domains\Finance\Enums\PaymentMethod;
use Domains\Finance\Models\Invoice;
use Domains\Finance\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $invoices = Invoice::all();
        $users = User::all();

        foreach ($invoices as $invoice) {
            // 60% chance of having at least one payment
            if (rand(1, 100) <= 60) {
                $paymentCount = rand(1, 2); // 1-2 payments per invoice
                $remainingAmount = $invoice->total_amount;

                for ($i = 0; $i < $paymentCount; $i++) {
                    if ($remainingAmount <= 0) {
                        break;
                    }

                    // Last payment covers remaining or partial
                    if ($i === $paymentCount - 1) {
                        $amount = rand((int) ($remainingAmount * 0.5), $remainingAmount);
                    } else {
                        $amount = rand((int) ($remainingAmount * 0.3), (int) ($remainingAmount * 0.7));
                    }

                    $user = $users->where('current_team_id', $invoice->team_id)->first() ?? $users->first();

                    Payment::create([
                        'team_id' => $invoice->team_id,
                        'invoice_id' => $invoice->id,
                        'amount' => $amount,
                        'payment_method' => PaymentMethod::cases()[array_rand(PaymentMethod::cases())],
                        'reference' => 'REF-'.str_pad((string) rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                        'notes' => 'Partial payment received',
                        'recorded_by' => $user->id,
                        'payment_date' => now()->subDays(rand(0, 30)),
                    ]);

                    $remainingAmount -= $amount;
                }
            }
        }
    }
}
