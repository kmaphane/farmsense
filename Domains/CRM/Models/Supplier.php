<?php

namespace Domains\CRM\Models;

use Domains\Shared\Enums\SupplierCategory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Supplier Model
 *
 * NOTE: Suppliers are GLOBAL/SHARED across all teams in the system.
 * This enables:
 * - Cross-team supplier performance insights
 * - Shared pricing reference data
 * - Future API integration for live pricing and orders
 *
 * Teams reference suppliers but do not own them.
 */
class Supplier extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'category',
        'performance_rating',
        'current_price_per_unit',
        'notes',
        'is_active',
    ];

    /**
     * Get the supplier category label
     *
     * @return string
     */
    protected function categoryLabel(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->category?->label() ?? '';
        });
    }

    /**
     * Get the performance rating as stars (1-5)
     *
     * @return string
     */
    protected function ratingStars(): Attribute
    {
        return Attribute::make(get: function () {
            if (! $this->performance_rating) {
                return 'Not rated';
            }

            return str_repeat('★', (int) $this->performance_rating).
                   str_repeat('☆', 5 - (int) $this->performance_rating);
        });
    }

    protected function casts(): array
    {
        return [
            'category' => SupplierCategory::class,
            'performance_rating' => 'float',
            'current_price_per_unit' => 'float',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
