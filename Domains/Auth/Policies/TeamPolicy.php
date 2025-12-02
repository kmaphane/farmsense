<?php

namespace Domains\Auth\Policies;

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;

class TeamPolicy
{
    /**
     * Determine whether the user can view any model.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can list teams they belong to
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Team $team): bool
    {
        // Can view if super admin or member of team
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasTeamAccess($team);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): bool
    {
        // Super Admin or team owner
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->isTeamOwner($team);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): bool
    {
        // Super Admin or team owner
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->isTeamOwner($team);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Team $team): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Team $team): bool
    {
        return $user->hasRole('super_admin');
    }
}
