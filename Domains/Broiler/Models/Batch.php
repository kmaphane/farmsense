<?php

declare(strict_types=1);

namespace Domains\Broiler\Models;

use Domains\Broiler\Enums\BatchStatus;
use Domains\CRM\Models\Supplier;
use Domains\Finance\Models\Expense;
use Domains\Inventory\Models\StockMovement;
use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Batch extends Model
{
    use BelongsToTeam;
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Domains\Broiler\Factories\BatchFactory
    {
        return \Domains\Broiler\Factories\BatchFactory::new();
    }

    protected $fillable = [
        'team_id',
        'name',
        'batch_number',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'status',
        'initial_quantity',
        'current_quantity',
        'supplier_id',
        'target_weight_kg',
        'average_weight_kg',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expected_end_date' => 'date',
            'actual_end_date' => 'date',
            'status' => BatchStatus::class,
            'initial_quantity' => 'integer',
            'current_quantity' => 'integer',
            'target_weight_kg' => 'decimal:2',
            'average_weight_kg' => 'decimal:2',
        ];
    }

    public function dailyLogs(): HasMany
    {
        return $this->hasMany(DailyLog::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function expenses(): MorphMany
    {
        return $this->morphMany(Expense::class, 'allocatable');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'reference', 'batch_number');
    }

    public function getAgeInDaysAttribute(): int
    {
        if (! $this->start_date) {
            return 0;
        }

        $endDate = $this->actual_end_date ?? now();

        return $this->start_date->diffInDays($endDate);
    }

    public function getTotalMortalityAttribute(): int
    {
        return $this->dailyLogs->sum('mortality_count');
    }

    public function getTotalFeedConsumedAttribute(): float
    {
        return (float) $this->dailyLogs->sum('feed_consumed_kg');
    }
}
