<?php

namespace Domains\Finance\Models;

use Domains\Finance\Factories\ExpenseFactory;
use Domains\Shared\Enums\ExpenseCategory;
use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Expense extends Model
{
    use BelongsToTeam;

    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'amount',
        'currency',
        'category',
        'description',
        'allocatable_type',
        'allocatable_id',
        'ocr_data',
        'receipt_path',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ExpenseFactory
    {
        return ExpenseFactory::new();
    }

    /**
     * Get the allocatable resource (Batch or General Farm)
     */
    public function allocatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the amount formatted as currency string
     *
     * @return string
     */
    protected function formattedAmount(): Attribute
    {
        return Attribute::make(get: function () {
            return number_format($this->amount / 100, 2).' '.$this->currency;
        });
    }

    /**
     * Get category label
     *
     * @return string
     */
    protected function categoryLabel(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->category?->label() ?? '';
        });
    }

    /**
     * Scope: Get expenses for a specific batch
     *
     * @param  mixed  $query
     * @return mixed
     */
    protected function scopeForBatch($query, int $batchId)
    {
        return $query->where('allocatable_type', 'Domains\\Broiler\\Models\\Batch')
            ->where('allocatable_id', $batchId);
    }

    /**
     * Scope: Get expenses for general farm (not allocated to specific batch)
     *
     * @param  mixed  $query
     * @return mixed
     */
    protected function scopeForGeneral($query)
    {
        return $query->whereNull('allocatable_type')
            ->whereNull('allocatable_id');
    }

    protected function casts(): array
    {
        return [
            'category' => ExpenseCategory::class,
            'ocr_data' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
