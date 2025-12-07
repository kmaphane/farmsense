<?php

namespace App\Http\Controllers;

use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Models\DailyLog;
use Domains\Broiler\Services\BatchCalculationService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected BatchCalculationService $calculationService
    ) {}

    /**
     * Display the dashboard with stats and recent batches.
     */
    public function __invoke(): Response
    {
        $teamId = Auth::user()->current_team_id;

        // Get active batches
        $activeBatches = Batch::query()
            ->where('team_id', $teamId)
            ->whereIn('status', [BatchStatus::Active, BatchStatus::Harvesting])
            ->get();

        // Calculate stats
        $totalBirds = $activeBatches->sum('current_quantity');

        $fcrValues = $activeBatches
            ->map(fn (Batch $batch) => $this->calculationService->calculateFCR($batch))
            ->filter()
            ->values();

        $avgFCR = $fcrValues->isNotEmpty() ? $fcrValues->avg() : null;

        $mortalityRates = $activeBatches
            ->map(fn (Batch $batch) => $this->calculationService->calculateMortalityRate($batch));

        $avgMortalityRate = $mortalityRates->isNotEmpty() ? $mortalityRates->avg() : 0;

        // Get today's logs count
        $todayLogs = DailyLog::query()
            ->whereHas('batch', fn ($q) => $q->where('team_id', $teamId))
            ->whereDate('log_date', today())
            ->count();

        // Get all active batches with details for display
        $recentBatches = $activeBatches
            ->sortByDesc('start_date')
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
            ])
            ->values();

        // Pending alerts - batches with high mortality (>5%)
        $pendingAlerts = $activeBatches
            ->filter(fn (Batch $batch) => $this->calculationService->calculateMortalityRate($batch) > 5)
            ->count();

        return Inertia::render('Dashboard', [
            'stats' => [
                'activeBatches' => $activeBatches->count(),
                'totalBirds' => $totalBirds,
                'avgFCR' => $avgFCR,
                'avgMortalityRate' => $avgMortalityRate,
                'todayLogs' => $todayLogs,
                'pendingAlerts' => $pendingAlerts,
            ],
            'recentBatches' => $recentBatches,
        ]);
    }
}
