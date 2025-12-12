<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Review $review): bool
    {
        return $review->approved || ($user && ($user->isAdmin() || $review->user_id === $user->id));
    }

    public function create(User $user): bool
    {
        return true; // Authenticated users can create reviews
    }

    public function update(User $user, Review $review): bool
    {
        return $user->isAdmin() || $review->user_id === $user->id;
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->isAdmin() || $review->user_id === $user->id;
    }

    public function approve(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }
}
