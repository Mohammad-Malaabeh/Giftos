<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'status',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class)->where('status', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeParents(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeChildrenOf(Builder $query, ?int $parentId = null): Builder
    {
        if ($parentId === null) {
            return $query->whereNotNull('parent_id');
        }
        return $query->where('parent_id', $parentId);
    }

    public function scopeWithProductCount(Builder $query): Builder
    {
        return $query->withCount('products');
    }

    public function scopeWithActiveProductCount(Builder $query): Builder
    {
        return $query->withCount(['products' => function (Builder $query) {
            $query->where('status', true);
        }]);
    }

    public function scopeWithChildrenCount(Builder $query): Builder
    {
        return $query->withCount('children');
    }

    public function scopeWithHierarchy(Builder $query): Builder
    {
        return $query->with(['parent', 'children']);
    }
}
