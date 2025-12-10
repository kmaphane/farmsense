<?php

declare(strict_types=1);

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Domains\Inventory\Models\Product;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('allows a user to create a product via the user-facing endpoint', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $user->teams()->attach($team->id);
    $user->setCurrentTeam($team);
    actingAs($user);

    $payload = [
        'name' => 'User Broiler',
        'type' => 'live_bird',
        'unit' => 'single',
        'selling_price_cents' => 3500,
        'units_per_package' => 1,
        'package_unit' => 'single',
        'yield_per_bird' => 1,
        'quantity_on_hand' => 100,
        'reorder_level' => 10,
        'unit_cost' => 2000,
        'is_active' => true,
    ];

    $response = post('/products', $payload);
    $response->assertRedirect();

    $product = Product::where('name', 'User Broiler')->first();
    expect($product)->not->toBeNull();
    expect($product->selling_price_cents)->toBe(3500);
    expect($product->units_per_package)->toBe(1);
    expect($product->package_unit)->toBe('single');
    expect($product->yield_per_bird)->toBe(1);
});
