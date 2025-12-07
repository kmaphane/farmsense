<?php

declare(strict_types=1);

namespace Domains\Broiler\Factories;

use Domains\Auth\Models\Team;
use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Models\Batch;
use Domains\CRM\Models\Supplier;
use Domains\Shared\Enums\SupplierCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    protected $model = Batch::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-60 days', '-5 days');
        $initialQuantity = fake()->numberBetween(500, 2000);
        $mortality = fake()->numberBetween(0, (int) ($initialQuantity * 0.08));

        return [
            'team_id' => Team::factory(),
            'name' => 'Batch '.strtoupper(fake()->lexify('???')).'-'.fake()->numerify('####'),
            'batch_number' => 'BRO-'.now()->format('Y').'-'.fake()->unique()->numerify('###'),
            'start_date' => $startDate,
            'expected_end_date' => (clone $startDate)->modify('+42 days'),
            'actual_end_date' => null,
            'status' => BatchStatus::Active,
            'initial_quantity' => $initialQuantity,
            'current_quantity' => $initialQuantity - $mortality,
            'supplier_id' => Supplier::query()->where('category', SupplierCategory::Chicks)->inRandomOrder()->first()?->id,
            'target_weight_kg' => fake()->randomFloat(2, 2.0, 2.8),
            'average_weight_kg' => fake()->randomFloat(2, 0.5, 2.5),
        ];
    }

    public function planned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BatchStatus::Planned,
            'start_date' => fake()->dateTimeBetween('+3 days', '+14 days'),
            'expected_end_date' => fake()->dateTimeBetween('+45 days', '+60 days'),
            'current_quantity' => $attributes['initial_quantity'],
            'average_weight_kg' => null,
        ]);
    }

    public function active(): static
    {
        $startDate = fake()->dateTimeBetween('-30 days', '-7 days');

        return $this->state(fn (array $attributes) => [
            'status' => BatchStatus::Active,
            'start_date' => $startDate,
            'expected_end_date' => (clone $startDate)->modify('+42 days'),
            'actual_end_date' => null,
        ]);
    }

    public function harvesting(): static
    {
        $startDate = fake()->dateTimeBetween('-45 days', '-35 days');

        return $this->state(fn (array $attributes) => [
            'status' => BatchStatus::Harvesting,
            'start_date' => $startDate,
            'expected_end_date' => (clone $startDate)->modify('+42 days'),
            'actual_end_date' => null,
            'average_weight_kg' => fake()->randomFloat(2, 2.0, 2.6),
        ]);
    }

    public function closed(): static
    {
        $startDate = fake()->dateTimeBetween('-90 days', '-50 days');
        $endDate = (clone $startDate)->modify('+42 days');

        return $this->state(fn (array $attributes) => [
            'status' => BatchStatus::Closed,
            'start_date' => $startDate,
            'expected_end_date' => $endDate,
            'actual_end_date' => $endDate,
            'average_weight_kg' => fake()->randomFloat(2, 2.0, 2.5),
        ]);
    }

    public function forTeam(Team $team): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $team->id,
        ]);
    }

    public function withSupplier(Supplier $supplier): static
    {
        return $this->state(fn (array $attributes) => [
            'supplier_id' => $supplier->id,
        ]);
    }
}
