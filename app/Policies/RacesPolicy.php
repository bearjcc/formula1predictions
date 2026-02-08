<?php

namespace App\Policies;

use App\Models\Races;
use App\Models\User;

class RacesPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view races
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Races $races): bool
    {
        // All authenticated users can view individual races
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins can create races
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Races $races): bool
    {
        // Only admins can update races
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Races $races): bool
    {
        // Only admins can delete races
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Races $races): bool
    {
        // Only admins can restore races
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Races $races): bool
    {
        // Only admins can permanently delete races
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can manage race results.
     */
    public function manageResults(User $user, Races $races): bool
    {
        // Only admins can manage race results
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view race predictions.
     */
    public function viewPredictions(User $user, Races $races): bool
    {
        // All authenticated users can view race predictions
        return true;
    }
}
