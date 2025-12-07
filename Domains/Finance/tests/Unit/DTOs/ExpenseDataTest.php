<?php

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Domains\Finance\DTOs\ExpenseData;
use Domains\Shared\Enums\ExpenseCategory;
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

it('creates expense DTO from array', function () {
    $dto = ExpenseData::from([
        'team_id' => $this->team->id,
        'amount' => 50000,
        'currency' => 'BWP',
        'category' => ExpenseCategory::Feed,
        'description' => 'Monthly feed purchase',
        'allocatable_type' => null,
        'allocatable_id' => null,
        'ocr_data' => null,
        'receipt_path' => null,
    ]);

    expect($dto->amount)->toBe(50000)
        ->and($dto->currency)->toBe('BWP')
        ->and($dto->category)->toBe(ExpenseCategory::Feed)
        ->and($dto->description)->toBe('Monthly feed purchase');
});

it('creates expense DTO using fromFilament method', function () {
    $dto = ExpenseData::fromFilament([
        'amount' => 100000,
        'category' => ExpenseCategory::Medicine,
        'description' => 'Vaccine purchase',
    ]);

    expect($dto->amount)->toBe(100000)
        ->and($dto->team_id)->toBe($this->team->id)
        ->and($dto->currency)->toBe('BWP'); // Default value
});

it('sets allocatable polymorphic relationship', function () {
    $dto = ExpenseData::from([
        'team_id' => $this->team->id,
        'amount' => 25000,
        'currency' => 'BWP',
        'category' => ExpenseCategory::Feed,
        'description' => 'Batch-specific feed',
        'allocatable_type' => null,
        'allocatable_id' => null,
    ]);

    $dto->withAllocatable('Domains\\Broiler\\Models\\Batch', 5);

    expect($dto->allocatable_type)->toBe('Domains\\Broiler\\Models\\Batch')
        ->and($dto->allocatable_id)->toBe(5);
});

it('validates required fields', function () {
    ExpenseData::from([
        'amount' => 50000,
        // Missing required fields
    ]);
})->throws(ValidationException::class);
