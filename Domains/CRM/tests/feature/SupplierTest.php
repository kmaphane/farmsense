<?php

namespace Domains\CRM\Tests\Feature;

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Domains\Auth\Seeders\RoleAndPermissionSeeder;
use Domains\CRM\Models\Supplier;
use Domains\Shared\Enums\SupplierCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $farmManager;

    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $superAdminRole = Role::query()->where('name', 'Super Admin')->first();
        $farmManagerRole = Role::query()->where('name', 'Farm Manager')->first();

        $this->superAdmin = User::factory()->create();
        $this->farmManager = User::factory()->create();
        $this->team = Team::factory()->create();

        // Assign Spatie roles
        $this->superAdmin->assignRole($superAdminRole);
        $this->farmManager->assignRole($farmManagerRole);

        $this->superAdmin->teams()->attach($this->team->id, ['role_id' => $superAdminRole->id]);
        $this->superAdmin->update(['current_team_id' => $this->team->id]);

        $this->farmManager->teams()->attach($this->team->id, ['role_id' => $farmManagerRole->id]);
        $this->farmManager->update(['current_team_id' => $this->team->id]);
    }

    /**
     * Test that suppliers are NOT scoped to teams (they are global).
     */
    public function test_suppliers_are_global_not_team_scoped(): void
    {
        // Create suppliers (without team context)
        $supplier = Supplier::factory()->create([
            'name' => 'Global Feed Supplier',
            'category' => SupplierCategory::Feed,
        ]);

        // Even with team context set, suppliers should be accessible
        $this->superAdmin->update(['current_team_id' => $this->team->id]);
        request()->merge(['team_id' => $this->team->id]);

        $suppliers = Supplier::query()->get();

        expect($suppliers->count())->toBeGreaterThan(0);
        expect($suppliers->pluck('id')->contains($supplier->id))->toBeTrue();
    }

    /**
     * Test that all authenticated users can view suppliers.
     */
    public function test_all_authenticated_users_can_view_suppliers(): void
    {
        $supplier = Supplier::factory()->create([
            'name' => 'Test Supplier',
            'category' => SupplierCategory::Feed,
        ]);

        expect($this->farmManager->can('viewAny', Supplier::class))->toBeTrue();
        expect($this->superAdmin->can('viewAny', Supplier::class))->toBeTrue();
        expect($this->farmManager->can('view', $supplier))->toBeTrue();
        expect($this->superAdmin->can('view', $supplier))->toBeTrue();
    }

    /**
     * Test that farm managers cannot create suppliers.
     */
    public function test_farm_manager_cannot_create_supplier(): void
    {
        expect($this->farmManager->can('create', Supplier::class))->toBeFalse();
    }

    /**
     * Test that super admin can create suppliers.
     */
    public function test_super_admin_can_create_supplier(): void
    {
        expect($this->superAdmin->can('create', Supplier::class))->toBeTrue();
    }

    /**
     * Test that farm managers cannot update suppliers.
     */
    public function test_farm_manager_cannot_update_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        expect($this->farmManager->can('update', $supplier))->toBeFalse();
    }

    /**
     * Test that super admin can update suppliers.
     */
    public function test_super_admin_can_update_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        expect($this->superAdmin->can('update', $supplier))->toBeTrue();
    }

    /**
     * Test that supplier categories are properly cast to enum.
     */
    public function test_supplier_category_is_enum(): void
    {
        $supplier = Supplier::factory()->create([
            'category' => SupplierCategory::Chicks,
        ]);

        expect($supplier->category)->toEqual(SupplierCategory::Chicks);
        expect($supplier->category)->toBeInstanceOf(SupplierCategory::class);
    }

    /**
     * Test that supplier pricing data is correctly stored and retrieved.
     */
    public function test_supplier_pricing_data(): void
    {
        $supplier = Supplier::factory()->create([
            'name' => 'Premium Feed Co',
            'category' => SupplierCategory::Feed,
            'current_price_per_unit' => 45.50,
            'performance_rating' => 4.5,
        ]);

        expect($supplier->current_price_per_unit)->toBe(45.50);
        expect($supplier->performance_rating)->toBe(4.5);
    }

    /**
     * Test that suppliers can be filtered by category.
     */
    public function test_suppliers_can_be_filtered_by_category(): void
    {
        Supplier::factory(3)->create(['category' => SupplierCategory::Feed]);
        Supplier::factory(2)->create(['category' => SupplierCategory::Chicks]);

        $feedSuppliers = Supplier::query()->where('category', SupplierCategory::Feed->value)->count();
        $chicksSuppliers = Supplier::query()->where('category', SupplierCategory::Chicks->value)->count();

        expect($feedSuppliers)->toBe(3);
        expect($chicksSuppliers)->toBe(2);
    }

    /**
     * Test that inactive suppliers can be hidden.
     */
    public function test_inactive_suppliers_can_be_hidden(): void
    {
        Supplier::factory()->create(['name' => 'Active Supplier', 'is_active' => true]);
        Supplier::factory()->create(['name' => 'Inactive Supplier', 'is_active' => false]);

        $activeSuppliers = Supplier::query()->where('is_active', true)->count();

        expect($activeSuppliers)->toBeGreaterThanOrEqual(1);
    }
}
