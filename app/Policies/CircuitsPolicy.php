<?php

namespace App\Policies;

use App\Models\Circuits;
use App\Models\User;

class CircuitsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view circuits
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Circuits $circuits): bool
    {
        // All authenticated users can view individual circuits
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins can create circuits
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Circuits $circuits): bool
    {
        // Only admins can update circuits
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Circuits $circuits): bool
    {
        // Only admins can delete circuits
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Circuits $circuits): bool
    {
        // Only admins can restore circuits
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Circuits $circuits): bool
    {
        // Only admins can permanently delete circuits
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view circuit statistics.
     */
    public function viewStats(User $user, Circuits $circuits): bool
    {
        // All authenticated users can view circuit statistics
        return true;
    }
}
