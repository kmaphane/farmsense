<?php

namespace Domains\CRM\Models;

use Domains\CRM\Factories\CustomerFactory;
use Domains\Shared\Enums\CustomerType;
use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;
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

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }

    /**
     * Get the customer type label
     *
     * @return string
     */
    protected function typeLabel(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->type?->label() ?? '';
        });
    }

    protected function casts(): array
    {
        return [
            'type' => CustomerType::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
