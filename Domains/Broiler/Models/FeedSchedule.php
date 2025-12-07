<?php

declare(strict_types=1);

namespace Domains\Broiler\Models;

use Domains\Broiler\Enums\FeedType;
use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedSchedule extends Model
{
    use BelongsToTeam, HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'feed_type',
        'age_from_days',
        'age_to_days',
        'grams_per_bird_per_day',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'feed_type' => FeedType::class,
            'age_from_days' => 'integer',
            'age_to_days' => 'integer',
            'grams_per_bird_per_day' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope to get schedules for a specific age.
     */
    public function scopeForAge($query, int $ageDays)
    {
        return $query->where('age_from_days', '<=', $ageDays)
            ->where('age_to_days', '>=', $ageDays);
    }

    /**
     * Scope to get only active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate total feed needed for a flock at this age.
     */
    public function calculateDailyFeed(int $birdCount): float
    {
        return ($this->grams_per_bird_per_day * $birdCount) / 1000; // Convert to kg
    }

    /**
     * Get the recommended feed schedule for a given age.
     */
    public static function getForAge(int $teamId, int $ageDays): ?self
    {
        return static::where('team_id', $teamId)
            ->active()
            ->forAge($ageDays)
            ->first();
    }
}
