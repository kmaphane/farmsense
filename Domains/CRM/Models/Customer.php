<?php

namespace Domains\CRM\Models;

use Domains\Shared\Enums\CustomerType;
use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'name',
        'email',
        'phone',
        'type',
        'credit_limit',
        'payment_terms',
        'notes',
    ];

    protected $casts = [
        'type' => CustomerType::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the customer type label
     *
     * @return string
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type?->label() ?? '';
    }
}
