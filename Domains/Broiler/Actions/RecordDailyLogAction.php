<?php

declare(strict_types=1);

namespace Domains\Broiler\Actions;

use Domains\Broiler\DTOs\DailyLogData;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Models\DailyLog;
use Illuminate\Support\Facades\DB;

class RecordDailyLogAction
{
    public function execute(DailyLogData $data): DailyLog
    {
        return DB::transaction(function () use ($data) {
            $batch = Batch::query()->findOrFail($data->batch_id);

            // Create the daily log
            $dailyLog = DailyLog::query()->create($data->toArray());

            // Update current quantity based on mortality
            $previousQuantity = $batch->current_quantity ?? $batch->initial_quantity;
            $newQuantity = $previousQuantity - $data->mortality_count;
            $batch->update(['current_quantity' => max(0, $newQuantity)]);

            // Create stock movement for feed consumption
            // This will be implemented in the financial integration step
            if ($data->feed_consumed_kg > 0) {
                $this->createFeedStockMovement($batch, $dailyLog, $data->feed_consumed_kg);
            }

            return $dailyLog->fresh();
        });
    }

    protected function createFeedStockMovement(Batch $batch, DailyLog $dailyLog, float $feedConsumedKg): void
    {
        // This will be implemented when integrating with the Inventory domain
        // For now, we'll leave it as a placeholder
        // The StockMovement should:
        // - Type: Out
        // - Reference: "Batch {batch_number} - Daily Log {date}"
        // - Quantity: $feedConsumedKg
        // - Link to feed product
    }
}
