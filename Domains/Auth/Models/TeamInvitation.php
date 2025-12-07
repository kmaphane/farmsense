<?php

namespace Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TeamInvitation Model
 *
 * Used in Phase 2 for team member invitations.
 * Currently a structure stub - full implementation in Phase 2.
 */
class TeamInvitation extends Model
{
    protected $fillable = [
        'team_id',
        'email',
        'role_id',
        'expires_at',
        'accepted_at',
    ];

    /**
     * Get the team this invitation is for
     *
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Check if the invitation has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the invitation has been accepted
     */
    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
