<?php

namespace Domains\Finance\Models;

use Domains\CRM\Models\Customer;
use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, BelongsToTeam;

    protected $fillable = [
        'team_id',
        'customer_id',
        'invoice_number',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'description',
        'due_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'tax_amount' => 'integer',
            'total_amount' => 'integer',
            'due_date' => 'date',
        ];
    }

    /**
     * Get the customer for this invoice
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get line items for this invoice
     */
    public function lineItems(): HasMany
    {
        return $this->hasMany(InvoiceLineItem::class);
    }

    /**
     * Get payments for this invoice
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
