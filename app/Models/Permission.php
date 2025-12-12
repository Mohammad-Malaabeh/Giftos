<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'group',
        'is_active',
        'level',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($permission) {
            if (empty($permission->slug)) {
                $permission->slug = str($permission->name)->slug()->toString();
            }
        });

        static::updating(function ($permission) {
            if ($permission->isDirty('name') && empty($permission->slug)) {
                $permission->slug = str($permission->name)->slug()->toString();
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

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
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

    public function scopeByGroup($query, $group)
    {
        return $query->where('group', $group);
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
    public function assignToRole(Role|string $role, array $pivotData = []): self
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $role->permissions()->syncWithoutDetaching([$this->id => array_merge([
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'is_active' => true,
        ], $pivotData)]);

        return $this;
    }

    public function revokeFromRole(Role|string $role): self
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $role->permissions()->detach($this->id);

        return $this;
    }

    public function assignToUser(User $user, array $pivotData = []): self
    {
        $user->permissions()->syncWithoutDetaching([$this->id => array_merge([
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'is_active' => true,
        ], $pivotData)]);

        return $this;
    }

    public function revokeFromUser(User $user): self
    {
        $user->permissions()->detach($this->id);

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

    public function getUsersCountAttribute(): int
    {
        return $this->users()->count();
    }

    public function getRolesCountAttribute(): int
    {
        return $this->roles()->count();
    }

    public function isHigherThan(Permission $permission): bool
    {
        return $this->level > $permission->level;
    }

    public function isLowerThan(Permission $permission): bool
    {
        return $this->level < $permission->level;
    }

    public function isSameLevel(Permission $permission): bool
    {
        return $this->level === $permission->level;
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

    public static function getByGroup(string $group): \Illuminate\Database\Eloquent\Collection
    {
        return static::byGroup($group)->active()->orderBy('level')->get();
    }

    public static function getGroups(): array
    {
        return static::active()->distinct()->pluck('group')->sort()->toArray();
    }

    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()->orderBy('group')->orderBy('level')->get();
    }
}
