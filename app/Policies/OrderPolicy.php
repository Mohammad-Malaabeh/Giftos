<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->isManager() || $order->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        return $order->user_id === $user->id &&
            in_array($order->status, ['pending', 'processing']);
    }

    public function refund(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function restore(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function viewAnalytics(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function export(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function bulkUpdate(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }
}
