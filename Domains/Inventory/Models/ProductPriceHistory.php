<?php

declare(strict_types=1);

namespace Domains\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPriceHistory extends Model
{
    protected $fillable = [
        'product_id',
        'price_cents',
        'effective_from',
        'effective_until',
        'changed_by',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }

    /**
     * Get the product this price history belongs to.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who changed the price.
     *
     * @return BelongsTo<User, $this>
     */
    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get the price in BWP (pula).
     */
    public function getPriceAttribute(): float
    {
        return $this->price_cents / 100;
    }

    /**
     * Check if this is the current active price.
     */
    public function isCurrent(): bool
    {
        return $this->effective_until === null;
    }

    /**
     * Scope to get only current prices.
     */
    public function scopeCurrent($query)
    {
        return $query->whereNull('effective_until');
    }

    /**
     * Scope to get prices effective on a given date.
     */
    public function scopeEffectiveOn($query, $date)
    {
        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $date);
            });
    }
}
