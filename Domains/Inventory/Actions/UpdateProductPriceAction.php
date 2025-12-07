<?php

declare(strict_types=1);

namespace Domains\Inventory\Actions;

use Domains\Inventory\Models\Product;
use Domains\Inventory\Models\ProductPriceHistory;
use Illuminate\Support\Facades\DB;

class UpdateProductPriceAction
{
    /**
     * Update a product's price and create history record.
     *
     * @param  int  $newPriceCents  New price in cents/thebe
     * @param  \DateTimeInterface|string|null  $effectiveFrom  When price takes effect (default: now)
     * @param  int|null  $changedBy  User ID who changed the price
     * @param  string|null  $reason  Reason for price change
     */
    public function execute(
        Product $product,
        int $newPriceCents,
        $effectiveFrom = null,
        ?int $changedBy = null,
        ?string $reason = null
    ): ProductPriceHistory {
        return DB::transaction(function () use ($product, $newPriceCents, $effectiveFrom, $changedBy, $reason) {
            $effectiveFrom = $effectiveFrom ?? now();

            // Close the current price history record (if exists)
            ProductPriceHistory::query()
                ->where('product_id', $product->id)
                ->whereNull('effective_until')
                ->update([
                    'effective_until' => $effectiveFrom,
                ]);

            // Create new price history record
            $priceHistory = ProductPriceHistory::query()->create([
                'product_id' => $product->id,
                'price_cents' => $newPriceCents,
                'effective_from' => $effectiveFrom,
                'effective_until' => null,
                'changed_by' => $changedBy ?? auth()->id(),
                'reason' => $reason,
            ]);

            // Update product's current selling price
            $product->update([
                'selling_price_cents' => $newPriceCents,
            ]);

            return $priceHistory;
        });
    }

    /**
     * Get price history for a product.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, ProductPriceHistory>
     */
    public function getHistory(Product $product, int $limit = 10)
    {
        return $product->priceHistory()
            ->orderByDesc('effective_from')
            ->limit($limit)
            ->get();
    }

    /**
     * Get price effective on a specific date.
     */
    public function getPriceOnDate(Product $product, $date): ?int
    {
        $history = ProductPriceHistory::query()
            ->where('product_id', $product->id)
            ->where('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $date);
            })
            ->first();

        return $history?->price_cents;
    }
}
