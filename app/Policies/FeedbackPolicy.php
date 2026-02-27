<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;

class FeedbackPolicy
{
    /**
     * Determine whether the user can view any feedback (admin list).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the feedback (moderate content).
     */
    public function delete(User $user, Feedback $feedback): bool
    {
        return $user->hasRole('admin');
    }
}
