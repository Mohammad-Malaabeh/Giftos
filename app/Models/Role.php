<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'is_default',
        'level',
        'permissions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'level' => 'integer',
        'permissions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($role) {
            if (empty($role->slug)) {
                $role->slug = str($role->name)->slug()->toString();
            }
        });

        static::updating(function ($role) {
            if ($role->isDirty('name') && empty($role->slug)) {
                $role->slug = str($role->name)->slug()->toString();
            }
        });
    }

    // Relationships
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
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
                'is_active',
                'notes',
                'metadata',
            ])
            ->withTimestamps()
            ->wherePivot('is_active', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeMinLevel($query, $level)
    {
        return $query->where('level', '>=', $level);
    }

    public function scopeMaxLevel($query, $level)
    {
        return $query->where('level', '<=', $level);
    }

    // Methods
    public function hasPermission(string $permission): bool
    {
        // Check if permission is in the permissions array
        if (isset($this->permissions) && in_array($permission, $this->permissions)) {
            return true;
        }

        // Check if permission is granted through relationship
        return $this->permissions()->where('slug', $permission)->exists();
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

    public function assignPermission(Permission|string $permission, array $pivotData = []): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->syncWithoutDetaching([$permission->id => array_merge([
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'is_active' => true,
        ], $pivotData)]);

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

    public function addUser(User $user, array $pivotData = []): self
    {
        $this->users()->syncWithoutDetaching([$user->id => array_merge([
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'is_active' => true,
        ], $pivotData)]);

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users()->detach($user->id);

        return $this;
    }

    public function getPermissionNamesAttribute(): array
    {
        return $this->permissions->pluck('name')->toArray();
    }

    public function getPermissionSlugsAttribute(): array
    {
        return $this->permissions->pluck('slug')->toArray();
    }

    public function getUsersCountAttribute(): int
    {
        return $this->users()->count();
    }

    public function isHigherThan(Role $role): bool
    {
        return $this->level > $role->level;
    }

    public function isLowerThan(Role $role): bool
    {
        return $this->level < $role->level;
    }

    public function isSameLevel(Role $role): bool
    {
        return $this->level === $role->level;
    }

    public function canManage(Role $role): bool
    {
        return $this->isHigherThan($role) || $this->isSameLevel($role);
    }

    // Static methods
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    public static function getDefault(): ?self
    {
        return static::default()->active()->first();
    }

    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()->orderBy('level')->get();
    }
}
