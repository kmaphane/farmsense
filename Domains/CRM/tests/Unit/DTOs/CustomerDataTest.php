<?php

use Domains\CRM\DTOs\CustomerData;
use Domains\Shared\Enums\CustomerType;

beforeEach(function () {
    // Mock authenticated user with team
    $this->user = \App\Models\User::factory()->create([
        'current_team_id' => 1,
    ]);
    $this->actingAs($this->user);
});

it('creates customer DTO from array', function () {
    $dto = CustomerData::from([
        'team_id' => 1,
        'name' => 'Test Customer',
        'email' => 'test@example.com',
        'phone' => '1234567890',
        'type' => CustomerType::Wholesale,
        'credit_limit' => 100000,
        'payment_terms' => 30,
        'notes' => 'Test notes',
    ]);

    expect($dto->name)->toBe('Test Customer')
        ->and($dto->email)->toBe('test@example.com')
        ->and($dto->phone)->toBe('1234567890')
        ->and($dto->type)->toBe(CustomerType::Wholesale)
        ->and($dto->credit_limit)->toBe(100000)
        ->and($dto->payment_terms)->toBe(30)
        ->and($dto->notes)->toBe('Test notes');
});

it('creates customer DTO using fromFilament method', function () {
    $dto = CustomerData::fromFilament([
        'name' => 'Filament Customer',
        'email' => 'filament@example.com',
        'type' => CustomerType::Retail,
    ]);

    expect($dto->name)->toBe('Filament Customer')
        ->and($dto->email)->toBe('filament@example.com')
        ->and($dto->type)->toBe(CustomerType::Retail)
        ->and($dto->team_id)->toBe(1); // Auto-filled from current user
});

it('converts customer DTO to array', function () {
    $dto = CustomerData::from([
        'team_id' => 1,
        'name' => 'Array Test',
        'email' => 'array@example.com',
        'type' => CustomerType::Wholesale,
    ]);

    $array = $dto->toArray();

    expect($array)->toBeArray()
        ->and($array['name'])->toBe('Array Test')
        ->and($array['email'])->toBe('array@example.com');
});

it('validates required fields', function () {
    CustomerData::from([
        'name' => '', // Invalid - required
    ]);
})->throws(\Spatie\LaravelData\Exceptions\ValidationException::class);

it('validates email format', function () {
    CustomerData::from([
        'team_id' => 1,
        'name' => 'Test',
        'email' => 'invalid-email', // Invalid format
        'type' => CustomerType::Retail,
    ]);
})->throws(\Spatie\LaravelData\Exceptions\ValidationException::class);

it('handles nullable fields', function () {
    $dto = CustomerData::from([
        'team_id' => 1,
        'name' => 'Minimal Customer',
        'email' => null,
        'phone' => null,
        'type' => CustomerType::Retail,
        'credit_limit' => null,
        'payment_terms' => null,
        'notes' => null,
    ]);

    expect($dto->email)->toBeNull()
        ->and($dto->phone)->toBeNull()
        ->and($dto->credit_limit)->toBeNull();
});
