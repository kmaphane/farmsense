<?php

declare(strict_types=1);

namespace Domains\Broiler\Actions;

use Domains\Broiler\DTOs\BatchData;
use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Models\Batch;
use Illuminate\Support\Facades\DB;

class CreateBatchAction
{
    public function execute(BatchData $data): Batch
    {
        return DB::transaction(function () use ($data) {
            // Ensure status is set to Planned for new batches
            $batchData = $data->toArray();
            $batchData['status'] = BatchStatus::Planned;
            $batchData['current_quantity'] = $data->initial_quantity;

            $batch = Batch::create($batchData);

            // Optionally create initial expense for chick purchase
            // This can be implemented when Finance integration is complete

            return $batch->fresh();
        });
    }

    public function generateBatchNumber(int $teamId): string
    {
        $year = now()->year;
        $prefix = 'BRO';

        // Get the count of batches for this team this year
        $count = Batch::where('team_id', $teamId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('%s-%d-%03d', $prefix, $year, $count);
    }
}
