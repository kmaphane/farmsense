<?php

namespace Domains\Shared\DTOs;

use Spatie\LaravelData\Data;

abstract class BaseData extends Data
{
    /**
     * Get the team_id from the current authenticated user
     */
    protected static function getCurrentTeamId(): ?int
    {
        return auth()->user()?->current_team_id;
    }

    /**
     * Merge team_id into the payload if not already present
     */
    public function withTeamId(): static
    {
        if (property_exists($this, 'team_id') && ! $this->team_id) {
            $this->team_id = static::getCurrentTeamId();
        }

        return $this;
    }
}
