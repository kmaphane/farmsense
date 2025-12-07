<?php

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Models\DailyLog;
use Domains\Broiler\Services\BatchCalculationService;
use Domains\Finance\Models\Expense;

beforeEach(function () {
    $this->service = new BatchCalculationService;
    $this->user = User::factory()->create();
    $this->team = Team::factory()->create(['owner_id' => $this->user->id]);
    $this->user->update(['current_team_id' => $this->team->id]);
});

describe('FCR Calculation Integration', function () {
    it('calculates ideal FCR for broilers (1.6-1.9)', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 960,
            'average_weight_kg' => 2.3,
            'start_date' => now()->subDays(42),
        ]);

        // Simulate realistic 42-day feed consumption
        // Total feed for good FCR: around 3500-4000kg for 1000 birds at 2.3kg
        // FCR 1.8 means total feed = 1.8 * (960 * 2.3) = 3974kg
        $totalFeed = 3974;
        $dailyLogs = 42;
        $avgDailyFeed = $totalFeed / $dailyLogs;

        for ($i = 0; $i < $dailyLogs; $i++) {
            DailyLog::factory()
                ->forBatch($batch)
                ->forDate(now()->subDays($dailyLogs - $i - 1))
                ->create([
                    'feed_consumed_kg' => $avgDailyFeed,
                    'mortality_count' => $i < 10 ? 1 : 0, // Early mortality
                ]);
        }

        $batch->load('dailyLogs');

        $fcr = $this->service->calculateFCR($batch);

        // FCR should be approximately 1.8 (within acceptable range)
        expect($fcr)->toBeGreaterThan(1.5)
            ->and($fcr)->toBeLessThan(2.0);
    });

    it('calculates poor FCR for inefficient operations', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 900,
            'average_weight_kg' => 1.8,
            'start_date' => now()->subDays(42),
        ]);

        // Poor FCR: high feed, low weight gain
        // FCR 2.5+ is poor
        $totalFeed = 4050; // Too much feed for the weight gained
        $dailyLogs = 42;

        for ($i = 0; $i < $dailyLogs; $i++) {
            DailyLog::factory()
                ->forBatch($batch)
                ->forDate(now()->subDays($dailyLogs - $i - 1))
                ->create([
                    'feed_consumed_kg' => $totalFeed / $dailyLogs,
                    'mortality_count' => $i % 4 === 0 ? 3 : 0,
                ]);
        }

        $batch->load('dailyLogs');

        $fcr = $this->service->calculateFCR($batch);

        // Poor FCR should be above 2.0
        expect($fcr)->toBeGreaterThan(2.0);
    });
});

describe('EPEF Calculation Integration', function () {
    it('calculates excellent EPEF (300-400)', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 965,
            'average_weight_kg' => 2.4,
            'start_date' => now()->subDays(42),
        ]);

        // For excellent EPEF around 350:
        // EPEF = (liveability * avgWeight * 100) / (age * FCR)
        // 350 = (96.5 * 2.4 * 100) / (42 * FCR)
        // FCR should be around 1.58
        $targetFCR = 1.58;
        $totalWeight = 965 * 2.4;
        $totalFeed = $targetFCR * $totalWeight;

        for ($i = 0; $i < 42; $i++) {
            DailyLog::factory()
                ->forBatch($batch)
                ->forDate(now()->subDays(41 - $i))
                ->create([
                    'feed_consumed_kg' => $totalFeed / 42,
                    'mortality_count' => $i < 7 ? 1 : 0,
                ]);
        }

        $batch->load('dailyLogs');

        $epef = $this->service->calculateEPEF($batch);

        // EPEF should be in excellent range
        expect($epef)->toBeGreaterThan(280)
            ->and($epef)->toBeLessThan(450);
    });

    it('calculates poor EPEF for underperforming batch', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 850,
            'average_weight_kg' => 1.9,
            'start_date' => now()->subDays(45),
        ]);

        // Poor EPEF: High mortality, low weight, long cycle, high FCR
        $totalFeed = 4000;

        for ($i = 0; $i < 45; $i++) {
            DailyLog::factory()
                ->forBatch($batch)
                ->forDate(now()->subDays(44 - $i))
                ->create([
                    'feed_consumed_kg' => $totalFeed / 45,
                    'mortality_count' => $i % 3 === 0 ? 4 : 0,
                ]);
        }

        $batch->load('dailyLogs');

        $epef = $this->service->calculateEPEF($batch);

        // Poor EPEF should be below 250
        expect($epef)->toBeLessThan(250);
    });
});

describe('Mortality Rate Calculation Integration', function () {
    it('calculates acceptable mortality rate (below 5%)', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 965,
        ]);

        // 35 total deaths over 42 days
        $mortalityPerDay = [3, 2, 2, 1, 1, 1, 1]; // First 7 days higher

        for ($i = 0; $i < 42; $i++) {
            $mortality = $i < 7 ? $mortalityPerDay[$i] : ($i % 10 === 0 ? 1 : 0);
            DailyLog::factory()
                ->forBatch($batch)
                ->forDate(now()->subDays(41 - $i))
                ->create(['mortality_count' => $mortality]);
        }

        $batch->load('dailyLogs');

        $mortalityRate = $this->service->calculateMortalityRate($batch);

        expect($mortalityRate)->toBeLessThan(5.0);
    });

    it('calculates high mortality rate (above 5%)', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 900,
        ]);

        // 100 deaths - disease outbreak scenario
        for ($i = 0; $i < 30; $i++) {
            $mortality = $i >= 10 && $i <= 15 ? 15 : 1; // Spike during disease
            DailyLog::factory()
                ->forBatch($batch)
                ->forDate(now()->subDays(29 - $i))
                ->create(['mortality_count' => $mortality]);
        }

        $batch->load('dailyLogs');

        $mortalityRate = $this->service->calculateMortalityRate($batch);

        expect($mortalityRate)->toBeGreaterThan(5.0);
    });
});

