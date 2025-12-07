<?php

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Domains\Broiler\Actions\RecordDailyLogAction;
use Domains\Broiler\DTOs\DailyLogData;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Models\DailyLog;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = Team::factory()->create(['owner_id' => $this->user->id]);
    $this->user->update(['current_team_id' => $this->team->id]);
    $this->user->teams()->attach($this->team->id);
});

describe('DailyLog Model', function () {
    it('can be created with factory', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        $log = DailyLog::factory()
            ->forBatch($batch)
            ->recordedBy($this->user)
            ->create();

        expect($log)->toBeInstanceOf(DailyLog::class)
            ->and($log->team_id)->toBe($this->team->id)
            ->and($log->batch_id)->toBe($batch->id)
            ->and($log->recorded_by)->toBe($this->user->id);
    });

    it('belongs to a batch', function () {
        $batch = Batch::factory()->forTeam($this->team)->create();
        $log = DailyLog::factory()->forBatch($batch)->create();

        expect($log->batch)->toBeInstanceOf(Batch::class)
            ->and($log->batch->id)->toBe($batch->id);
    });

    it('belongs to a recorder', function () {
        $batch = Batch::factory()->forTeam($this->team)->create();
        $log = DailyLog::factory()
            ->forBatch($batch)
            ->recordedBy($this->user)
            ->create();

        expect($log->recorder)->toBeInstanceOf(User::class)
            ->and($log->recorder->id)->toBe($this->user->id);
    });
});

describe('DailyLog isEditable', function () {
    it('returns true for today\'s log', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        $log = DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now())
            ->create();

        expect($log->isEditable())->toBeTrue();
    });

    it('returns false for yesterday\'s log', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        $log = DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDay())
            ->create();

        expect($log->isEditable())->toBeFalse();
    });

    it('returns false for older logs', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        $log = DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDays(5))
            ->create();

        expect($log->isEditable())->toBeFalse();
    });
});

describe('RecordDailyLogAction', function () {
    it('creates a daily log successfully', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 1000,
        ]);

        $action = new RecordDailyLogAction;

        $logData = new DailyLogData(
            team_id: $this->team->id,
            batch_id: $batch->id,
            log_date: now(),
            mortality_count: 5,
            feed_consumed_kg: 150.0,
            water_consumed_liters: 280.0,
            temperature_celsius: 32.5,
            humidity_percent: 65.0,
            ammonia_ppm: 12.0,
            notes: 'Normal day',
            recorded_by: $this->user->id,
        );

        $log = $action->execute($logData);

        expect($log)->toBeInstanceOf(DailyLog::class)
            ->and($log->batch_id)->toBe($batch->id)
            ->and($log->mortality_count)->toBe(5)
            ->and((float) $log->feed_consumed_kg)->toBe(150.0);
    });

    it('updates batch current quantity after recording mortality', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 1000,
        ]);

        $action = new RecordDailyLogAction;

        $logData = new DailyLogData(
            team_id: $this->team->id,
            batch_id: $batch->id,
            log_date: now(),
            mortality_count: 15,
            feed_consumed_kg: 150.0,
            water_consumed_liters: null,
            temperature_celsius: null,
            humidity_percent: null,
            ammonia_ppm: null,
            notes: null,
            recorded_by: $this->user->id,
        );

        $action->execute($logData);

        $batch->refresh();

        expect($batch->current_quantity)->toBe(985); // 1000 - 15
    });

    it('prevents negative current quantity', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 100,
            'current_quantity' => 10,
        ]);

        $action = new RecordDailyLogAction;

        $logData = new DailyLogData(
            team_id: $this->team->id,
            batch_id: $batch->id,
            log_date: now(),
            mortality_count: 50, // More than current quantity
            feed_consumed_kg: 50.0,
            water_consumed_liters: null,
            temperature_celsius: null,
            humidity_percent: null,
            ammonia_ppm: null,
            notes: null,
            recorded_by: $this->user->id,
        );

        $action->execute($logData);

        $batch->refresh();

        expect($batch->current_quantity)->toBe(0); // Max 0, not negative
    });

    it('records zero mortality correctly', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 1000,
        ]);

        $action = new RecordDailyLogAction;

        $logData = new DailyLogData(
            team_id: $this->team->id,
            batch_id: $batch->id,
            log_date: now(),
            mortality_count: 0,
            feed_consumed_kg: 150.0,
            water_consumed_liters: null,
            temperature_celsius: null,
            humidity_percent: null,
            ammonia_ppm: null,
            notes: null,
            recorded_by: $this->user->id,
        );

        $log = $action->execute($logData);

        $batch->refresh();

        expect($log->mortality_count)->toBe(0);
        expect($batch->current_quantity)->toBe(1000); // Unchanged
    });
});

describe('DailyLog unique constraint', function () {
    it('only allows one log per day per batch', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now())
            ->create();

        // Trying to create another log for the same date should fail
        expect(fn () => DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now())
            ->create()
        )->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
    });

    it('allows logs on different days', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        $log1 = DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDay())
            ->create();

        $log2 = DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now())
            ->create();

        expect($log1->id)->not->toBe($log2->id);
        expect(DailyLog::query()->where('batch_id', $batch->id)->count())->toBe(2);
    });

    it('allows same date logs for different batches', function () {
        $batch1 = Batch::factory()->forTeam($this->team)->active()->create();
        $batch2 = Batch::factory()->forTeam($this->team)->active()->create();

        $log1 = DailyLog::factory()
            ->forBatch($batch1)
            ->forDate(now())
            ->create();

        $log2 = DailyLog::factory()
            ->forBatch($batch2)
            ->forDate(now())
            ->create();

        expect($log1->id)->not->toBe($log2->id);
    });
});

describe('DailyLog Team Scoping', function () {
    it('filters daily logs by team', function () {
        $otherTeam = Team::factory()->create();

        $batch1 = Batch::factory()->forTeam($this->team)->create();
        $batch2 = Batch::factory()->forTeam($otherTeam)->create();

        DailyLog::factory()
            ->forBatch($batch1)
            ->count(3)
            ->sequence(
                ['log_date' => now()->subDays(2)],
                ['log_date' => now()->subDay()],
                ['log_date' => now()],
            )
            ->create();

        DailyLog::factory()
            ->forBatch($batch2)
            ->count(2)
            ->sequence(
                ['log_date' => now()->subDay()],
                ['log_date' => now()],
            )
            ->create();

        $allLogs = DailyLog::query()->withoutTeamScope()->count();
        expect($allLogs)->toBe(5);

        $teamLogs = DailyLog::query()->where('team_id', $this->team->id)->count();
        expect($teamLogs)->toBe(3);
    });
});

describe('DailyLog Factory States', function () {
    it('creates realistic logs based on age with forAgeDay', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 1000,
        ]);

        // Week 1 log
        $week1Log = DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now())
            ->forAgeDay(5, 1000)
            ->create();

        // Week 4 log
        $week4Log = DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDay())
            ->forAgeDay(25, 950)
            ->create();

        // Week 4 should have higher feed consumption than week 1
        expect((float) $week4Log->feed_consumed_kg)->toBeGreaterThan((float) $week1Log->feed_consumed_kg);
    });
});
