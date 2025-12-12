<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission('roles.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('roles.create');
    }

    public function update(User $user, Role $role): bool
    {
        // Users can only update roles at or below their own level
        if ($user->getHighestRoleLevelAttribute() <= $role->level) {
            return false;
        }

        return $user->hasPermission('roles.update');
    }

    public function delete(User $user, Role $role): bool
    {
        // Cannot delete roles with users or higher/equal level roles
        if ($role->users_count > 0 || $user->getHighestRoleLevelAttribute() <= $role->level) {
            return false;
        }

        return $user->hasPermission('roles.delete');
    }

    public function assign(User $user): bool
    {
        return $user->hasPermission('roles.assign');
    }

    public function managePermissions(User $user, Role $role): bool
    {
        // Users can only manage permissions for roles at or below their own level
        if ($user->getHighestRoleLevelAttribute() <= $role->level) {
            return false;
        }

        return $user->hasPermission('roles.permissions');
    }

    public function assignUsers(User $user, Role $role): bool
    {
        // Users can only assign users to roles at or below their own level
        if ($user->getHighestRoleLevelAttribute() <= $role->level) {
            return false;
        }

        return $user->hasPermission('roles.assign');
    }

    public function bulkManage(User $user): bool
    {
        return $user->hasPermission('roles.bulk');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('roles.export');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('roles.import');
    }
}
