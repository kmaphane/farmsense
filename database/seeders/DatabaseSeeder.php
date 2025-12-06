<?php

namespace Database\Seeders;

use Domains\Auth\Seeders\RoleAndPermissionSeeder;
use Domains\Auth\Seeders\TeamSeeder;
use Domains\Auth\Seeders\UserSeeder;
use Domains\Broiler\Seeders\BatchSeeder;
use Domains\Broiler\Seeders\DailyLogSeeder;
use Domains\CRM\Seeders\CustomerSeeder;
use Domains\CRM\Seeders\SupplierSeeder;
use Domains\Finance\Seeders\ExpenseSeeder;
use Domains\Finance\Seeders\InvoiceSeeder;
use Domains\Finance\Seeders\PaymentSeeder;
use Domains\Inventory\Seeders\ProductSeeder;
use Domains\Inventory\Seeders\StockMovementSeeder;
use Domains\Inventory\Seeders\WarehouseSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Only seed in local environment
        if (! app()->isLocal()) {
            return;
        }

        // Order matters - dependencies first
        $this->call([
            RoleAndPermissionSeeder::class,  // Filament Shield roles/permissions
            SupplierSeeder::class,            // Global suppliers (no dependencies)
            TeamSeeder::class,                // Create teams
            UserSeeder::class,                // Create users and assign to teams
            CustomerSeeder::class,            // Create customers per team
            ExpenseSeeder::class,             // Create expenses per team
            ProductSeeder::class,             // Create products per team
            WarehouseSeeder::class,           // Create warehouses per team
            StockMovementSeeder::class,       // Create stock movements
            InvoiceSeeder::class,             // Create invoices with line items
            PaymentSeeder::class,             // Create payments for invoices
            BatchSeeder::class,               // Create broiler batches per team
            DailyLogSeeder::class,            // Create daily logs for batches
        ]);
    }
}
