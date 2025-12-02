<?php

namespace Database\Seeders;

use Domains\CRM\Models\Supplier;
use Domains\Shared\Enums\SupplierCategory;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            // Feed suppliers
            [
                'name' => 'ABC Feeds Ltd',
                'email' => 'sales@abcfeeds.co.bw',
                'phone' => '+267 3900 000',
                'category' => SupplierCategory::Feed,
                'performance_rating' => 4.5,
                'current_price_per_unit' => 45.50,
            ],
            [
                'name' => 'Premier Nutrition',
                'email' => 'info@premiernut.co.bw',
                'phone' => '+267 3901 111',
                'category' => SupplierCategory::Feed,
                'performance_rating' => 4.0,
                'current_price_per_unit' => 42.75,
            ],
            // Chicks suppliers
            [
                'name' => 'Botswana Hatchery',
                'email' => 'chicks@bwhatchery.co.bw',
                'phone' => '+267 3902 222',
                'category' => SupplierCategory::Chicks,
                'performance_rating' => 5.0,
                'current_price_per_unit' => 8.50,
            ],
            [
                'name' => 'Continental Poultry',
                'email' => 'sales@contpoultry.co.bw',
                'phone' => '+267 3903 333',
                'category' => SupplierCategory::Chicks,
                'performance_rating' => 4.5,
                'current_price_per_unit' => 9.00,
            ],
            // Medication suppliers
            [
                'name' => 'Vet Pharma Solutions',
                'email' => 'sales@vetpharma.co.bw',
                'phone' => '+267 3904 444',
                'category' => SupplierCategory::Meds,
                'performance_rating' => 4.8,
                'current_price_per_unit' => 125.00,
            ],
            [
                'name' => 'Animal Health Supplies',
                'email' => 'support@animhealth.co.bw',
                'phone' => '+267 3905 555',
                'category' => SupplierCategory::Meds,
                'performance_rating' => 4.2,
                'current_price_per_unit' => 110.50,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
