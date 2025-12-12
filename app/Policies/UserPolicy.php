<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->isAdmin() || $user->isManager();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->isAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }

    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }

    public function manageRoles(User $user): bool
    {
        return $user->isAdmin();
    }

    public function updateRole(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }

    public function viewOrders(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->isAdmin() || $user->isManager();
    }

    public function viewAnalytics(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function export(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function verify(User $user): bool
    {
        return $user->isAdmin();
    }

    public function ban(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }
}
