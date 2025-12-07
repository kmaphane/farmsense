<?php

declare(strict_types=1);

namespace Domains\Broiler\Models;

use Domains\Broiler\Enums\DiscrepancyReason;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaughterBatchSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'slaughter_record_id',
        'batch_id',
        'expected_quantity',
        'actual_quantity',
        'discrepancy_reason',
        'discrepancy_notes',
    ];

    protected function casts(): array
    {
        return [
            'expected_quantity' => 'integer',
            'actual_quantity' => 'integer',
            'discrepancy_reason' => DiscrepancyReason::class,
        ];
    }

    /**
     * Get the slaughter record this source belongs to.
     *
     * @return BelongsTo<SlaughterRecord, $this>
     */
    public function slaughterRecord(): BelongsTo
    {
        return $this->belongsTo(SlaughterRecord::class);
    }

    /**
     * Get the batch this source came from.
     *
     * @return BelongsTo<Batch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the discrepancy amount (expected - actual).
     */
    public function getDiscrepancyAttribute(): int
    {
        return $this->expected_quantity - $this->actual_quantity;
    }

    /**
     * Check if there's a discrepancy.
     */
    public function hasDiscrepancy(): bool
    {
        return $this->actual_quantity < $this->expected_quantity;
    }

    /**
     * Check if this discrepancy requires manager notification.
     */
    public function requiresNotification(): bool
    {
        return $this->hasDiscrepancy()
            && $this->discrepancy_reason?->requiresNotification();
    }
}
