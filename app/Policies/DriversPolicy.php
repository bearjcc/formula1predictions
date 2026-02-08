<?php

namespace App\Policies;

use App\Models\Drivers;
use App\Models\User;

class DriversPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view drivers
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Drivers $drivers): bool
    {
        // All authenticated users can view individual drivers
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins can create drivers
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Drivers $drivers): bool
    {
        // Only admins can update drivers
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Drivers $drivers): bool
    {
        // Only admins can delete drivers
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Drivers $drivers): bool
    {
        // Only admins can restore drivers
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Drivers $drivers): bool
    {
        // Only admins can permanently delete drivers
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view driver statistics.
     */
    public function viewStats(User $user, Drivers $drivers): bool
    {
        // All authenticated users can view driver statistics
        return true;
    }
}
