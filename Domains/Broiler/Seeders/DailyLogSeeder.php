<?php

declare(strict_types=1);

namespace Domains\Broiler\Seeders;

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Models\DailyLog;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class DailyLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();

        foreach ($teams as $team) {
            $batches = Batch::query()->where('team_id', $team->id)
                ->whereIn('status', [BatchStatus::Active, BatchStatus::Harvesting, BatchStatus::Closed])
                ->get();

            // Get users from this team to act as recorders
            $recorders = User::query()->whereHas('teams', fn (Builder $q) => $q->where('team_id', $team->id))->get();

            foreach ($batches as $batch) {
                $this->createDailyLogsForBatch($batch, $recorders);
            }
        }
    }

    /**
     * @param  Collection<int, User>  $recorders
     */
    private function createDailyLogsForBatch(Batch $batch, $recorders): void
    {
        $startDate = $batch->start_date;
        $endDate = match ($batch->status) {
            BatchStatus::Closed => $batch->actual_end_date ?? $batch->expected_end_date,
            BatchStatus::Harvesting => now()->subDays(1),
            default => now()->subDays(1), // Active batches get logs up to yesterday
        };

        $dayCount = (int) $startDate->diffInDays($endDate);
        $currentQuantity = $batch->initial_quantity;
        $totalFeedConsumed = 0;

        for ($day = 0; $day <= $dayCount; $day++) {
            $logDate = $startDate->copy()->addDays($day);
            $dayNumber = $day + 1;

            // Calculate realistic values based on age
            $logData = $this->calculateDailyLogData($dayNumber, $currentQuantity, $batch->initial_quantity);

            // Update running totals
            $currentQuantity -= $logData['mortality_count'];
            $totalFeedConsumed += $logData['feed_consumed_kg'];

            DailyLog::query()->create([
                'team_id' => $batch->team_id,
                'batch_id' => $batch->id,
                'log_date' => $logDate,
                'mortality_count' => $logData['mortality_count'],
                'feed_consumed_kg' => $logData['feed_consumed_kg'],
                'water_consumed_liters' => $logData['water_consumed_liters'],
                'temperature_celsius' => $logData['temperature_celsius'],
                'humidity_percent' => $logData['humidity_percent'],
                'ammonia_ppm' => $logData['ammonia_ppm'],
                'rainfall_mm' => $logData['rainfall_mm'],
                'notes' => $logData['notes'],
                'recorded_by' => $recorders->random()->id,
            ]);
        }

        // Update batch with actual current quantity and estimated average weight
        $ageInDays = (int) ($dayCount + 1);
        $estimatedWeight = $this->estimateWeightForAge($ageInDays);

        $batch->update([
            'current_quantity' => $currentQuantity,
            'average_weight_kg' => $estimatedWeight,
        ]);
    }

    /**
     * @return array{mortality_count: int, feed_consumed_kg: float, water_consumed_liters: float, temperature_celsius: float, humidity_percent: float, ammonia_ppm: float, rainfall_mm: float|null, notes: string|null}
     */
    private function calculateDailyLogData(int $dayNumber, int $birdCount, int $initialCount): array
    {
        // Feed consumption increases with age (kg per bird per day)
        $feedPerBird = match (true) {
            $dayNumber <= 7 => fake()->randomFloat(3, 0.012, 0.022),   // Week 1: 12-22g
            $dayNumber <= 14 => fake()->randomFloat(3, 0.028, 0.045), // Week 2: 28-45g
            $dayNumber <= 21 => fake()->randomFloat(3, 0.055, 0.085), // Week 3: 55-85g
            $dayNumber <= 28 => fake()->randomFloat(3, 0.095, 0.125), // Week 4: 95-125g
            $dayNumber <= 35 => fake()->randomFloat(3, 0.135, 0.160), // Week 5: 135-160g
            default => fake()->randomFloat(3, 0.165, 0.185),          // Week 6+: 165-185g
        };

        // Water is roughly 1.8-2x feed consumption
        $waterMultiplier = fake()->randomFloat(2, 1.8, 2.1);

        // Mortality patterns - higher in first week, lower in middle, slight increase at end
        $mortalityRate = match (true) {
            $dayNumber <= 3 => 0.003,   // 0.3% per day (first 3 days critical)
            $dayNumber <= 7 => 0.0015,  // 0.15% per day
            $dayNumber <= 35 => 0.0008, // 0.08% per day (stable period)
            default => 0.001,           // 0.1% per day (late mortality)
        };

        // Add some randomness to mortality
        $mortality = 0;
        if (fake()->boolean((int) ($mortalityRate * 100 * 10))) { // Scale up for boolean
            $mortality = fake()->numberBetween(1, max(1, (int) ($birdCount * $mortalityRate * 3)));
        }

        // Temperature management (lower as birds age and generate more body heat)
        $idealTemp = match (true) {
            $dayNumber <= 3 => 33.0,
            $dayNumber <= 7 => 31.0,
            $dayNumber <= 14 => 28.0,
            $dayNumber <= 21 => 26.0,
            default => 24.0,
        };

        // Generate notes for special events
        $notes = null;
        if ($dayNumber === 1) {
            $notes = 'Day-old chicks placed. Initial health check completed.';
        } elseif ($dayNumber === 7) {
            $notes = 'First week complete. Vaccination administered.';
        } elseif ($dayNumber === 21) {
            $notes = 'Booster vaccination given. Birds looking healthy.';
        } elseif ($mortality > 5) {
            $notes = 'Elevated mortality observed. Checking for disease signs.';
        } elseif (fake()->boolean(5)) {
            $notes = fake()->randomElement([
                'Routine health inspection performed.',
                'Feed texture adjusted.',
                'Ventilation optimized.',
                'Litter quality checked.',
                'Water lines flushed.',
            ]);
        }

        return [
            'mortality_count' => $mortality,
            'feed_consumed_kg' => round($feedPerBird * $birdCount, 2),
            'water_consumed_liters' => round($feedPerBird * $waterMultiplier * $birdCount, 2),
            'temperature_celsius' => round($idealTemp + fake()->randomFloat(1, -1.5, 1.5), 1),
            'humidity_percent' => round(fake()->randomFloat(1, 58, 68), 1),
            'ammonia_ppm' => round(min(25, 5 + ($dayNumber * 0.4) + fake()->randomFloat(1, -2, 2)), 1),
            'rainfall_mm' => fake()->boolean(25) ? round(fake()->randomFloat(1, 0.5, 45), 1) : null,
            'notes' => $notes,
        ];
    }

    private function estimateWeightForAge(int $ageInDays): float
    {
        // Typical broiler growth curve (kg)
        $weightByAge = match (true) {
            $ageInDays <= 7 => 0.15 + ($ageInDays * 0.02),
            $ageInDays <= 14 => 0.30 + (($ageInDays - 7) * 0.04),
            $ageInDays <= 21 => 0.58 + (($ageInDays - 14) * 0.06),
            $ageInDays <= 28 => 1.00 + (($ageInDays - 21) * 0.08),
            $ageInDays <= 35 => 1.56 + (($ageInDays - 28) * 0.09),
            $ageInDays <= 42 => 2.19 + (($ageInDays - 35) * 0.07),
            default => 2.68,
        };

        return round($weightByAge + fake()->randomFloat(2, -0.1, 0.1), 2);
    }
}
