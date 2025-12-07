<?php

declare(strict_types=1);

namespace Domains\Broiler\Actions;

use Domains\Broiler\Models\PortioningRecord;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;
use Domains\Inventory\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecordPortioningAction
{
    /**
     * Record a portioning session (whole birds → chicken pieces).
     *
     * @param  \DateTimeInterface|string  $portioningDate
     */
    public function execute(
        int $teamId,
        $portioningDate,
        int $wholeBirdsUsed,
        int $packsProduced,
        float $packWeightKg = 0.5,
        ?int $recordedBy = null,
        ?string $notes = null
    ): PortioningRecord {
        return DB::transaction(function () use ($teamId, $portioningDate, $wholeBirdsUsed, $packsProduced, $packWeightKg, $recordedBy, $notes) {
            // Find whole chicken and chicken pieces products
            $wholeChickenProduct = $this->findProduct($teamId, ProductType::WholeChicken);
            $chickenPiecesProduct = $this->findProduct($teamId, ProductType::ChickenPieces);

            // Validate stock availability
            if ($wholeChickenProduct->quantity_on_hand < $wholeBirdsUsed) {
                throw new InvalidArgumentException(
                    "Insufficient whole chicken stock. Available: {$wholeChickenProduct->quantity_on_hand}, Required: {$wholeBirdsUsed}"
                );
            }

            // Create portioning record
            $portioningRecord = PortioningRecord::query()->create([
                'team_id' => $teamId,
                'portioning_date' => $portioningDate,
                'whole_birds_used' => $wholeBirdsUsed,
                'packs_produced' => $packsProduced,
                'pack_weight_kg' => $packWeightKg,
                'recorded_by' => $recordedBy ?? auth()->id(),
                'notes' => $notes,
            ]);

            // Create stock movement OUT for whole birds
            StockMovement::query()->create([
                'team_id' => $teamId,
                'product_id' => $wholeChickenProduct->id,
                'type' => 'out',
                'quantity' => $wholeBirdsUsed,
                'reason' => "Portioning Record #{$portioningRecord->id} - {$portioningRecord->portioning_date->format('Y-m-d')}",
                'recorded_by' => $recordedBy ?? auth()->id(),
            ]);
            $wholeChickenProduct->decrement('quantity_on_hand', $wholeBirdsUsed);

            // Create stock movement IN for chicken pieces
            StockMovement::query()->create([
                'team_id' => $teamId,
                'product_id' => $chickenPiecesProduct->id,
                'type' => 'in',
                'quantity' => $packsProduced,
                'reason' => "Portioning Record #{$portioningRecord->id} - {$portioningRecord->portioning_date->format('Y-m-d')}",
                'recorded_by' => $recordedBy ?? auth()->id(),
            ]);
            $chickenPiecesProduct->increment('quantity_on_hand', $packsProduced);

            return $portioningRecord->fresh();
        });
    }

    protected function findProduct(int $teamId, ProductType $type): Product
    {
        $product = Product::query()
            ->where('team_id', $teamId)
            ->where('type', $type)
            ->where('is_active', true)
            ->first();

        if (! $product) {
            throw new InvalidArgumentException(
                "No active {$type->label()} product found for this team."
            );
        }

        return $product;
    }

    /**
     * Estimate packs from whole birds.
     * Typical yield: 1 bird (~2kg) → ~1.5kg usable meat → 3 packs of 0.5kg.
     */
    public static function estimatePacks(int $wholeBirds, float $packWeightKg = 0.5): int
    {
        $yieldRatio = 1.5 / $packWeightKg;

        return (int) floor($wholeBirds * $yieldRatio);
    }
}
