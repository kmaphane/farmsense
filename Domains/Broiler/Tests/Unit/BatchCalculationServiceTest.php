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
});

describe('calculateFCR', function () {
    it('calculates FCR correctly with valid data', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 950,
            'average_weight_kg' => 2.0,
        ]);

        // Create daily logs with total feed of 3800 kg
        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDays(1))
            ->create(['feed_consumed_kg' => 1900]);

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now())
            ->create(['feed_consumed_kg' => 1900]);

        $batch->load('dailyLogs');

        // FCR = Total Feed / Total Weight Gain
        // Total Feed = 3800 kg
        // Total Weight = 950 * 2.0 = 1900 kg
        // FCR = 3800 / 1900 = 2.0
        $fcr = $this->service->calculateFCR($batch);

        expect($fcr)->toBe(2.0);
    });

    it('returns zero when no feed consumed', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 950,
            'average_weight_kg' => 2.0,
        ]);

        $batch->load('dailyLogs');

        $fcr = $this->service->calculateFCR($batch);

        expect($fcr)->toBe(0.0);
    });

    it('returns zero when average weight is zero', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 950,
            'average_weight_kg' => 0,
        ]);

        DailyLog::factory()
            ->forBatch($batch)
            ->create(['feed_consumed_kg' => 100]);

        $batch->load('dailyLogs');

        $fcr = $this->service->calculateFCR($batch);

        expect($fcr)->toBe(0.0);
    });

    it('returns zero when current quantity is zero', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 0,
            'average_weight_kg' => 2.0,
        ]);

        DailyLog::factory()
            ->forBatch($batch)
            ->create(['feed_consumed_kg' => 100]);

        $batch->load('dailyLogs');

        $fcr = $this->service->calculateFCR($batch);

        expect($fcr)->toBe(0.0);
    });
});

describe('calculateEPEF', function () {
    it('calculates EPEF correctly with valid data', function () {
        $startDate = now()->subDays(35);
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 950,
            'average_weight_kg' => 2.0,
            'start_date' => $startDate,
        ]);

        // Total feed for FCR calculation
        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDay())
            ->create(['feed_consumed_kg' => 1900]);

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now())
            ->create(['feed_consumed_kg' => 1900]);

        $batch->load('dailyLogs');

        // Liveability = (950 / 1000) * 100 = 95%
        // Avg Weight = 2.0 kg
        // Age = 35 days
        // FCR = 3800 / (950 * 2.0) = 2.0
        // EPEF = (95 * 2.0 * 100) / (35 * 2.0) = 271.43
        $epef = $this->service->calculateEPEF($batch);

        expect($epef)->toBeGreaterThan(200)
            ->and($epef)->toBeLessThan(350);
    });

    it('returns zero when FCR is zero', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 950,
            'average_weight_kg' => 0,
            'start_date' => now()->subDays(35),
        ]);

        $batch->load('dailyLogs');

        $epef = $this->service->calculateEPEF($batch);

        expect($epef)->toBe(0.0);
    });

    it('returns zero when age is zero', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 950,
            'average_weight_kg' => 2.0,
            'start_date' => now(),
        ]);

        $batch->load('dailyLogs');

        $epef = $this->service->calculateEPEF($batch);

        expect($epef)->toBe(0.0);
    });
});

describe('calculateMortalityRate', function () {
    it('calculates mortality rate correctly', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
        ]);

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDays(2))
            ->create(['mortality_count' => 10]);

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDay())
            ->create(['mortality_count' => 15]);

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now())
            ->create(['mortality_count' => 25]);

        $batch->load('dailyLogs');

        // Mortality Rate = (50 / 1000) * 100 = 5%
        $mortalityRate = $this->service->calculateMortalityRate($batch);

        expect($mortalityRate)->toBe(5.0);
    });

    it('returns zero when no mortality', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
        ]);

        DailyLog::factory()
            ->forBatch($batch)
            ->create(['mortality_count' => 0]);

        $batch->load('dailyLogs');

        $mortalityRate = $this->service->calculateMortalityRate($batch);

        expect($mortalityRate)->toBe(0.0);
    });

    it('returns zero when initial quantity is zero', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 0,
        ]);

        $batch->load('dailyLogs');

        $mortalityRate = $this->service->calculateMortalityRate($batch);

        expect($mortalityRate)->toBe(0.0);
    });
});

