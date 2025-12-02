<?php

namespace Domains\CRM\Models;

use Domains\Shared\Enums\SupplierCategory;
use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'name',
        'email',
        'phone',
        'category',
        'performance_rating',
        'notes',
    ];

    protected $casts = [
        'category' => SupplierCategory::class,
        'performance_rating' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the supplier category label
     *
     * @return string
     */
    public function getCategoryLabelAttribute(): string
    {
        return $this->category?->label() ?? '';
    }

    /**
     * Get the performance rating as stars (1-5)
     *
     * @return string
     */
    public function getRatingStarsAttribute(): string
    {
        if (!$this->performance_rating) {
            return 'Not rated';
        }

        return str_repeat('★', (int) $this->performance_rating) .
               str_repeat('☆', 5 - (int) $this->performance_rating);
    }
}
