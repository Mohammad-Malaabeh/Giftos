<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Auth\Access\Response;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('permissions.view');
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->hasPermission('permissions.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('permissions.create');
    }

    public function update(User $user, Permission $permission): bool
    {
        // Only super admins can modify system permissions
        if ($permission->level >= 100) {
            return $user->isSuperAdmin();
        }

        return $user->hasPermission('permissions.update');
    }

    public function delete(User $user, Permission $permission): bool
    {
        // Cannot delete permissions that are assigned to roles or system permissions
        if ($permission->roles_count > 0 || $permission->level >= 100) {
            return false;
        }

        return $user->hasPermission('permissions.delete');
    }

    public function assign(User $user): bool
    {
        return $user->hasPermission('permissions.assign');
    }

    public function assignToRoles(User $user): bool
    {
        return $user->hasPermission('permissions.assign') && $user->hasPermission('roles.permissions');
    }

    public function assignToUsers(User $user): bool
    {
        return $user->hasPermission('permissions.assign') && $user->hasPermission('users.permissions');
    }

    public function bulkManage(User $user): bool
    {
        return $user->hasPermission('permissions.bulk');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('permissions.export');
    }

    public function import(User $user): bool
    {
        // Only super admins can import permissions
        return $user->isSuperAdmin();
    }

    public function manageGroups(User $user): bool
    {
        return $user->hasPermission('permissions.groups');
    }
}
