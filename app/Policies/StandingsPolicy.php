<?php

namespace App\Policies;

use App\Models\Standings;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StandingsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view standings
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Standings $standings): bool
    {
        // All authenticated users can view individual standings
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins can create standings
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Standings $standings): bool
    {
        // Only admins can update standings
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Standings $standings): bool
    {
        // Only admins can delete standings
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Standings $standings): bool
    {
        // Only admins can restore standings
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Standings $standings): bool
    {
        // Only admins can permanently delete standings
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view standings statistics.
     */
    public function viewStats(User $user, Standings $standings): bool
    {
        // All authenticated users can view standings statistics
        return true;
    }
}
