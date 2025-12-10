<?php

declare(strict_types=1);

use Database\Factories\ProductFactory;
use Database\Factories\TeamFactory;
use Domains\Auth\Models\User;
use Domains\Inventory\Enums\PackageUnit;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('can create a poultry product with all fields', function () {
    $user = User::factory()->create();
    actingAs($user);
    $team = TeamFactory::new()->create();

    $payload = [
        'team_id' => $team->id,
        'name' => 'Test Broiler',
        'type' => ProductType::LiveBird->value,
        'unit' => 'single',
        'selling_price_cents' => 3500,
        'units_per_package' => 1,
        'package_unit' => PackageUnit::Single->value,
        'yield_per_bird' => 1,
        'quantity_on_hand' => 100,
        'reorder_level' => 10,
        'unit_cost' => 2000,
        'is_active' => true,
    ];

    $response = post('/admin/products', $payload);
    $response->assertRedirect();

    expect(Product::where('name', 'Test Broiler')->first())
        ->not->toBeNull()
        ->selling_price_cents->toBe(3500)
        ->units_per_package->toBe(1)
        ->package_unit->toBe(PackageUnit::Single)
        ->yield_per_bird->toBe(1);
});

it('shows poultry product fields in Filament table', function () {
    $user = User::factory()->create();
    actingAs($user);
    $team = TeamFactory::new()->create();

    $product = ProductFactory::new()->create([
        'team_id' => $team->id,
        'name' => 'Whole Chicken',
        'type' => ProductType::WholeChicken->value,
        'selling_price_cents' => 4200,
        'units_per_package' => 1,
        'package_unit' => PackageUnit::Single->value,
        'yield_per_bird' => 1,
    ]);

    $response = get('/admin/products');
    $response->assertSee('Whole Chicken');
    $response->assertSee('4200');
    $response->assertSee('Single');
    $response->assertSee('1');
});
