<?php

declare(strict_types=1);

namespace Domains\Broiler\Factories;

use DateTimeInterface;
use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Models\DailyLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyLog>
 */
class DailyLogFactory extends Factory
{
    protected $model = DailyLog::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'batch_id' => Batch::factory(),
            'log_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'mortality_count' => fake()->numberBetween(0, 5),
            'feed_consumed_kg' => fake()->randomFloat(2, 50, 200),
            'water_consumed_liters' => fake()->randomFloat(2, 100, 400),
            'temperature_celsius' => fake()->randomFloat(1, 28, 35),
            'humidity_percent' => fake()->randomFloat(1, 55, 75),
            'ammonia_ppm' => fake()->randomFloat(1, 5, 25),
            'notes' => fake()->optional(0.3)->sentence(),
            'recorded_by' => User::query()->inRandomOrder()->first()?->id,
        ];
    }

    public function forBatch(Batch $batch): static
    {
        return $this->state(fn (array $attributes) => [
            'batch_id' => $batch->id,
            'team_id' => $batch->team_id,
        ]);
    }

    public function forDate(DateTimeInterface|string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'log_date' => $date,
        ]);
    }

    public function recordedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_by' => $user->id,
        ]);
    }

    /**
     * Create a realistic daily log based on the age of the batch.
     * Feed consumption and water increase as birds grow.
     */
    public function forAgeDay(int $dayNumber, int $birdCount): static
    {
        // Feed consumption increases with age (roughly 10-180g per bird per day)
        $feedPerBird = match (true) {
            $dayNumber <= 7 => fake()->randomFloat(2, 0.010, 0.025),   // Week 1: 10-25g
            $dayNumber <= 14 => fake()->randomFloat(2, 0.025, 0.050),  // Week 2: 25-50g
            $dayNumber <= 21 => fake()->randomFloat(2, 0.050, 0.090),  // Week 3: 50-90g
            $dayNumber <= 28 => fake()->randomFloat(2, 0.090, 0.130),  // Week 4: 90-130g
            $dayNumber <= 35 => fake()->randomFloat(2, 0.130, 0.165),  // Week 5: 130-165g
            default => fake()->randomFloat(2, 0.160, 0.190),           // Week 6+: 160-190g
        };

        // Water is roughly 1.8-2x feed consumption
        $waterMultiplier = fake()->randomFloat(2, 1.8, 2.2);

        // Mortality is higher in first week and last week, lower in middle
        $mortalityChance = match (true) {
            $dayNumber <= 7 => 0.4,    // 40% chance of any mortality
            $dayNumber <= 35 => 0.15,  // 15% chance
            default => 0.25,           // 25% chance
        };

        $hasMortality = fake()->boolean((int) ($mortalityChance * 100));
        $mortality = $hasMortality ? fake()->numberBetween(1, max(1, (int) ($birdCount * 0.005))) : 0;

        // Temperature should be cooler as birds age (they generate more heat)
        $idealTemp = match (true) {
            $dayNumber <= 3 => 33,
            $dayNumber <= 7 => 31,
            $dayNumber <= 14 => 28,
            $dayNumber <= 21 => 26,
            default => 24,
        };

        return $this->state(fn (array $attributes) => [
            'mortality_count' => $mortality,
            'feed_consumed_kg' => round($feedPerBird * $birdCount, 2),
            'water_consumed_liters' => round($feedPerBird * $waterMultiplier * $birdCount, 2),
            'temperature_celsius' => fake()->randomFloat(1, $idealTemp - 2, $idealTemp + 2),
            'humidity_percent' => fake()->randomFloat(1, 55, 70),
            'ammonia_ppm' => fake()->randomFloat(1, 8, min(25, 8 + $dayNumber * 0.4)),
        ]);
    }
}
