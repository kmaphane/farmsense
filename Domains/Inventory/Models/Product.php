<?php

declare(strict_types=1);

namespace Domains\Inventory\Models;

use Domains\Inventory\Enums\PackageUnit;
use Domains\Inventory\Enums\ProductType;
use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToTeam, HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'type',
        'unit',
        'quantity_on_hand',
        'reorder_level',
        'unit_cost',
        'selling_price_cents',
        'units_per_package',
        'package_unit',
        'yield_per_bird',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'package_unit' => PackageUnit::class,
            'unit_cost' => 'integer',
            'selling_price_cents' => 'integer',
            'units_per_package' => 'integer',
            'yield_per_bird' => 'integer',
            'quantity_on_hand' => 'integer',
            'reorder_level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get stock movements for this product.
     *
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get price history for this product.
     *
     * @return HasMany<ProductPriceHistory, $this>
     */
    public function priceHistory(): HasMany
    {
        return $this->hasMany(ProductPriceHistory::class);
    }

    /**
     * Get the selling price in BWP (pula).
     */
    public function getSellingPriceAttribute(): ?float
    {
        return $this->selling_price_cents !== null
            ? $this->selling_price_cents / 100
            : null;
    }

    /**
     * Check if this is a poultry product.
     */
    public function isPoultryProduct(): bool
    {
        return $this->type?->isPoultryProduct() ?? false;
    }

    /**
     * Calculate estimated yield for a given number of birds.
     */
    public function calculateEstimatedYield(int $birdCount): int
    {
        if ($this->units_per_package <= 0) {
            return 0;
        }

        $totalUnits = $birdCount * $this->yield_per_bird;

        return (int) floor($totalUnits / $this->units_per_package);
    }
}
