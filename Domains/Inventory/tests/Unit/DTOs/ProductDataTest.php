<?php

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Domains\Inventory\DTOs\ProductData;
use Spatie\LaravelData\Exceptions\ValidationException;

beforeEach(function () {
    // Create user first
    $this->user = User::factory()->create();

    // Create team owned by user
    $this->team = Team::factory()->create(['owner_id' => $this->user->id]);

    // Update user with current team
    $this->user->update(['current_team_id' => $this->team->id]);

    $this->actingAs($this->user);
});

it('creates product DTO from array', function () {
    $dto = ProductData::from([
        'team_id' => $this->team->id,
        'name' => 'Broiler Starter Feed',
        'description' => 'High protein starter feed',
        'type' => 'feed',
        'unit' => 'kg',
        'quantity_on_hand' => 5000,
        'reorder_level' => 1000,
        'unit_cost' => 2500,
        'is_active' => true,
    ]);

    expect($dto->name)->toBe('Broiler Starter Feed')
        ->and($dto->type)->toBe('feed')
        ->and($dto->unit)->toBe('kg')
        ->and($dto->quantity_on_hand)->toBe(5000)
        ->and($dto->reorder_level)->toBe(1000)
        ->and($dto->unit_cost)->toBe(2500)
        ->and($dto->is_active)->toBeTrue();
});

it('creates product DTO using fromFilament method with defaults', function () {
    $dto = ProductData::fromFilament([
        'name' => 'New Product',
        'type' => 'equipment',
        'unit' => 'pcs',
    ]);

    expect($dto->name)->toBe('New Product')
        ->and($dto->team_id)->toBe($this->team->id)
        ->and($dto->quantity_on_hand)->toBe(0)
        ->and($dto->is_active)->toBeTrue();
});

it('validates product type enum', function () {
    ProductData::from([
        'team_id' => $this->team->id,
        'name' => 'Test Product',
        'type' => 'invalid_type', // Invalid
        'unit' => 'kg',
    ]);
})->throws(ValidationException::class);

it('validates minimum values for quantities', function () {
    ProductData::from([
        'team_id' => $this->team->id,
        'name' => 'Test Product',
        'type' => 'feed',
        'unit' => 'kg',
        'quantity_on_hand' => -10, // Invalid - negative
    ]);
})->throws(ValidationException::class);
