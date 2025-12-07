<?php

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Models\DailyLog;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = Team::factory()->create(['owner_id' => $this->user->id]);
    $this->user->update(['current_team_id' => $this->team->id]);
    $this->user->teams()->attach($this->team->id);
});

describe('BatchController::index', function () {
    it('requires authentication', function () {
        $this->get(route('batches.index'))
            ->assertRedirect('/login');
    })->skip('Requires auth middleware configuration for redirect');

    it('displays active batches for the current team', function () {
        Batch::factory()->forTeam($this->team)->active()->count(2)->create();
        Batch::factory()->forTeam($this->team)->harvesting()->create();
        Batch::factory()->forTeam($this->team)->planned()->create();
        Batch::factory()->forTeam($this->team)->closed()->create();

        $response = $this->actingAs($this->user)
            ->get(route('batches.index'));

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Batches/Index')
                ->has('batches', 3) // Only active and harvesting
            );
    });

    it('only shows batches from the current team', function () {
        $otherTeam = Team::factory()->create();

        Batch::factory()->forTeam($this->team)->active()->count(2)->create();
        Batch::factory()->forTeam($otherTeam)->active()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->get(route('batches.index'));

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->has('batches', 2)
            );
    });

    it('includes batch metrics in response', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 980,
        ]);

        DailyLog::factory()->forBatch($batch)->create([
            'mortality_count' => 5,
            'feed_consumed_kg' => 100,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('batches.index'));

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Batches/Index')
                ->has('batches.0', fn ($batch) => $batch
                    ->has('id')
                    ->has('name')
                    ->has('age_in_days')
                    ->has('current_bird_count')
                    ->has('status')
                    ->has('statusLabel')
                    ->has('statusColor')
                    ->has('mortality_rate')
                    ->has('fcr')
                    ->etc()
                )
            );
    });
});

describe('BatchController::show', function () {
    it('requires authentication', function () {
        $batch = Batch::factory()->forTeam($this->team)->create();

        $this->get(route('batches.show', $batch))
            ->assertRedirect('/login');
    })->skip('Requires auth middleware configuration for redirect');

    it('displays batch details', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 980,
            'target_weight_kg' => 2.5,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('batches.show', $batch));

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Batches/Show')
                ->has('batch', fn ($b) => $b
                    ->where('id', $batch->id)
                    ->where('name', $batch->name)
                    ->where('current_bird_count', 980)
                    ->where('initial_bird_count', 1000)
                    ->etc()
                )
                ->has('stats')
                ->has('dailyLogs')
            );
    });

    it('returns 404 for batches from other teams due to global scoping', function () {
        $otherTeam = Team::factory()->create();
        $batch = Batch::factory()->forTeam($otherTeam)->create();

        $this->actingAs($this->user)
            ->get(route('batches.show', $batch))
            ->assertNotFound();
    });

    it('includes daily logs ordered by date descending', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDays(2))
            ->create();

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDay())
            ->create();

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now())
            ->create();

        $response = $this->actingAs($this->user)
            ->get(route('batches.show', $batch));

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->has('dailyLogs', 3)
            );
    });
});

describe('DailyLogController::create', function () {
    it('requires authentication', function () {
        $batch = Batch::factory()->forTeam($this->team)->create();

        $this->get(route('batches.logs.create', $batch))
            ->assertRedirect('/login');
    })->skip('Requires auth middleware configuration for redirect');

    it('displays the create daily log form', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        $response = $this->actingAs($this->user)
            ->get(route('batches.logs.create', $batch));

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Batches/DailyLog/Create')
                ->has('batch', fn ($b) => $b
                    ->where('id', $batch->id)
                    ->where('name', $batch->name)
                    ->etc()
                )
                ->has('suggestedDate')
            );
    });

    it('returns 404 for batches from other teams due to global scoping', function () {
        $otherTeam = Team::factory()->create();
        $batch = Batch::factory()->forTeam($otherTeam)->create();

        $this->actingAs($this->user)
            ->get(route('batches.logs.create', $batch))
            ->assertNotFound();
    });

    it('includes last log data for reference', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDay())
            ->create([
                'feed_consumed_kg' => 150.50,
                'water_consumed_liters' => 280.00,
            ]);

        $response = $this->actingAs($this->user)
            ->get(route('batches.logs.create', $batch));

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->has('lastLog', fn ($log) => $log
                    ->where('feed_consumed_kg', 150.50)
                    ->where('water_consumed_liters', 280) // Integer from DB
                    ->etc()
                )
            );
    });
});

