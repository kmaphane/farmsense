<?php

namespace Domains\Shared\Traits;

use Illuminate\Database\Eloquent\Builder;

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
     * Get the current team ID from the request context
     *
     * @return int|null
     */
    protected static function getCurrentTeamId(): ?int
    {
        return request()->get('team_id') ?? auth()?->user()?->current_team_id;
    }

    /**
     * Scope to a specific team
     *
     * @param Builder $query
     * @param int|null $teamId
     * @return Builder
     */
    public function scopeBelongsToTeam(Builder $query, ?int $teamId = null): Builder
    {
        return $query->where('team_id', $teamId ?? static::getCurrentTeamId());
    }

    /**
     * Scope to exclude the global team filter (admin use only)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithoutTeamScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('team');
    }
}
