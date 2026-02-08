<?php

namespace App\Policies;

use App\Models\Teams;
use App\Models\User;

class TeamsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view teams
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Teams $teams): bool
    {
        // All authenticated users can view individual teams
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins can create teams
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Teams $teams): bool
    {
        // Only admins can update teams
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Teams $teams): bool
    {
        // Only admins can delete teams
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Teams $teams): bool
    {
        // Only admins can restore teams
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Teams $teams): bool
    {
        // Only admins can permanently delete teams
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view team statistics.
     */
    public function viewStats(User $user, Teams $teams): bool
    {
        // All authenticated users can view team statistics
        return true;
    }
}
