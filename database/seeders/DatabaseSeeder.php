<?php

namespace Database\Seeders;

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
        ]);
    }
}
