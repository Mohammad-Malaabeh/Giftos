<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Product $product): bool
    {
        return $product->status || ($user && $user->isAdmin());
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    public function manageInventory(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function updatePricing(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function viewAnalytics(User $user, Product $product): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function toggleFeatured(User $user): bool
    {
        return $user->isAdmin();
    }

    public function bulkUpdate(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function export(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }
}
