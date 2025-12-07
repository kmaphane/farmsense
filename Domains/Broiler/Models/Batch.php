<?php

declare(strict_types=1);

namespace Domains\Broiler\Models;

use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Enums\DiscrepancyReason;
use Domains\Broiler\Factories\BatchFactory;
use Domains\CRM\Models\Supplier;
use Domains\Finance\Models\Expense;
use Domains\Inventory\Models\StockMovement;
use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
    protected static function newFactory(): BatchFactory
    {
        return BatchFactory::new();
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
        'manure_bags_collected',
        'closure_reason',
        'closure_notes',
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
            'manure_bags_collected' => 'integer',
            'closure_reason' => DiscrepancyReason::class,
        ];
    }

    /**
     * @return HasMany<DailyLog, $this>
     */
    public function dailyLogs(): HasMany
    {
        return $this->hasMany(DailyLog::class);
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return MorphMany<Expense, $this>
     */
    public function expenses(): MorphMany
    {
        return $this->morphMany(Expense::class, 'allocatable');
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'reference', 'batch_number');
    }

    /**
     * Get slaughter batch sources for this batch.
     *
     * @return HasMany<SlaughterBatchSource, $this>
     */
    public function slaughterSources(): HasMany
    {
        return $this->hasMany(SlaughterBatchSource::class);
    }

    /**
     * Get live sale records for this batch.
     *
     * @return HasMany<LiveSaleRecord, $this>
     */
    public function liveSales(): HasMany
    {
        return $this->hasMany(LiveSaleRecord::class);
    }

    protected function ageInDays(): Attribute
    {
        return Attribute::make(get: function () {
            if (! $this->start_date) {
                return 0;
            }
            $endDate = $this->actual_end_date ?? now();

            return (int) $this->start_date->diffInDays($endDate);
        });
    }

    protected function totalMortality(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->dailyLogs->sum('mortality_count');
        });
    }

    protected function totalFeedConsumed(): Attribute
    {
        return Attribute::make(get: function () {
            return (float) $this->dailyLogs->sum('feed_consumed_kg');
        });
    }

    /**
     * Get total birds sold live from this batch.
     */
    protected function totalLiveSold(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->liveSales->sum('quantity_sold');
        });
    }

    /**
     * Get total birds slaughtered from this batch.
     */
    protected function totalSlaughtered(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->slaughterSources->sum('actual_quantity');
        });
    }
}
