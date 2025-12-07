<?php

namespace Domains\Auth\Policies;

use Domains\Auth\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any model.
     */
    public function viewAny(User $user): bool
    {
        // Super Admin and Farm Manager can view users
        return $user->hasRole(['Super Admin', 'Farm Manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Can view if super admin or same user
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Super Admin can update anyone
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Users can update themselves
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Super Admin only
        return $user->hasRole('Super Admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasRole('Super Admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasRole('Super Admin');
    }
}
