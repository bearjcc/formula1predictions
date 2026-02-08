<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only admins can view all users
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Users can view their own profile, admins can view any profile
        return $user->id === $model->id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins can create users
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Users can update their own profile, admins can update any profile
        return $user->id === $model->id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Only admins can delete users, and they cannot delete themselves
        return $user->hasRole('admin') && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        // Only admins can restore users
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Only admins can permanently delete users, and they cannot delete themselves
        return $user->hasRole('admin') && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can manage roles.
     */
    public function manageRoles(User $user, User $model): bool
    {
        // Only admins can manage roles
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view user statistics.
     */
    public function viewStats(User $user, User $model): bool
    {
        // Users can view their own stats, admins can view any stats
        return $user->id === $model->id || $user->hasRole('admin');
    }
}
