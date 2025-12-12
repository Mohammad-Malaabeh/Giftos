<?php

namespace App\Policies;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CouponPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Coupon $coupon): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Coupon $coupon): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Coupon $coupon): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Coupon $coupon): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Coupon $coupon): bool
    {
        return $user->isAdmin();
    }
}
