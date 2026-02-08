<?php

namespace App\Policies;

use App\Models\Countries;
use App\Models\User;

class CountriesPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view countries
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Countries $countries): bool
    {
        // All authenticated users can view individual countries
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins can create countries
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Countries $countries): bool
    {
        // Only admins can update countries
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Countries $countries): bool
    {
        // Only admins can delete countries
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Countries $countries): bool
    {
        // Only admins can restore countries
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Countries $countries): bool
    {
        // Only admins can permanently delete countries
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view country statistics.
     */
    public function viewStats(User $user, Countries $countries): bool
    {
        // All authenticated users can view country statistics
        return true;
    }
}
