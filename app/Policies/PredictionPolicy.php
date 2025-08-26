<?php

namespace App\Policies;

use App\Models\Prediction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PredictionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Users can view their own predictions
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Prediction $prediction): bool
    {
        // Users can view their own predictions
        return $user->id === $prediction->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create predictions
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Prediction $prediction): bool
    {
        // Users can only update their own predictions that are still in draft status
        return $user->id === $prediction->user_id && $prediction->status === 'draft';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Prediction $prediction): bool
    {
        // Users can only delete their own predictions that are still in draft status
        return $user->id === $prediction->user_id && $prediction->status === 'draft';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Prediction $prediction): bool
    {
        // Only admins can restore predictions
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Prediction $prediction): bool
    {
        // Only admins can permanently delete predictions
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can submit the prediction.
     */
    public function submit(User $user, Prediction $prediction): bool
    {
        // Users can only submit their own predictions that are in draft status
        return $user->id === $prediction->user_id && $prediction->status === 'draft';
    }

    /**
     * Determine whether the user can score the prediction.
     */
    public function score(User $user, Prediction $prediction): bool
    {
        // Only admins or the system can score predictions
        return $user->hasRole('admin') || $user->hasRole('system');
    }
}
