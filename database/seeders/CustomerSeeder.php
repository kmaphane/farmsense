<?php

namespace Database\Seeders;

use Domains\Auth\Models\Team;
use Domains\CRM\Models\Customer;
use Domains\Shared\Enums\CustomerType;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();

        // Customer templates
        $customerTemplates = [
            // Wholesale customers
            [
                'name' => 'Gaborone Poultry Market',
                'email' => 'sales@gabpoultry.co.bw',
                'phone' => '+267 3950 100',
                'type' => CustomerType::Wholesale,
                'credit_limit' => 500000, // stored as cents (5000.00 BWP)
                'payment_terms' => 'Net 30',
            ],
            [
                'name' => 'Francistown Distribution',
                'email' => 'contact@fctdist.co.bw',
                'phone' => '+267 4120 200',
                'type' => CustomerType::Wholesale,
                'credit_limit' => 750000,
                'payment_terms' => 'Net 45',
            ],
            [
                'name' => 'Bulk Food Traders',
                'email' => 'orders@bulkfood.co.bw',
                'phone' => '+267 3951 300',
                'type' => CustomerType::Wholesale,
                'credit_limit' => 1000000,
                'payment_terms' => 'Net 30',
            ],
            // Retail customers
            [
                'name' => 'Main Street Butchery',
                'email' => 'butcher@mainst.co.bw',
                'phone' => '+267 3952 400',
                'type' => CustomerType::Retail,
                'credit_limit' => 100000,
                'payment_terms' => 'Cash',
            ],
            [
                'name' => 'Green Valley Supermarket',
                'email' => 'supply@greenvalley.co.bw',
                'phone' => '+267 3953 500',
                'type' => CustomerType::Retail,
                'credit_limit' => 150000,
                'payment_terms' => 'Net 7',
            ],
            [
                'name' => 'Family Foods Ltd',
                'email' => 'purchasing@familyfoods.co.bw',
                'phone' => '+267 3954 600',
                'type' => CustomerType::Retail,
                'credit_limit' => 200000,
                'payment_terms' => 'Net 14',
            ],
        ];

        // Assign customers to each team
        foreach ($teams as $team) {
            foreach ($customerTemplates as $customerData) {
                Customer::create([
                    ...$customerData,
                    'team_id' => $team->id,
                    'notes' => 'Created for ' . $team->name,
                ]);
            }
        }
    }
}
