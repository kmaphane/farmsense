<?php

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Domains\Broiler\Actions\CloseBatchAction;
use Domains\Broiler\Actions\CreateBatchAction;
use Domains\Broiler\DTOs\BatchData;
use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Services\BatchCalculationService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = Team::factory()->create(['owner_id' => $this->user->id]);
    $this->user->update(['current_team_id' => $this->team->id]);
    $this->user->teams()->attach($this->team->id);
});

describe('Batch Model', function () {
    it('can be created with factory', function () {
        $batch = Batch::factory()->forTeam($this->team)->create();

        expect($batch)->toBeInstanceOf(Batch::class)
            ->and($batch->team_id)->toBe($this->team->id)
            ->and($batch->name)->not->toBeEmpty()
            ->and($batch->batch_number)->not->toBeEmpty();
    });

    it('can have different statuses', function () {
        $planned = Batch::factory()->forTeam($this->team)->planned()->create();
        $active = Batch::factory()->forTeam($this->team)->active()->create();
        $harvesting = Batch::factory()->forTeam($this->team)->harvesting()->create();
        $closed = Batch::factory()->forTeam($this->team)->closed()->create();

        expect($planned->status)->toBe(BatchStatus::Planned);
        expect($active->status)->toBe(BatchStatus::Active);
        expect($harvesting->status)->toBe(BatchStatus::Harvesting);
        expect($closed->status)->toBe(BatchStatus::Closed);
    });

    it('calculates age in days correctly', function () {
        $batch = Batch::factory()->forTeam($this->team)->create([
            'start_date' => now()->subDays(30),
        ]);

        expect($batch->age_in_days)->toBe(30);
    });

    it('returns zero age when batch just started today', function () {
        $batch = Batch::factory()->forTeam($this->team)->create([
            'start_date' => now(),
        ]);

        expect($batch->age_in_days)->toBe(0);
    });

    it('calculates total mortality from daily logs', function () {
        $batch = Batch::factory()->forTeam($this->team)->create();

        \Domains\Broiler\Models\DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDays(2))
            ->create(['mortality_count' => 5]);

        \Domains\Broiler\Models\DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDay())
            ->create(['mortality_count' => 3]);

        $batch->load('dailyLogs');

        expect($batch->total_mortality)->toBe(8);
    });

    it('calculates total feed consumed from daily logs', function () {
        $batch = Batch::factory()->forTeam($this->team)->create();

        \Domains\Broiler\Models\DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDays(2))
            ->create(['feed_consumed_kg' => 100.50]);

        \Domains\Broiler\Models\DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDay())
            ->create(['feed_consumed_kg' => 150.25]);

        $batch->load('dailyLogs');

        expect($batch->total_feed_consumed)->toBe(250.75);
    });
});

describe('CreateBatchAction', function () {
    it('creates a batch with planned status', function () {
        $action = new CreateBatchAction;

        $batchData = new BatchData(
            team_id: $this->team->id,
            name: 'Test Batch 2025',
            batch_number: 'BRO-2025-001',
            start_date: now()->addDays(3),
            expected_end_date: now()->addDays(45),
            actual_end_date: null,
            status: BatchStatus::Active, // Should be overridden to Planned
            initial_quantity: 1000,
            current_quantity: null,
            supplier_id: null,
            target_weight_kg: 2.5,
            average_weight_kg: null,
        );

        $batch = $action->execute($batchData);

        expect($batch->status)->toBe(BatchStatus::Planned);
        expect($batch->current_quantity)->toBe(1000);
        expect($batch->team_id)->toBe($this->team->id);
        expect($batch->name)->toBe('Test Batch 2025');
    });

    it('generates unique batch numbers', function () {
        $action = new CreateBatchAction;

        $number1 = $action->generateBatchNumber($this->team->id);
        expect($number1)->toStartWith('BRO-'.now()->year.'-');

        // Create a batch with the first number
        Batch::factory()->forTeam($this->team)->create([
            'batch_number' => $number1,
        ]);

        $number2 = $action->generateBatchNumber($this->team->id);

        expect($number2)->not->toBe($number1);
    });
});

