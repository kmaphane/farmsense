<?php

namespace App\Http\Controllers\Batches;

use App\Http\Controllers\Controller;
use App\Http\Requests\Batches\StoreDailyLogRequest;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Models\DailyLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DailyLogController extends Controller
{
    /**
     * Display a listing of all daily logs.
     */
    public function index(): Response
    {
        $teamId = Auth::user()->current_team_id;

        $dailyLogs = DailyLog::query()
            ->where('team_id', $teamId)
            ->with(['batch', 'recorder'])
            ->orderBy('log_date', 'desc')
            ->paginate(15);

        return Inertia::render('DailyLogs/Index', [
            'dailyLogs' => $dailyLogs->through(fn ($log) => [
                'id' => $log->id,
                'log_date' => $log->log_date->format('M d, Y'),
                'batch_name' => $log->batch->name,
                'batch_id' => $log->batch_id,
                'mortality_count' => $log->mortality_count,
                'feed_consumed_kg' => (float) $log->feed_consumed_kg,
                'water_consumed_liters' => $log->water_consumed_liters ? (float) $log->water_consumed_liters : null,
                'temperature_celsius' => $log->temperature_celsius ? (float) $log->temperature_celsius : null,
                'recorded_by' => $log->recorder->name,
            ]),
            'pagination' => [
                'current_page' => $dailyLogs->currentPage(),
                'last_page' => $dailyLogs->lastPage(),
                'per_page' => $dailyLogs->perPage(),
                'total' => $dailyLogs->total(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new daily log.
     */
    public function create(Batch $batch): Response
    {
        $this->authorizeBatch($batch);

        // Get the last log entry for reference
        $lastLog = $batch->dailyLogs()
            ->latest('log_date')
            ->first();

        // Calculate suggested date (today or next day after last log)
        $suggestedDate = now()->toDateString();

        return Inertia::render('Batches/DailyLog/Create', [
            'batch' => [
                'id' => $batch->id,
                'name' => $batch->name,
                'age_in_days' => $batch->age_in_days,
                'current_bird_count' => $batch->current_bird_count,
                'status' => $batch->status->value,
                'statusLabel' => $batch->status->label(),
            ],
            'lastLog' => $lastLog ? [
                'log_date' => $lastLog->log_date->toDateString(),
                'mortality_count' => $lastLog->mortality_count,
                'feed_consumed_kg' => (float) $lastLog->feed_consumed_kg,
                'water_consumed_liters' => $lastLog->water_consumed_liters ? (float) $lastLog->water_consumed_liters : null,
                'temperature_celsius' => $lastLog->temperature_celsius ? (float) $lastLog->temperature_celsius : null,
                'humidity_percent' => $lastLog->humidity_percent ? (float) $lastLog->humidity_percent : null,
            ] : null,
            'suggestedDate' => $suggestedDate,
        ]);
    }

    /**
     * Store a newly created daily log in storage.
     */
    public function store(StoreDailyLogRequest $request, Batch $batch): RedirectResponse
    {
        $validated = $request->validated();

        DailyLog::query()->create([
            'team_id' => Auth::user()->current_team_id,
            'batch_id' => $batch->id,
            'log_date' => $validated['log_date'],
            'mortality_count' => $validated['mortality_count'],
            'feed_consumed_kg' => $validated['feed_consumed_kg'],
            'water_consumed_liters' => $validated['water_consumed_liters'] ?? null,
            'temperature_celsius' => $validated['temperature_celsius'] ?? null,
            'humidity_percent' => $validated['humidity_percent'] ?? null,
            'ammonia_ppm' => $validated['ammonia_ppm'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => Auth::id(),
        ]);

        // Update batch's current bird count based on mortality
        $batch->decrement('current_bird_count', $validated['mortality_count']);

        return to_route('batches.show', $batch)
            ->with('success', 'Daily log recorded successfully.');
    }

    /**
     * Show the form for editing an existing daily log.
     */
    public function edit(Batch $batch, DailyLog $dailyLog): Response
    {
        $this->authorizeBatch($batch);

        abort_unless($dailyLog->isEditable(), 403, 'This log entry can no longer be edited.');

        return Inertia::render('Batches/DailyLog/Edit', [
            'batch' => [
                'id' => $batch->id,
                'name' => $batch->name,
                'age_in_days' => $batch->age_in_days,
            ],
            'dailyLog' => [
                'id' => $dailyLog->id,
                'log_date' => $dailyLog->log_date->toDateString(),
                'mortality_count' => $dailyLog->mortality_count,
                'feed_consumed_kg' => (float) $dailyLog->feed_consumed_kg,
                'water_consumed_liters' => $dailyLog->water_consumed_liters ? (float) $dailyLog->water_consumed_liters : null,
                'temperature_celsius' => $dailyLog->temperature_celsius ? (float) $dailyLog->temperature_celsius : null,
                'humidity_percent' => $dailyLog->humidity_percent ? (float) $dailyLog->humidity_percent : null,
                'ammonia_ppm' => $dailyLog->ammonia_ppm ? (float) $dailyLog->ammonia_ppm : null,
                'notes' => $dailyLog->notes,
            ],
        ]);
    }

    /**
     * Update the specified daily log in storage.
     */
    public function update(StoreDailyLogRequest $request, Batch $batch, DailyLog $dailyLog): RedirectResponse
    {
        abort_unless($dailyLog->isEditable(), 403, 'This log entry can no longer be edited.');

        $validated = $request->validated();

        // Calculate mortality difference to update bird count
        $mortalityDiff = $validated['mortality_count'] - $dailyLog->mortality_count;

        $dailyLog->update([
            'log_date' => $validated['log_date'],
            'mortality_count' => $validated['mortality_count'],
            'feed_consumed_kg' => $validated['feed_consumed_kg'],
            'water_consumed_liters' => $validated['water_consumed_liters'] ?? null,
            'temperature_celsius' => $validated['temperature_celsius'] ?? null,
            'humidity_percent' => $validated['humidity_percent'] ?? null,
            'ammonia_ppm' => $validated['ammonia_ppm'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Adjust batch bird count if mortality changed
        if ($mortalityDiff !== 0) {
            $batch->decrement('current_bird_count', $mortalityDiff);
        }

        return to_route('batches.show', $batch)
            ->with('success', 'Daily log updated successfully.');
    }

    /**
     * Authorize that the user can access this batch.
     */
    private function authorizeBatch(Batch $batch): void
    {
        abort_if($batch->team_id !== Auth::user()->current_team_id, 403, 'Unauthorized access to this batch.');
    }
}
