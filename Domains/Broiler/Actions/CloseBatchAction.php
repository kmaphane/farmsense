<?php

declare(strict_types=1);

namespace Domains\Broiler\Actions;

use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Services\BatchCalculationService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CloseBatchAction
{
    public function __construct(
        protected BatchCalculationService $calculationService
    ) {
    }

    public function execute(Batch $batch, float $averageWeightKg): Batch
    {
        return DB::transaction(function () use ($batch, $averageWeightKg) {
            // Validate that batch can be closed
            if ($batch->status !== BatchStatus::Harvesting) {
                throw new InvalidArgumentException(
                    'Only batches in Harvesting status can be closed.'
                );
            }

            // Update batch with final data
            $batch->update([
                'status' => BatchStatus::Closed,
                'actual_end_date' => now(),
                'average_weight_kg' => $averageWeightKg,
            ]);

            // Calculate and store final metrics for historical reference
            $statistics = $this->calculationService->getBatchStatistics($batch->fresh());

            // You could optionally store these in a separate batch_statistics table
            // or in a JSON column on the batch for historical tracking

            return $batch->fresh();
        });
    }

    public function transitionToHarvesting(Batch $batch): Batch
    {
        if ($batch->status !== BatchStatus::Active) {
            throw new InvalidArgumentException(
                'Only active batches can transition to Harvesting status.'
            );
        }

        $batch->update(['status' => BatchStatus::Harvesting]);

        return $batch->fresh();
    }

    public function transitionToActive(Batch $batch): Batch
    {
        if ($batch->status !== BatchStatus::Planned) {
            throw new InvalidArgumentException(
                'Only planned batches can transition to Active status.'
            );
        }

        // Validate that required fields are set
        if (! $batch->start_date || ! $batch->initial_quantity) {
            throw new InvalidArgumentException(
                'Batch must have a start date and initial quantity to become active.'
            );
        }

        $batch->update([
            'status' => BatchStatus::Active,
            'current_quantity' => $batch->current_quantity ?? $batch->initial_quantity,
        ]);

        return $batch->fresh();
    }
}
