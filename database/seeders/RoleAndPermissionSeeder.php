<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing roles and permissions
        Role::query()->delete();
        Permission::query()->delete();

        // Define the 4 roles
        $superAdmin = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $farmManager = Role::create(['name' => 'Farm Manager', 'guard_name' => 'web']);
        $partner = Role::create(['name' => 'Partner', 'guard_name' => 'web']);
        $fieldWorker = Role::create(['name' => 'Field Worker', 'guard_name' => 'web']);

        // Define permissions - Super Admin gets ALL
        $allPermissions = Permission::all();

        // Super Admin: Full access to everything
        $superAdmin->syncPermissions($allPermissions);

        // Farm Manager: Full team access (financial + operational)
        $farmManagerPermissions = Permission::whereIn('name', [
            // Users in own team
            'view_user',
            'view_any_user',
            'create_user',
            'update_user',
            'delete_user',

            // Customers
            'view_customer',
            'view_any_customer',
            'create_customer',
            'update_customer',
            'delete_customer',

            // Suppliers
            'view_supplier',
            'view_any_supplier',
            'create_supplier',
            'update_supplier',
            'delete_supplier',

            // Expenses
            'view_expense',
            'view_any_expense',
            'create_expense',
            'update_expense',
            'delete_expense',

            // Batches (if exists)
            'view_batch',
            'view_any_batch',
            'create_batch',
            'update_batch',
            'delete_batch',
        ])->pluck('id');
        $farmManager->syncPermissions($farmManagerPermissions);

        // Partner: Read-only financials, read/write batches
        $partnerPermissions = Permission::whereIn('name', [
            // View financials
            'view_expense',
            'view_any_expense',

            // Batches read/write
            'view_batch',
            'view_any_batch',
            'create_batch',
            'update_batch',
        ])->pluck('id');
        $partner->syncPermissions($partnerPermissions);

        // Field Worker: Daily logs only
        $fieldWorkerPermissions = Permission::whereIn('name', [
            // Batches read only
            'view_batch',
            'view_any_batch',
        ])->pluck('id');
        $fieldWorker->syncPermissions($fieldWorkerPermissions);
    }
}
