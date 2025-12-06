<?php

namespace Domains\Finance\Models;

use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, BelongsToTeam;

    protected $fillable = [
        'team_id',
        'invoice_id',
        'amount',
        'payment_method',
        'reference',
        'notes',
        'recorded_by',
        'payment_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'payment_date' => 'date',
        ];
    }

    /**
     * Get the invoice for this payment
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the user who recorded this payment
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(\Domains\Auth\Models\User::class, 'recorded_by');
    }
}
