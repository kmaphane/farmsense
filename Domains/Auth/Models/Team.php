<?php

namespace Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'subscription_plan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the owner of the team
     *
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the users in this team
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    /**
     * Add a user to the team with a specific role
     *
     * @param User $user
     * @param int $roleId
     * @return void
     */
    public function addUser(User $user, int $roleId): void
    {
        // Only attach if not already a member
        if (!$this->users()->where('user_id', $user->id)->exists()) {
            $this->users()->attach($user->id, ['role_id' => $roleId]);
        }
    }

    /**
     * Remove a user from the team
     *
     * @param User $user
     * @return void
     */
    public function removeUser(User $user): void
    {
        $this->users()->detach($user->id);

        // Clear current team if it was the user's current team
        if ($user->current_team_id === $this->id) {
            $user->update(['current_team_id' => null]);
        }
    }

    /**
     * Change a user's role in the team
     *
     * @param User $user
     * @param int $roleId
     * @return void
     */
    public function changeUserRole(User $user, int $roleId): void
    {
        if ($this->users()->where('user_id', $user->id)->exists()) {
            $this->users()->updateExistingPivot($user->id, ['role_id' => $roleId]);
        }
    }
}
