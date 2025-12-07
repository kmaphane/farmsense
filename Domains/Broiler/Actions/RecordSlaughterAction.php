<?php

declare(strict_types=1);

namespace Domains\Broiler\Actions;

use App\Notifications\DiscrepancyNotification;
use Domains\Broiler\Enums\DiscrepancyReason;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Models\SlaughterBatchSource;
use Domains\Broiler\Models\SlaughterRecord;
use Domains\Broiler\Models\SlaughterYield;
use Domains\Inventory\Models\Product;
use Domains\Inventory\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

class RecordSlaughterAction
{
    public function __construct(
        protected CalculateSlaughterYieldsAction $yieldCalculator
    ) {}

    /**
     * Record a slaughter session.
     *
     * @param  \DateTimeInterface|string  $slaughterDate
     * @param  array<int, array{expected: int, actual: int, reason?: string, notes?: string}>  $batchSources  Keyed by batch_id
     * @param  array<int, array{estimated: int, actual: int}>  $yields  Keyed by product_id
     */
    public function execute(
        int $teamId,
        $slaughterDate,
        array $batchSources,
        array $yields,
        int $recordedBy,
        ?string $notes = null
    ): SlaughterRecord {
        return DB::transaction(function () use ($teamId, $slaughterDate, $batchSources, $yields, $recordedBy, $notes) {
            // Validate batch sources
            $this->validateBatchSources($batchSources);

            // Calculate total birds
            $totalBirds = array_sum(array_column($batchSources, 'actual'));

            // Create slaughter record
            $slaughterRecord = SlaughterRecord::query()->create([
                'team_id' => $teamId,
                'slaughter_date' => $slaughterDate,
                'total_birds_processed' => $totalBirds,
                'recorded_by' => $recordedBy,
                'notes' => $notes,
            ]);

            // Create batch sources and deduct from batches
            $this->createBatchSources($slaughterRecord, $batchSources);

            // Create yields and stock movements
            $this->createYields($slaughterRecord, $teamId, $yields);

            return $slaughterRecord->fresh(['batchSources', 'yields']);
        });
    }

    protected function validateBatchSources(array $batchSources): void
    {
        foreach ($batchSources as $batchId => $source) {
            $batch = Batch::query()->find($batchId);

            if (! $batch) {
                throw new InvalidArgumentException("Batch {$batchId} not found.");
            }

            if ($source['expected'] > $batch->current_quantity) {
                throw new InvalidArgumentException(
                    "Expected quantity ({$source['expected']}) exceeds batch {$batch->batch_number} current quantity ({$batch->current_quantity})."
                );
            }
        }
    }

    protected function createBatchSources(SlaughterRecord $record, array $batchSources): void
    {
        foreach ($batchSources as $batchId => $source) {
            $batch = Batch::query()->findOrFail($batchId);
            $discrepancyReason = isset($source['reason'])
                ? DiscrepancyReason::tryFrom($source['reason'])
                : null;

            $batchSource = SlaughterBatchSource::query()->create([
                'slaughter_record_id' => $record->id,
                'batch_id' => $batchId,
                'expected_quantity' => $source['expected'],
                'actual_quantity' => $source['actual'],
                'discrepancy_reason' => $discrepancyReason?->value,
                'discrepancy_notes' => $source['notes'] ?? null,
            ]);

            // Deduct from batch immediately
            $batch->decrement('current_quantity', $source['actual']);

            // Notify managers if suspicious discrepancy
            if ($batchSource->requiresNotification()) {
                $this->notifyManagers($batch, $batchSource);
            }
        }
    }

    protected function createYields(SlaughterRecord $record, int $teamId, array $yields): void
    {
        foreach ($yields as $productId => $yieldData) {
            $product = Product::query()->findOrFail($productId);

            $yield = SlaughterYield::query()->create([
                'slaughter_record_id' => $record->id,
                'product_id' => $productId,
                'estimated_quantity' => $yieldData['estimated'],
                'actual_quantity' => $yieldData['actual'],
                'household_consumed' => max(0, $yieldData['estimated'] - $yieldData['actual']),
            ]);

            // Create stock movement for actual yield
            if ($yieldData['actual'] > 0) {
                StockMovement::query()->create([
                    'team_id' => $teamId,
                    'product_id' => $productId,
                    'type' => 'in',
                    'quantity' => $yieldData['actual'],
                    'reason' => "Slaughter Record #{$record->id} - {$record->slaughter_date->format('Y-m-d')}",
                    'recorded_by' => $record->recorded_by,
                ]);

                // Update product quantity
                $product->increment('quantity_on_hand', $yieldData['actual']);
            }
        }
    }

    protected function notifyManagers(Batch $batch, SlaughterBatchSource $source): void
    {
        $managers = $batch->team->users()
            ->whereHas('roles', fn ($q) => $q->where('name', 'farm_manager'))
            ->get();

        if ($managers->isNotEmpty()) {
            Notification::send(
                $managers,
                DiscrepancyNotification::fromSlaughterSource($source)
            );
        }
    }
}
