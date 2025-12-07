<?php

namespace App\Http\Controllers\Batches;

use App\Http\Controllers\Controller;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Services\BatchCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BatchAnalyticsController extends Controller
{
    public function __construct(
        private readonly BatchCalculationService $calculationService
    ) {}

    /**
     * Get chart data for feed consumption over time.
     */
    public function feedConsumption(Batch $batch): JsonResponse
    {
        $this->authorizeBatch($batch);

        $logs = $batch->dailyLogs()
            ->oldest('log_date')
            ->get(['log_date', 'feed_consumed_kg']);

        $cumulativeFeed = 0;
        $data = $logs->map(function ($log) use (&$cumulativeFeed) {
            $cumulativeFeed += (float) $log->feed_consumed_kg;

            return [
                'date' => $log->log_date->format('M d'),
                'daily' => (float) $log->feed_consumed_kg,
                'cumulative' => round($cumulativeFeed, 2),
            ];
        });

        return response()->json([
            'labels' => $data->pluck('date'),
            'datasets' => [
                [
                    'label' => 'Daily Feed (kg)',
                    'data' => $data->pluck('daily'),
                ],
                [
                    'label' => 'Cumulative Feed (kg)',
                    'data' => $data->pluck('cumulative'),
                ],
            ],
        ]);
    }

    /**
     * Get chart data for mortality trends.
     */
    public function mortalityTrend(Batch $batch): JsonResponse
    {
        $this->authorizeBatch($batch);

        $logs = $batch->dailyLogs()
            ->oldest('log_date')
            ->get(['log_date', 'mortality_count']);

        $cumulativeMortality = 0;
        $data = $logs->map(function ($log) use (&$cumulativeMortality, $batch) {
            $cumulativeMortality += $log->mortality_count;
            $mortalityRate = ($cumulativeMortality / $batch->initial_bird_count) * 100;

            return [
                'date' => $log->log_date->format('M d'),
                'daily' => $log->mortality_count,
                'cumulative' => $cumulativeMortality,
                'rate' => round($mortalityRate, 2),
            ];
        });

        return response()->json([
            'labels' => $data->pluck('date'),
            'datasets' => [
                [
                    'label' => 'Daily Mortality',
                    'data' => $data->pluck('daily'),
                ],
                [
                    'label' => 'Mortality Rate (%)',
                    'data' => $data->pluck('rate'),
                ],
            ],
        ]);
    }

    /**
     * Get environmental data trends.
     */
    public function environmentalData(Batch $batch): JsonResponse
    {
        $this->authorizeBatch($batch);

        $logs = $batch->dailyLogs()
            ->oldest('log_date')
            ->get(['log_date', 'temperature_celsius', 'humidity_percent', 'ammonia_ppm']);

        $data = $logs->map(fn ($log) => [
            'date' => $log->log_date->format('M d'),
            'temperature' => $log->temperature_celsius ? (float) $log->temperature_celsius : null,
            'humidity' => $log->humidity_percent ? (float) $log->humidity_percent : null,
            'ammonia' => $log->ammonia_ppm ? (float) $log->ammonia_ppm : null,
        ]);

        return response()->json([
            'labels' => $data->pluck('date'),
            'datasets' => [
                [
                    'label' => 'Temperature (°C)',
                    'data' => $data->pluck('temperature'),
                ],
                [
                    'label' => 'Humidity (%)',
                    'data' => $data->pluck('humidity'),
                ],
                [
                    'label' => 'Ammonia (ppm)',
                    'data' => $data->pluck('ammonia'),
                ],
            ],
        ]);
    }

    /**
     * Get batch performance summary.
     */
    public function summary(Batch $batch): JsonResponse
    {
        $this->authorizeBatch($batch);

        $fcr = $this->calculationService->calculateFCR($batch);
        $epef = $this->calculationService->calculateEPEF($batch);
        $mortalityRate = $this->calculationService->calculateMortalityRate($batch);

        return response()->json([
            'fcr' => $fcr,
            'epef' => $epef,
            'mortalityRate' => $mortalityRate,
            'averageWeight' => $batch->average_weight_kg ?? 0,
            'totalFeedConsumed' => $batch->total_feed_consumed,
            'currentBirdCount' => $batch->current_quantity ?? $batch->initial_quantity,
            'initialBirdCount' => $batch->initial_quantity,
            'ageInDays' => $batch->age_in_days,
        ]);
    }

    /**
     * Authorize that the user can access this batch.
     */
    private function authorizeBatch(Batch $batch): void
    {
        abort_if($batch->team_id !== Auth::user()->current_team_id, 403, 'Unauthorized access to this batch.');
    }
}