describe('calculateLiveability', function () {
    it('calculates liveability correctly', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 950,
        ]);

        // Liveability = (950 / 1000) * 100 = 95%
        $liveability = $this->service->calculateLiveability($batch);

        expect($liveability)->toBe(95.0);
    });

    it('returns 100% when no mortality', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 1000,
        ]);

        $liveability = $this->service->calculateLiveability($batch);

        expect($liveability)->toBe(100.0);
    });

    it('uses initial quantity when current is null', function () {
        $batch = Batch::factory()->forTeam($this->team)->planned()->create([
            'initial_quantity' => 1000,
            'current_quantity' => null,
        ]);

        $liveability = $this->service->calculateLiveability($batch);

        expect($liveability)->toBe(100.0);
    });
});

describe('calculateCostPerBird', function () {
    it('calculates cost per bird correctly', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 950,
        ]);

        // Create expenses totaling 47,500 BWP (4,750,000 cents)
        Expense::factory()->create([
            'team_id' => $this->team->id,
            'allocatable_id' => $batch->id,
            'allocatable_type' => Batch::class,
            'amount' => 2500000, // 25,000 BWP
        ]);

        Expense::factory()->create([
            'team_id' => $this->team->id,
            'allocatable_id' => $batch->id,
            'allocatable_type' => Batch::class,
            'amount' => 2250000, // 22,500 BWP
        ]);

        $batch->load('expenses');

        // Cost per bird = 4,750,000 / 950 = 5000 cents (50 BWP)
        $costPerBird = $this->service->calculateCostPerBird($batch);

        expect($costPerBird)->toBe(5000);
    });

    it('returns zero when no expenses', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'current_quantity' => 950,
        ]);

        $batch->load('expenses');

        $costPerBird = $this->service->calculateCostPerBird($batch);

        expect($costPerBird)->toBe(0);
    });

    it('returns zero when current quantity is zero', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'current_quantity' => 0,
        ]);

        Expense::factory()->create([
            'team_id' => $this->team->id,
            'allocatable_id' => $batch->id,
            'allocatable_type' => Batch::class,
            'amount' => 100000,
        ]);

        $batch->load('expenses');

        $costPerBird = $this->service->calculateCostPerBird($batch);

        expect($costPerBird)->toBe(0);
    });
});

describe('calculateCostPerKg', function () {
    it('calculates cost per kg correctly', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 950,
            'average_weight_kg' => 2.0,
        ]);

        Expense::factory()->create([
            'team_id' => $this->team->id,
            'allocatable_id' => $batch->id,
            'allocatable_type' => Batch::class,
            'amount' => 4750000, // 47,500 BWP
        ]);

        $batch->load('expenses');

        // Total weight = 950 * 2.0 = 1900 kg
        // Cost per kg = 4,750,000 / 1900 = 2500 cents (25 BWP)
        $costPerKg = $this->service->calculateCostPerKg($batch);

        expect($costPerKg)->toBe(2500);
    });

    it('returns zero when total weight is zero', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'current_quantity' => 950,
            'average_weight_kg' => 0,
        ]);

        Expense::factory()->create([
            'team_id' => $this->team->id,
            'allocatable_id' => $batch->id,
            'allocatable_type' => Batch::class,
            'amount' => 100000,
        ]);

        $batch->load('expenses');

        $costPerKg = $this->service->calculateCostPerKg($batch);

        expect($costPerKg)->toBe(0);
    });
});

describe('getBatchStatistics', function () {
    it('returns all statistics as an array', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 950,
            'average_weight_kg' => 2.0,
            'start_date' => now()->subDays(30),
        ]);

        DailyLog::factory()
            ->forBatch($batch)
            ->create(['feed_consumed_kg' => 100, 'mortality_count' => 5]);

        $batch->load(['dailyLogs', 'expenses']);

        $stats = $this->service->getBatchStatistics($batch);

        expect($stats)->toBeArray()
            ->toHaveKeys([
                'age_in_days',
                'current_quantity',
                'mortality_rate',
                'liveability',
                'average_weight_kg',
                'fcr',
                'epef',
                'total_feed_consumed',
                'total_mortality',
                'cost_per_bird_cents',
                'cost_per_kg_cents',
            ]);

        expect($stats['age_in_days'])->toBe(30);
        expect($stats['current_quantity'])->toBe(950);
    });
});
