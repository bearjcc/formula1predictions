<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;

class NewsPolicy
{
    /**
     * Determine whether the user can view any news (admin list).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view a single news post (admin).
     */
    public function view(User $user, News $news): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create news.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the news.
     */
    public function update(User $user, News $news): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the news.
     */
    public function delete(User $user, News $news): bool
    {
        return $user->hasRole('admin');
    }
}
