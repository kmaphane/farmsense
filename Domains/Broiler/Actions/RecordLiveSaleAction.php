<?php

declare(strict_types=1);

namespace Domains\Broiler\Actions;

use Domains\Broiler\Models\Batch;
use Domains\Broiler\Models\LiveSaleRecord;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecordLiveSaleAction
{
    /**
     * Record a live bird sale from a batch.
     *
     * @param  \DateTimeInterface|string  $saleDate
     * @param  int|null  $unitPriceCents  If null, uses default live bird product price
     */
    public function execute(
        int $teamId,
        int $batchId,
        $saleDate,
        int $quantitySold,
        ?int $unitPriceCents = null,
        ?int $customerId = null,
        ?int $recordedBy = null,
        ?string $notes = null
    ): LiveSaleRecord {
        return DB::transaction(function () use ($teamId, $batchId, $saleDate, $quantitySold, $unitPriceCents, $customerId, $recordedBy, $notes) {
            $batch = Batch::query()->findOrFail($batchId);

            // Validate batch has enough birds
            if ($quantitySold > $batch->current_quantity) {
                throw new InvalidArgumentException(
                    "Insufficient birds in batch. Available: {$batch->current_quantity}, Requested: {$quantitySold}"
                );
            }

            // Get price from live bird product if not specified
            if ($unitPriceCents === null) {
                $unitPriceCents = $this->getLiveBirdPrice($teamId);
            }

            // Create live sale record
            $saleRecord = LiveSaleRecord::query()->create([
                'team_id' => $teamId,
                'batch_id' => $batchId,
                'sale_date' => $saleDate,
                'quantity_sold' => $quantitySold,
                'unit_price_cents' => $unitPriceCents,
                'total_amount_cents' => $quantitySold * $unitPriceCents,
                'customer_id' => $customerId,
                'recorded_by' => $recordedBy ?? auth()->id(),
                'notes' => $notes,
            ]);

            // Deduct from batch immediately
            $batch->decrement('current_quantity', $quantitySold);

            return $saleRecord->fresh(['batch', 'customer']);
        });
    }

    protected function getLiveBirdPrice(int $teamId): int
    {
        $liveBirdProduct = Product::query()
            ->where('team_id', $teamId)
            ->where('type', ProductType::LiveBird)
            ->where('is_active', true)
            ->first();

        if (! $liveBirdProduct || ! $liveBirdProduct->selling_price_cents) {
            throw new InvalidArgumentException(
                'No live bird product with price found. Please specify unit price.'
            );
        }

        return $liveBirdProduct->selling_price_cents;
    }
}
