<?php

namespace Domains\Inventory\Models;

use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, BelongsToTeam;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'type',
        'unit',
        'quantity_on_hand',
        'reorder_level',
        'unit_cost',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'integer',
            'quantity_on_hand' => 'integer',
            'reorder_level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get stock movements for this product
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
