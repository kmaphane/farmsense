<?php

declare(strict_types=1);

namespace Domains\Broiler\Models;

use Domains\Inventory\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaughterYield extends Model
{
    use HasFactory;

    protected $fillable = [
        'slaughter_record_id',
        'product_id',
        'estimated_quantity',
        'actual_quantity',
        'household_consumed',
    ];

    protected function casts(): array
    {
        return [
            'estimated_quantity' => 'integer',
            'actual_quantity' => 'integer',
            'household_consumed' => 'integer',
        ];
    }

    /**
     * Get the slaughter record this yield belongs to.
     *
     * @return BelongsTo<SlaughterRecord, $this>
     */
    public function slaughterRecord(): BelongsTo
    {
        return $this->belongsTo(SlaughterRecord::class);
    }

    /**
     * Get the product this yield produces.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate household consumed (estimated - actual).
     */
    public function calculateHouseholdConsumed(): int
    {
        return max(0, $this->estimated_quantity - $this->actual_quantity);
    }

    /**
     * Update household consumed based on estimated vs actual.
     */
    public function updateHouseholdConsumed(): void
    {
        $this->update([
            'household_consumed' => $this->calculateHouseholdConsumed(),
        ]);
    }

    /**
     * Calculate estimated yield from bird count.
     */
    public static function calculateEstimated(Product $product, int $birdCount): int
    {
        return $product->calculateEstimatedYield($birdCount);
    }
}
