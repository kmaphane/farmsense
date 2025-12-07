<?php

declare(strict_types=1);

namespace Domains\Broiler\Models;

use Domains\Auth\Models\User;
use Domains\Broiler\Factories\DailyLogFactory;
use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyLog extends Model
{
    use BelongsToTeam;
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): DailyLogFactory
    {
        return DailyLogFactory::new();
    }

    protected $fillable = [
        'team_id',
        'batch_id',
        'log_date',
        'mortality_count',
        'feed_consumed_kg',
        'water_consumed_liters',
        'temperature_celsius',
        'humidity_percent',
        'ammonia_ppm',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'mortality_count' => 'integer',
            'feed_consumed_kg' => 'decimal:2',
            'water_consumed_liters' => 'decimal:2',
            'temperature_celsius' => 'decimal:1',
            'humidity_percent' => 'decimal:1',
            'ammonia_ppm' => 'decimal:1',
        ];
    }

    /**
     * @return BelongsTo<Batch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function isEditable(): bool
    {
        // Can only edit today's log (immutable after 24 hours)
        return $this->log_date->isToday();
    }
}
