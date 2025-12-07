<?php

namespace Domains\Shared\Traits;

use Domains\Auth\Models\Team;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BelongsToTeam Trait
 *
 * Automatically scopes eloquent queries to the current team context.
 * This ensures data isolation in multi-tenant applications.
 *
 * Usage:
 *   class Batch extends Model
 *   {
 *       use BelongsToTeam;
 *   }
 */
trait BelongsToTeam
{
    /**
     * Boot the trait - add global scope
     */
    protected static function bootBelongsToTeam(): void
    {
        static::addGlobalScope('team', function (Builder $builder): void {
            $teamId = static::getCurrentTeamId();

            if ($teamId !== null) {
                $builder->where('team_id', $teamId);
            }
        });
    }

    /**
     * Get the team that owns this model (required by Filament tenancy).
     *
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the current team ID from Filament tenant or fallback to user's current team.
     */
    protected static function getCurrentTeamId(): ?int
    {
        // First, try to get the tenant from Filament (if we're in a Filament panel context)
        if (class_exists(Filament::class)) {
            $tenant = Filament::getTenant();
            if ($tenant !== null) {
                return $tenant->getKey();
            }
        }

        // Fallback to request parameter or user's current team
        return request()->get('team_id') ?? auth()?->user()?->current_team_id;
    }

    /**
     * Scope to a specific team
     */
    protected function scopeBelongsToTeam(Builder $query, ?int $teamId = null): Builder
    {
        return $query->where('team_id', $teamId ?? static::getCurrentTeamId());
    }

    /**
     * Scope to exclude the global team filter (admin use only)
     */
    protected function scopeWithoutTeamScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('team');
    }
}
