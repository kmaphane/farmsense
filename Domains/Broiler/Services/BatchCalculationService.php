<?php

declare(strict_types=1);

namespace Domains\Broiler\Services;

use Domains\Broiler\Models\Batch;

class BatchCalculationService
{
    /**
     * Calculate Feed Conversion Ratio (FCR)
     * FCR = Total Feed Consumed (kg) / Total Weight Gain (kg)
     * Lower is better (ideal: 1.6-1.9 for broilers)
     */
    public function calculateFCR(Batch $batch): float
    {
        $totalFeed = $batch->dailyLogs->sum('feed_consumed_kg');
        $weightGain = ($batch->average_weight_kg ?? 0) * ($batch->current_quantity ?? 0);

        if ($totalFeed <= 0 || $weightGain <= 0) {
            return 0.0;
        }

        return round($totalFeed / $weightGain, 2);
    }

    /**
     * Calculate European Production Efficiency Factor (EPEF)
     * EPEF = (Liveability% × Avg Weight kg × 100) / (Age days × FCR)
     * Higher is better (ideal: 300-400)
     */
    public function calculateEPEF(Batch $batch): float
    {
        $liveability = $this->calculateLiveability($batch);
        $avgWeight = $batch->average_weight_kg ?? 0;
        $age = $batch->age_in_days;
        $fcr = $this->calculateFCR($batch);

        if ($age <= 0 || $fcr <= 0 || $avgWeight <= 0) {
            return 0.0;
        }

        return round(($liveability * $avgWeight * 100) / ($age * $fcr), 2);
    }

    /**
     * Calculate Mortality Rate
     * Mortality Rate = (Total Deaths / Initial Quantity) × 100
     */
    public function calculateMortalityRate(Batch $batch): float
    {
        if ($batch->initial_quantity <= 0) {
            return 0.0;
        }

        $totalDeaths = $batch->dailyLogs->sum('mortality_count');

        return round(($totalDeaths / $batch->initial_quantity) * 100, 2);
    }

    /**
     * Calculate Liveability (%)
     * Liveability = (Current Quantity / Initial Quantity) × 100
     */
    public function calculateLiveability(Batch $batch): float
    {
        if ($batch->initial_quantity <= 0) {
            return 0.0;
        }

        $currentQuantity = $batch->current_quantity ?? $batch->initial_quantity;

        return round(($currentQuantity / $batch->initial_quantity) * 100, 2);
    }

    /**
     * Calculate Cost Per Bird
     * Total allocated expenses / current quantity
     */
    public function calculateCostPerBird(Batch $batch): int
    {
        $totalCostCents = $batch->expenses->sum('amount_cents');
        $currentQuantity = $batch->current_quantity ?? 0;

        if ($currentQuantity <= 0) {
            return 0;
        }

        return (int) ($totalCostCents / $currentQuantity);
    }

    /**
     * Calculate Cost Per Kg
     * Total cost / total weight
     */
    public function calculateCostPerKg(Batch $batch): int
    {
        $totalCostCents = $batch->expenses->sum('amount_cents');
        $totalWeight = ($batch->average_weight_kg ?? 0) * ($batch->current_quantity ?? 0);

        if ($totalWeight <= 0) {
            return 0;
        }

        return (int) ($totalCostCents / $totalWeight);
    }

    /**
     * Get all batch statistics in one call
     */
    public function getBatchStatistics(Batch $batch): array
    {
        return [
            'age_in_days' => $batch->age_in_days,
            'current_quantity' => $batch->current_quantity ?? $batch->initial_quantity,
            'mortality_rate' => $this->calculateMortalityRate($batch),
            'liveability' => $this->calculateLiveability($batch),
            'average_weight_kg' => $batch->average_weight_kg ?? 0,
            'fcr' => $this->calculateFCR($batch),
            'epef' => $this->calculateEPEF($batch),
            'total_feed_consumed' => $batch->total_feed_consumed,
            'total_mortality' => $batch->total_mortality,
            'cost_per_bird_cents' => $this->calculateCostPerBird($batch),
            'cost_per_kg_cents' => $this->calculateCostPerKg($batch),
        ];
    }
}
