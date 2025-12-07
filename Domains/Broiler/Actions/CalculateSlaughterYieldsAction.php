<?php

declare(strict_types=1);

namespace Domains\Broiler\Actions;

use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;

class CalculateSlaughterYieldsAction
{
    /**
     * Calculate estimated yields for all poultry products based on bird count.
     *
     * @return array<int, array{product_id: int, product_name: string, estimated_quantity: int}>
     */
    public function execute(int $teamId, int $birdCount): array
    {
        $products = Product::query()
            ->where('team_id', $teamId)
            ->whereIn('type', ProductType::poultryProducts())
            ->where('type', '!=', ProductType::LiveBird) // Exclude live birds from slaughter yields
            ->where('is_active', true)
            ->get();

        $yields = [];

        foreach ($products as $product) {
            $estimatedQuantity = $this->calculateProductYield($product, $birdCount);

            if ($estimatedQuantity > 0) {
                $yields[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_type' => $product->type->value,
                    'units_per_package' => $product->units_per_package,
                    'yield_per_bird' => $product->yield_per_bird,
                    'estimated_quantity' => $estimatedQuantity,
                    'package_unit' => $product->package_unit?->value,
                ];
            }
        }

        return $yields;
    }

    /**
     * Calculate yield for a specific product.
     */
    protected function calculateProductYield(Product $product, int $birdCount): int
    {
        if ($product->units_per_package <= 0) {
            return 0;
        }

        $totalUnits = $birdCount * $product->yield_per_bird;

        return (int) floor($totalUnits / $product->units_per_package);
    }

    /**
     * Get yield breakdown with detailed calculations.
     *
     * @return array<int, array{
     *   product_id: int,
     *   product_name: string,
     *   total_units: int,
     *   packs: int,
     *   remainder: int,
     *   calculation: string
     * }>
     */
    public function executeWithDetails(int $teamId, int $birdCount): array
    {
        $products = Product::query()
            ->where('team_id', $teamId)
            ->whereIn('type', ProductType::poultryProducts())
            ->where('type', '!=', ProductType::LiveBird)
            ->where('is_active', true)
            ->get();

        $yields = [];

        foreach ($products as $product) {
            if ($product->units_per_package <= 0) {
                continue;
            }

            $totalUnits = $birdCount * $product->yield_per_bird;
            $packs = (int) floor($totalUnits / $product->units_per_package);
            $remainder = $totalUnits % $product->units_per_package;

            $yields[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'total_units' => $totalUnits,
                'packs' => $packs,
                'remainder' => $remainder,
                'calculation' => "{$birdCount} birds × {$product->yield_per_bird} / {$product->units_per_package} = {$packs} packs",
            ];
        }

        return $yields;
    }
}