describe('Batch Status Transitions', function () {
    it('transitions from planned to active', function () {
        $batch = Batch::factory()->forTeam($this->team)->planned()->create([
            'start_date' => now(),
            'initial_quantity' => 1000,
            'current_quantity' => null, // Should be set by transitionToActive
        ]);

        $action = new CloseBatchAction(new BatchCalculationService);
        $updatedBatch = $action->transitionToActive($batch);

        expect($updatedBatch->status)->toBe(BatchStatus::Active);
        expect($updatedBatch->current_quantity)->toBe(1000);
    });

    it('transitions from active to harvesting', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        $action = new CloseBatchAction(new BatchCalculationService);
        $updatedBatch = $action->transitionToHarvesting($batch);

        expect($updatedBatch->status)->toBe(BatchStatus::Harvesting);
    });

    it('transitions from harvesting to closed', function () {
        // Create batch with 0 remaining birds (fully sold/slaughtered)
        $batch = Batch::factory()->forTeam($this->team)->harvesting()->create([
            'current_quantity' => 0,
        ]);

        $action = new CloseBatchAction(new BatchCalculationService);
        $closedBatch = $action->execute($batch, 2.35);

        expect($closedBatch->status)->toBe(BatchStatus::Closed);
        expect($closedBatch->actual_end_date)->not->toBeNull();
        expect((float) $closedBatch->average_weight_kg)->toBe(2.35);
    });

    it('transitions from harvesting to closed with remaining birds and closure reason', function () {
        $batch = Batch::factory()->forTeam($this->team)->harvesting()->create([
            'current_quantity' => 10, // Birds remaining
        ]);

        $action = new CloseBatchAction(new BatchCalculationService);
        $closedBatch = $action->execute(
            batch: $batch,
            averageWeightKg: 2.35,
            manureBagsCollected: 5,
            closureReason: \Domains\Broiler\Enums\DiscrepancyReason::HouseholdConsumption,
            closureNotes: '10 birds kept for household',
        );

        expect($closedBatch->status)->toBe(BatchStatus::Closed);
        expect($closedBatch->manure_bags_collected)->toBe(5);
        expect($closedBatch->closure_reason)->toBe(\Domains\Broiler\Enums\DiscrepancyReason::HouseholdConsumption);
        expect($closedBatch->closure_notes)->toBe('10 birds kept for household');
    });

    it('requires closure reason when birds remain in batch', function () {
        $batch = Batch::factory()->forTeam($this->team)->harvesting()->create([
            'current_quantity' => 10, // Birds remaining
        ]);

        $action = new CloseBatchAction(new BatchCalculationService);

        expect(fn () => $action->execute($batch, 2.35))
            ->toThrow(InvalidArgumentException::class, 'Closure reason is required when birds remain in the batch.');
    });

    it('throws exception when transitioning from planned directly to harvesting', function () {
        $batch = Batch::factory()->forTeam($this->team)->planned()->create();

        $action = new CloseBatchAction(new BatchCalculationService);

        expect(fn () => $action->transitionToHarvesting($batch))
            ->toThrow(InvalidArgumentException::class, 'Only active batches can transition to Harvesting status.');
    });

    it('throws exception when transitioning from active directly to closed', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        $action = new CloseBatchAction(new BatchCalculationService);

        expect(fn () => $action->execute($batch, 2.35))
            ->toThrow(InvalidArgumentException::class, 'Only batches in Harvesting status can be closed.');
    });

    it('validates start date requirement when activating batch', function () {
        // NOTE: The database schema enforces NOT NULL on start_date,
        // so the validation in transitionToActive() is a secondary check.
        // This test verifies the action validates the requirement.
        $batch = Batch::factory()->forTeam($this->team)->planned()->create([
            'start_date' => now(),
            'initial_quantity' => 1000,
        ]);

        // Manually unset start_date in memory (bypass DB constraint for this test)
        $batch->start_date = null;

        $action = new CloseBatchAction(new BatchCalculationService);

        expect(fn () => $action->transitionToActive($batch))
            ->toThrow(InvalidArgumentException::class, 'Batch must have a start date and initial quantity to become active.');
    });

    it('validates initial quantity requirement when activating batch', function () {
        // NOTE: The database schema enforces NOT NULL on initial_quantity,
        // so the validation in transitionToActive() is a secondary check.
        $batch = Batch::factory()->forTeam($this->team)->planned()->create([
            'start_date' => now(),
            'initial_quantity' => 1000,
        ]);

        // Manually unset initial_quantity in memory (bypass DB constraint for this test)
        $batch->initial_quantity = null;

        $action = new CloseBatchAction(new BatchCalculationService);

        expect(fn () => $action->transitionToActive($batch))
            ->toThrow(InvalidArgumentException::class, 'Batch must have a start date and initial quantity to become active.');
    });
});

describe('Batch Team Scoping', function () {
    it('filters batches by team', function () {
        $otherTeam = Team::factory()->create();

        Batch::factory()->forTeam($this->team)->count(3)->create();
        Batch::factory()->forTeam($otherTeam)->count(2)->create();

        // Query without scoping (for testing)
        $allBatches = Batch::query()->withoutTeamScope()->count();
        expect($allBatches)->toBe(5);

        // Query with team scoping
        $teamBatches = Batch::query()->where('team_id', $this->team->id)->count();
        expect($teamBatches)->toBe(3);
    });
});

describe('Batch Relationships', function () {
    it('has many daily logs', function () {
        $batch = Batch::factory()->forTeam($this->team)->create();

        \Domains\Broiler\Models\DailyLog::factory()
            ->forBatch($batch)
            ->count(5)
            ->sequence(
                ['log_date' => now()->subDays(4)],
                ['log_date' => now()->subDays(3)],
                ['log_date' => now()->subDays(2)],
                ['log_date' => now()->subDay()],
                ['log_date' => now()],
            )
            ->create();

        expect($batch->dailyLogs)->toHaveCount(5);
    });

    it('can have allocated expenses', function () {
        $batch = Batch::factory()->forTeam($this->team)->create();

        \Domains\Finance\Models\Expense::factory()->create([
            'team_id' => $this->team->id,
            'allocatable_id' => $batch->id,
            'allocatable_type' => Batch::class,
        ]);

        \Domains\Finance\Models\Expense::factory()->create([
            'team_id' => $this->team->id,
            'allocatable_id' => $batch->id,
            'allocatable_type' => Batch::class,
        ]);

        $batch->load('expenses');

        expect($batch->expenses)->toHaveCount(2);
    });
});
