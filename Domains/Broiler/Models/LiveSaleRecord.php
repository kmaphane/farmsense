<?php

declare(strict_types=1);

namespace Domains\Broiler\Models;

use App\Models\User;
use Domains\CRM\Models\Customer;
use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveSaleRecord extends Model
{
    use BelongsToTeam, HasFactory;

    protected $fillable = [
        'team_id',
        'batch_id',
        'sale_date',
        'quantity_sold',
        'unit_price_cents',
        'total_amount_cents',
        'customer_id',
        'recorded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'quantity_sold' => 'integer',
            'unit_price_cents' => 'integer',
            'total_amount_cents' => 'integer',
        ];
    }

    /**
     * Get the batch this sale came from.
     *
     * @return BelongsTo<Batch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the customer for this sale.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who recorded this sale.
     *
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the unit price in BWP (pula).
     */
    public function getUnitPriceAttribute(): float
    {
        return $this->unit_price_cents / 100;
    }

    /**
     * Get the total amount in BWP (pula).
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->total_amount_cents / 100;
    }

    /**
     * Calculate total amount from quantity and unit price.
     */
    public static function calculateTotal(int $quantity, int $unitPriceCents): int
    {
        return $quantity * $unitPriceCents;
    }

    /**
     * Boot method to auto-calculate total.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $record) {
            if ($record->quantity_sold && $record->unit_price_cents) {
                $record->total_amount_cents = self::calculateTotal(
                    $record->quantity_sold,
                    $record->unit_price_cents
                );
            }
        });
    }
}
