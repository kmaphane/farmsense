<?php

namespace Domains\CRM\Policies;

use Domains\Auth\Models\User;
use Domains\CRM\Models\Customer;

class CustomerPolicy
{
    /**
     * Determine whether the user can view any model.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'Farm Manager', 'Partner']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $customer): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Team-scoped: user must be in the customer's team
        return $user->currentTeam()?->id === $customer->team_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'Farm Manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $customer): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Only Farm Manager in the same team
        return $user->hasRole('Farm Manager') &&
               $user->currentTeam()?->id === $customer->team_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customer $customer): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasRole('Farm Manager') &&
               $user->currentTeam()?->id === $customer->team_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Customer $customer): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        return $user->hasRole('super_admin');
    }
}
