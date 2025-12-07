<?php

namespace App\Http\Controllers\Batches;

use App\Http\Controllers\Controller;
use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Services\BatchCalculationService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BatchController extends Controller
{
    public function __construct(
        protected BatchCalculationService $calculationService
    ) {}

    /**
     * Display a listing of active batches for field mode.
     */
    public function index(): Response
    {
        $batches = Batch::query()
            ->where('team_id', Auth::user()->current_team_id)
            ->whereIn('status', [BatchStatus::Active, BatchStatus::Harvesting])
            ->with(['supplier', 'dailyLogs'])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn (Batch $batch) => [
                'id' => $batch->id,
                'name' => $batch->name,
                'age_in_days' => $batch->age_in_days,
                'current_bird_count' => $batch->current_quantity,
                'status' => $batch->status->value,
                'statusLabel' => $batch->status->label(),
                'statusColor' => $batch->status->color(),
                'fcr' => $this->calculationService->calculateFCR($batch) ?: null,
                'mortality_rate' => $this->calculationService->calculateMortalityRate($batch),
            ]);

        return Inertia::render('Batches/Index', [
            'batches' => $batches,
        ]);
    }

    /**
     * Display the specified batch with full details.
     */
    public function show(Batch $batch): Response
    {
        // Authorization: ensure user can only view their team's batches
        abort_if($batch->team_id !== Auth::user()->current_team_id, 403, 'Unauthorized access to this batch.');

        $batch->load(['supplier', 'dailyLogs' => fn ($q) => $q->orderBy('log_date', 'desc')]);

        $stats = $this->calculationService->getBatchStatistics($batch);

        return Inertia::render('Batches/Show', [
            'batch' => [
                'id' => $batch->id,
                'name' => $batch->name,
                'age_in_days' => $batch->age_in_days,
                'current_bird_count' => $batch->current_quantity,
                'initial_bird_count' => $batch->initial_quantity,
                'status' => $batch->status->value,
                'statusLabel' => $batch->status->label(),
                'statusColor' => $batch->status->color(),
                'start_date' => $batch->start_date->toDateString(),
                'target_weight_kg' => (float) $batch->target_weight_kg,
                'supplier' => $batch->supplier ? ['name' => $batch->supplier->name] : null,
            ],
            'stats' => [
                'fcr' => $stats['fcr'] ?: null,
                'epef' => $stats['epef'] ?: null,
                'mortalityRate' => $stats['mortality_rate'],
                'avgDailyGain' => $stats['average_weight_kg'] > 0 && $batch->age_in_days > 0
                    ? round(($stats['average_weight_kg'] * 1000) / $batch->age_in_days, 1)
                    : null,
                'totalFeedConsumed' => (float) $stats['total_feed_consumed'],
            ],
            'dailyLogs' => $batch->dailyLogs->map(fn ($log) => [
                'id' => $log->id,
                'log_date' => $log->log_date->toDateString(),
                'mortality_count' => $log->mortality_count,
                'feed_consumed_kg' => (float) $log->feed_consumed_kg,
                'water_consumed_liters' => $log->water_consumed_liters ? (float) $log->water_consumed_liters : null,
                'temperature_celsius' => $log->temperature_celsius ? (float) $log->temperature_celsius : null,
                'humidity_percent' => $log->humidity_percent ? (float) $log->humidity_percent : null,
                'isEditable' => $log->isEditable(),
            ]),
        ]);
    }
}
