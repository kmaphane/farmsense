<?php

namespace Domains\Finance\Factories;

use Domains\Auth\Models\Team;
use Domains\Finance\Models\Expense;
use Domains\Shared\Enums\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Expense::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'amount' => fake()->numberBetween(1000, 500000), // Amount in cents
            'currency' => 'BWP',
            'category' => fake()->randomElement(ExpenseCategory::cases()),
            'description' => fake()->optional()->sentence(),
            'allocatable_type' => null,
            'allocatable_id' => null,
            'ocr_data' => null,
            'receipt_path' => null,
        ];
    }

    /**
     * Assign the expense to a team.
     */
    public function forTeam(Team $team): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $team->id,
        ]);
    }

    /**
     * Set the expense category to Feed.
     */
    public function feed(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => ExpenseCategory::Feed,
        ]);
    }

    /**
     * Set the expense category to Labor.
     */
    public function labor(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => ExpenseCategory::Labor,
        ]);
    }

    /**
     * Set the expense category to Utilities.
     */
    public function utilities(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => ExpenseCategory::Utilities,
        ]);
    }

    /**
     * Set the expense category to Medication.
     */
    public function medication(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => ExpenseCategory::Medication,
        ]);
    }

    /**
     * Allocate the expense to a specific model.
     */
    public function allocatedTo(Model $allocatable): static
    {
        return $this->state(fn (array $attributes) => [
            'allocatable_type' => get_class($allocatable),
            'allocatable_id' => $allocatable->getKey(),
        ]);
    }
}
