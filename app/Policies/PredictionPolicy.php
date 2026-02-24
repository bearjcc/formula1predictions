<?php

namespace App\Policies;

use App\Models\Prediction;
use App\Models\User;

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
        // Users can view their own predictions, admins and moderators can view all
        return $user->id === $prediction->user_id || $user->hasRole('admin') || $user->hasRole('moderator');
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
        // Admins and moderators can update any prediction
        if ($user->hasRole('admin') || $user->hasRole('moderator')) {
            return true;
        }

        // Users can edit their own predictions while they are still editable
        // (not locked/scored and before the deadline — matches Prediction::isEditable())
        return $user->id === $prediction->user_id && $prediction->isEditable();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Prediction $prediction): bool
    {
        // Admins and moderators can delete any prediction
        if ($user->hasRole('admin') || $user->hasRole('moderator')) {
            return true;
        }

        // Users can only delete predictions that are still editable
        return $user->id === $prediction->user_id && $prediction->isEditable();
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
