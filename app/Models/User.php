<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Attributes\UserAttributes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UserAttributes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        if (!array_key_exists('email_verified_at', $array)) {
            $array['email_verified_at'] = null;
        }

        return $array;
    }

    public function cartItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeAdmins(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('role', 'admin');
    }

    public function scopeCustomers(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where(function (\Illuminate\Database\Eloquent\Builder $query) {
            $query->where('role', 'customer')
                ->orWhereNull('role');
        });
    }

    public function scopeVerified(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeUnverified(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNull('email_verified_at');
    }

    public function wishlist(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function addresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function defaultShippingAddress(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserAddress::class)->where('is_default_shipping', true);
    }

    public function defaultBillingAddress(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserAddress::class)->where('is_default_billing', true);
    }

    public function media(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->ordered();
    }

    public function avatar(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Media::class, 'mediable')->images()->primary();
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function approvedComments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->approved();
    }

    public function tags(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function feedback(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    // BelongsToMany Relationships
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot([
                'assigned_by',
                'assigned_at',
                'expires_at',
                'is_active',
                'notes',
                'metadata',
            ])
            ->withTimestamps()
            ->wherePivot('is_active', true);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)
            ->withPivot([
                'assigned_by',
                'assigned_at',
                'expires_at',
                'is_active',
                'notes',
                'metadata',
            ])
            ->withTimestamps()
            ->wherePivot('is_active', true);
    }

    public function activeRoles(): BelongsToMany
    {
        return $this->roles()->where('roles.is_active', true);
    }

    public function activePermissions(): BelongsToMany
    {
        return $this->permissions()->where('permissions.is_active', true);
    }

    // Role and Permission Methods
    public function hasRole(string $role): bool
    {
        // First check the relationship (preferred method)
        $hasRoleViaRelationship = $this->roles()->where('slug', $role)->exists();
        
        // Fallback to string field for backward compatibility
        $hasRoleViaString = $this->role === $role;
        
        return $hasRoleViaRelationship || $hasRoleViaString;
    }

    public function hasAnyRole(array $roles): bool
    {
        // First check the relationship (preferred method)
        $hasAnyRoleViaRelationship = $this->roles()->whereIn('slug', $roles)->exists();
        
        // Fallback to string field for backward compatibility
        $hasAnyRoleViaString = in_array($this->role, $roles);
        
        return $hasAnyRoleViaRelationship || $hasAnyRoleViaString;
    }

    public function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }
        return true;
    }

    public function hasPermission(string $permission): bool
    {
        // Check direct permission assignment
        if ($this->permissions()->where('slug', $permission)->exists()) {
            return true;
        }

        // Check permissions through roles
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    public function assignRole(Role|string $role, array $pivotData = []): self
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $this->roles()->syncWithoutDetaching([
            $role->id => array_merge([
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
                'is_active' => true,
            ], $pivotData)
        ]);

        return $this;
    }

    public function revokeRole(Role|string $role): self
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $this->roles()->detach($role->id);

        return $this;
    }

    public function syncRoles(array $roles): self
    {
        $roleIds = collect($roles)->map(function ($role) {
            if (is_string($role)) {
                return Role::where('slug', $role)->firstOrFail()->id;
            }
            return $role instanceof Role ? $role->id : $role;
        })->toArray();

        $this->roles()->sync($roleIds);

        return $this;
    }

    public function assignPermission(Permission|string $permission, array $pivotData = []): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->syncWithoutDetaching([
            $permission->id => array_merge([
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
                'is_active' => true,
            ], $pivotData)
        ]);

        return $this;
    }

    public function revokePermission(Permission|string $permission): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->detach($permission->id);

        return $this;
    }

    public function syncPermissions(array $permissions): self
    {
        $permissionIds = collect($permissions)->map(function ($permission) {
            if (is_string($permission)) {
                return Permission::where('slug', $permission)->firstOrFail()->id;
            }
            return $permission instanceof Permission ? $permission->id : $permission;
        })->toArray();

        $this->permissions()->sync($permissionIds);

        return $this;
    }

    public function getRoleNamesAttribute(): array
    {
        return $this->roles->pluck('name')->toArray();
    }

    public function getRoleSlugsAttribute(): array
    {
        return $this->roles->pluck('slug')->toArray();
    }

    public function getPermissionNamesAttribute(): array
    {
        $rolePermissions = $this->roles->pluck('permissions')->flatten()->unique()->toArray();
        $directPermissions = $this->permissions->pluck('name')->toArray();

        return array_unique(array_merge($rolePermissions, $directPermissions));
    }

    public function getPermissionSlugsAttribute(): array
    {
        $rolePermissions = $this->roles->pluck('permissions')->flatten()->unique()->toArray();
        $directPermissions = $this->permissions->pluck('slug')->toArray();

        return array_unique(array_merge($rolePermissions, $directPermissions));
    }

    public function getHighestRoleLevelAttribute(): int
    {
        return $this->roles->max('level') ?? 0;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin') || $this->hasPermission('*');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->isSuperAdmin();
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager') || $this->isAdmin();
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('customer') || !$this->roles->count();
    }

    public function canManage(User $user): bool
    {
        if ($this->id === $user->id) {
            return false;
        }

        return $this->getHighestRoleLevelAttribute() > $user->getHighestRoleLevelAttribute();
    }
}
