<?php

declare(strict_types=1);

namespace Domains\Broiler\Models;

use App\Models\User;
use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortioningRecord extends Model
{
    use BelongsToTeam, HasFactory;

    protected $fillable = [
        'team_id',
        'portioning_date',
        'whole_birds_used',
        'packs_produced',
        'pack_weight_kg',
        'recorded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'portioning_date' => 'date',
            'whole_birds_used' => 'integer',
            'packs_produced' => 'integer',
            'pack_weight_kg' => 'decimal:2',
        ];
    }

    /**
     * Get the user who recorded this portioning.
     *
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Calculate total weight of packs produced.
     */
    public function getTotalWeightKgAttribute(): float
    {
        return $this->packs_produced * $this->pack_weight_kg;
    }

    /**
     * Calculate average yield per bird (packs per bird).
     */
    public function getYieldPerBirdAttribute(): float
    {
        if ($this->whole_birds_used <= 0) {
            return 0;
        }

        return $this->packs_produced / $this->whole_birds_used;
    }

    /**
     * Estimate typical packs from whole birds.
     * Assumes ~2kg bird yields ~3 packs of 0.5kg pieces (after bone/waste).
     */
    public static function estimatePacks(int $wholeBirds, float $packWeightKg = 0.5): int
    {
        // Typical yield: 1 bird (~2kg) → ~1.5kg usable meat → 3 packs of 0.5kg
        $yieldRatio = 1.5 / $packWeightKg; // packs per bird

        return (int) floor($wholeBirds * $yieldRatio);
    }
}
