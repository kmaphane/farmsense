<?php

declare(strict_types=1);

namespace Domains\Broiler\Models;

use App\Models\User;
use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlaughterRecord extends Model
{
    use BelongsToTeam, HasFactory;

    protected $fillable = [
        'team_id',
        'slaughter_date',
        'total_birds_processed',
        'recorded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'slaughter_date' => 'date',
            'total_birds_processed' => 'integer',
        ];
    }

    /**
     * Get the user who recorded this slaughter.
     *
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the batch sources for this slaughter.
     *
     * @return HasMany<SlaughterBatchSource, $this>
     */
    public function batchSources(): HasMany
    {
        return $this->hasMany(SlaughterBatchSource::class);
    }

    /**
     * Get the yields produced from this slaughter.
     *
     * @return HasMany<SlaughterYield, $this>
     */
    public function yields(): HasMany
    {
        return $this->hasMany(SlaughterYield::class);
    }

    /**
     * Calculate total birds from all sources.
     */
    public function calculateTotalBirds(): int
    {
        return $this->batchSources()->sum('actual_quantity');
    }

    /**
     * Update the total birds processed from batch sources.
     */
    public function updateTotalBirds(): void
    {
        $this->update([
            'total_birds_processed' => $this->calculateTotalBirds(),
        ]);
    }

    /**
     * Check if any batch source has a discrepancy.
     */
    public function hasDiscrepancies(): bool
    {
        return $this->batchSources()
            ->whereColumn('actual_quantity', '<', 'expected_quantity')
            ->exists();
    }
}