describe('DailyLogController::store', function () {
    it('requires authentication', function () {
        $batch = Batch::factory()->forTeam($this->team)->create();

        $this->post(route('batches.logs.store', $batch), [])
            ->assertRedirect('/login');
    })->skip('Requires auth middleware configuration');

    it('creates a daily log successfully', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 1000,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('batches.logs.store', $batch), [
                'log_date' => now()->toDateString(),
                'mortality_count' => 5,
                'feed_consumed_kg' => 150.50,
                'water_consumed_liters' => 280.00,
                'temperature_celsius' => 32.5,
                'humidity_percent' => 65.0,
                'ammonia_ppm' => 12.0,
                'notes' => 'Normal day',
            ]);

        $response->assertRedirect(route('batches.show', $batch))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('daily_logs', [
            'batch_id' => $batch->id,
            'mortality_count' => 5,
        ]);
    })->skip('Requires DailyLogController store implementation');

    it('validates required fields', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        $response = $this->actingAs($this->user)
            ->post(route('batches.logs.store', $batch), []);

        $response->assertSessionHasErrors(['log_date', 'mortality_count', 'feed_consumed_kg']);
    })->skip('Requires DailyLogController store implementation');

    it('validates log_date is not in the future', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        $response = $this->actingAs($this->user)
            ->post(route('batches.logs.store', $batch), [
                'log_date' => now()->addDay()->toDateString(),
                'mortality_count' => 5,
                'feed_consumed_kg' => 150.00,
            ]);

        $response->assertSessionHasErrors(['log_date']);
    })->skip('Requires DailyLogController store implementation');

    it('validates unique log per day per batch', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now())
            ->create();

        $response = $this->actingAs($this->user)
            ->post(route('batches.logs.store', $batch), [
                'log_date' => now()->toDateString(),
                'mortality_count' => 5,
                'feed_consumed_kg' => 150.00,
            ]);

        $response->assertSessionHasErrors(['log_date']);
    })->skip('Requires DailyLogController store implementation');

    it('validates mortality_count is non-negative', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        $response = $this->actingAs($this->user)
            ->post(route('batches.logs.store', $batch), [
                'log_date' => now()->toDateString(),
                'mortality_count' => -5,
                'feed_consumed_kg' => 150.00,
            ]);

        $response->assertSessionHasErrors(['mortality_count']);
    })->skip('Requires DailyLogController store implementation');

    it('validates feed_consumed_kg is non-negative', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        $response = $this->actingAs($this->user)
            ->post(route('batches.logs.store', $batch), [
                'log_date' => now()->toDateString(),
                'mortality_count' => 5,
                'feed_consumed_kg' => -150.00,
            ]);

        $response->assertSessionHasErrors(['feed_consumed_kg']);
    })->skip('Requires DailyLogController store implementation');

    it('returns 404 for batches from other teams due to global scoping', function () {
        $otherTeam = Team::factory()->create();
        $batch = Batch::factory()->forTeam($otherTeam)->create();

        $this->actingAs($this->user)
            ->post(route('batches.logs.store', $batch), [
                'log_date' => now()->toDateString(),
                'mortality_count' => 5,
                'feed_consumed_kg' => 150.00,
            ])
            ->assertNotFound();
    })->skip('Requires DailyLogController store implementation');
});

describe('DailyLogController::edit', function () {
    it('allows editing today\'s log', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();
        $log = DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now())
            ->create();

        $response = $this->actingAs($this->user)
            ->get(route('batches.logs.edit', [$batch, $log]));

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Batches/DailyLog/Edit')
                ->has('dailyLog')
            );
    });

    it('returns 403 for yesterday\'s log (immutable after 24h)', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();
        $log = DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDay())
            ->create();

        $this->actingAs($this->user)
            ->get(route('batches.logs.edit', [$batch, $log]))
            ->assertForbidden();
    });
});

describe('BatchAnalyticsController', function () {
    it('returns feed consumption chart data', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDays(2))
            ->create(['feed_consumed_kg' => 100]);

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDay())
            ->create(['feed_consumed_kg' => 120]);

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now())
            ->create(['feed_consumed_kg' => 140]);

        $response = $this->actingAs($this->user)
            ->get(route('batches.analytics.feed', $batch));

        $response->assertSuccessful()
            ->assertJsonStructure([
                'labels',
                'datasets' => [
                    ['label', 'data'],
                    ['label', 'data'],
                ],
            ]);
    });

    it('returns mortality trend chart data', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
        ]);

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDays(2))
            ->create(['mortality_count' => 3]);

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now()->subDay())
            ->create(['mortality_count' => 2]);

        $response = $this->actingAs($this->user)
            ->get(route('batches.analytics.mortality', $batch));

        $response->assertSuccessful()
            ->assertJsonStructure([
                'labels',
                'datasets' => [
                    ['label', 'data'],
                    ['label', 'data'],
                ],
            ]);
    });

    it('returns environmental data', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create();

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now())
            ->create([
                'temperature_celsius' => 32.5,
                'humidity_percent' => 65.0,
                'ammonia_ppm' => 12.0,
            ]);

        $response = $this->actingAs($this->user)
            ->get(route('batches.analytics.environment', $batch));

        $response->assertSuccessful();
    });

    it('returns summary analytics', function () {
        $batch = Batch::factory()->forTeam($this->team)->active()->create([
            'initial_quantity' => 1000,
            'current_quantity' => 980,
            'average_weight_kg' => 2.0,
            'start_date' => now()->subDays(30),
        ]);

        DailyLog::factory()
            ->forBatch($batch)
            ->forDate(now())
            ->create(['feed_consumed_kg' => 100, 'mortality_count' => 5]);

        $response = $this->actingAs($this->user)
            ->get(route('batches.analytics.summary', $batch));

        $response->assertSuccessful()
            ->assertJsonStructure([
                'fcr',
                'epef',
                'mortalityRate',
                'averageWeight',
                'totalFeedConsumed',
                'currentBirdCount',
                'initialBirdCount',
                'ageInDays',
            ]);
    });

    it('returns 404 for batches from other teams due to global scoping', function () {
        $otherTeam = Team::factory()->create();
        $batch = Batch::factory()->forTeam($otherTeam)->create();

        $this->actingAs($this->user)
            ->get(route('batches.analytics.feed', $batch))
            ->assertNotFound();
    });
});
