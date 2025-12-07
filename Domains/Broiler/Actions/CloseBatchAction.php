<?php

declare(strict_types=1);

namespace Domains\Broiler\Actions;

use App\Notifications\DiscrepancyNotification;
use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Enums\DiscrepancyReason;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Services\BatchCalculationService;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;
use Domains\Inventory\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

class CloseBatchAction
{
    public function __construct(
        protected BatchCalculationService $calculationService
    ) {}

    public function execute(
        Batch $batch,
        float $averageWeightKg,
        ?int $manureBagsCollected = null,
        ?DiscrepancyReason $closureReason = null,
        ?string $closureNotes = null
    ): Batch {
        return DB::transaction(function () use ($batch, $averageWeightKg, $manureBagsCollected, $closureReason, $closureNotes) {
            // Validate that batch can be closed
            throw_if(
                $batch->status !== BatchStatus::Harvesting,
                InvalidArgumentException::class,
                'Only batches in Harvesting status can be closed.'
            );

            // If birds remain, require closure reason
            if ($batch->current_quantity > 0 && ! $closureReason) {
                throw new InvalidArgumentException(
                    'Closure reason is required when birds remain in the batch.'
                );
            }

            // Update batch with final data
            $batch->update([
                'status' => BatchStatus::Closed,
                'actual_end_date' => now(),
                'average_weight_kg' => $averageWeightKg,
                'manure_bags_collected' => $manureBagsCollected,
                'closure_reason' => $closureReason?->value,
                'closure_notes' => $closureNotes,
            ]);

            // Create stock movement for manure if collected
            if ($manureBagsCollected && $manureBagsCollected > 0) {
                $this->createManureStockMovement($batch, $manureBagsCollected);
            }

            // Notify managers if discrepancy
            if ($batch->current_quantity > 0 && $closureReason?->requiresNotification()) {
                $this->notifyManagers($batch);
            }

            // Calculate and store final metrics for historical reference
            $statistics = $this->calculationService->getBatchStatistics($batch->fresh());

            return $batch->fresh();
        });
    }

    protected function createManureStockMovement(Batch $batch, int $bags): void
    {
        // Find manure product for this team
        $manureProduct = Product::query()
            ->where('team_id', $batch->team_id)
            ->where('type', ProductType::ByProduct)
            ->where('name', 'like', '%Manure%')
            ->first();

        if (! $manureProduct) {
            return;
        }

        StockMovement::query()->create([
            'team_id' => $batch->team_id,
            'product_id' => $manureProduct->id,
            'type' => 'in',
            'quantity' => $bags,
            'reason' => "Batch {$batch->batch_number} - Closure Manure Collection",
            'recorded_by' => auth()->id(),
        ]);

        // Update product quantity
        $manureProduct->increment('quantity_on_hand', $bags);
    }

    protected function notifyManagers(Batch $batch): void
    {
        $managers = $batch->team->users()
            ->whereHas('roles', fn ($q) => $q->where('name', 'farm_manager'))
            ->get();

        if ($managers->isNotEmpty()) {
            Notification::send(
                $managers,
                DiscrepancyNotification::fromBatchClosure($batch)
            );
        }
    }

    public function transitionToHarvesting(Batch $batch): Batch
    {
        throw_if(
            $batch->status !== BatchStatus::Active,
            InvalidArgumentException::class,
            'Only active batches can transition to Harvesting status.'
        );

        $batch->update(['status' => BatchStatus::Harvesting]);

        return $batch->fresh();
    }

    public function transitionToActive(Batch $batch): Batch
    {
        throw_if(
            $batch->status !== BatchStatus::Planned,
            InvalidArgumentException::class,
            'Only planned batches can transition to Active status.'
        );

        // Validate that required fields are set
        throw_if(
            ! $batch->start_date || ! $batch->initial_quantity,
            InvalidArgumentException::class,
            'Batch must have a start date and initial quantity to become active.'
        );

        $batch->update([
            'status' => BatchStatus::Active,
            'current_quantity' => $batch->current_quantity ?? $batch->initial_quantity,
        ]);

        return $batch->fresh();
    }
}