describe('Liveability Calculation Integration', function () {
    it('calculates liveability correctly with mortality progression', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 970,
        ]);

        $liveability = $this->service->calculateLiveability($batch);

        expect($liveability)->toBe(97.0);
    });
});

describe('Cost Calculations Integration', function () {
    it('calculates cost per bird with multiple expense types', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 960,
        ]);

        // Chick purchase: 8000 BWP (800,000 cents)
        Expense::factory()->create([
            'team_id' => $this->team->id,
            'allocatable_id' => $batch->id,
            'allocatable_type' => Batch::class,
            'amount' => 800000,
            'description' => 'Day-old chicks purchase',
        ]);

        // Feed costs: 25000 BWP (2,500,000 cents)
        Expense::factory()->create([
            'team_id' => $this->team->id,
            'allocatable_id' => $batch->id,
            'allocatable_type' => Batch::class,
            'amount' => 2500000,
            'description' => 'Feed costs',
        ]);

        // Medication: 2000 BWP (200,000 cents)
        Expense::factory()->create([
            'team_id' => $this->team->id,
            'allocatable_id' => $batch->id,
            'allocatable_type' => Batch::class,
            'amount' => 200000,
            'description' => 'Medications and vaccines',
        ]);

        // Utilities: 1500 BWP (150,000 cents)
        Expense::factory()->create([
            'team_id' => $this->team->id,
            'allocatable_id' => $batch->id,
            'allocatable_type' => Batch::class,
            'amount' => 150000,
            'description' => 'Utilities',
        ]);

        $batch->load('expenses');

        // Total: 36,500 BWP / 960 birds = 38.02 BWP per bird (3802 cents)
        $costPerBird = $this->service->calculateCostPerBird($batch);

        expect($costPerBird)->toBe(3802);
    });

    it('calculates cost per kg correctly', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 960,
            'average_weight_kg' => 2.5,
        ]);

        // Total cost: 36,500 BWP
        Expense::factory()->create([
            'team_id' => $this->team->id,
            'allocatable_id' => $batch->id,
            'allocatable_type' => Batch::class,
            'amount' => 3650000,
        ]);

        $batch->load('expenses');

        // Total weight: 960 * 2.5 = 2400 kg
        // Cost per kg: 3,650,000 / 2400 = 1520 cents (15.20 BWP)
        $costPerKg = $this->service->calculateCostPerKg($batch);

        expect($costPerKg)->toBe(1520);
    });
});

describe('Complete Batch Statistics', function () {
    it('returns complete statistics for a realistic batch', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 960,
            'average_weight_kg' => 2.3,
            'start_date' => now()->subDays(42),
        ]);

        // Create 42 days of logs
        for ($i = 0; $i < 42; $i++) {
            DailyLog::factory()
                ->forBatch($batch)
                ->forDate(now()->subDays(41 - $i))
                ->forAgeDay($i + 1, 1000 - ($i * 1))
                ->create();
        }

        // Add expenses
        Expense::factory()->create([
            'team_id' => $this->team->id,
            'allocatable_id' => $batch->id,
            'allocatable_type' => Batch::class,
            'amount' => 3500000,
        ]);

        $batch->load(['dailyLogs', 'expenses']);

        $stats = $this->service->getBatchStatistics($batch);

        // Verify all stats are present and reasonable
        expect($stats['age_in_days'])->toBe(42);
        expect($stats['current_quantity'])->toBe(960);
        expect($stats['mortality_rate'])->toBeGreaterThanOrEqual(0);
        expect($stats['liveability'])->toBeGreaterThan(90);
        expect((float) $stats['average_weight_kg'])->toBe(2.3);
        expect($stats['fcr'])->toBeGreaterThan(0);
        expect($stats['epef'])->toBeGreaterThan(0);
        expect($stats['total_feed_consumed'])->toBeGreaterThan(0);
        expect($stats['total_mortality'])->toBeGreaterThanOrEqual(0);
        expect($stats['cost_per_bird_cents'])->toBeGreaterThan(0);
        expect($stats['cost_per_kg_cents'])->toBeGreaterThan(0);
    });
});

describe('Batch with No Data', function () {
    it('handles batch with no daily logs gracefully', function () {
        $batch = Batch::factory()->forTeam($this->team)->planned()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 1000,
            'average_weight_kg' => null,
            'start_date' => now()->addDays(7),
        ]);

        $batch->load(['dailyLogs', 'expenses']);

        $stats = $this->service->getBatchStatistics($batch);

        expect($stats['fcr'])->toBe(0.0);
        expect($stats['epef'])->toBe(0.0);
        expect($stats['mortality_rate'])->toBe(0.0);
        expect($stats['liveability'])->toBe(100.0);
        expect($stats['total_feed_consumed'])->toBe(0.0);
        expect($stats['total_mortality'])->toBe(0);
    });

    it('handles batch with no expenses gracefully', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 980,
            'average_weight_kg' => 2.0,
        ]);

        DailyLog::factory()->forBatch($batch)->create();

        $batch->load(['dailyLogs', 'expenses']);

        expect($this->service->calculateCostPerBird($batch))->toBe(0);
        expect($this->service->calculateCostPerKg($batch))->toBe(0);
    });
});
