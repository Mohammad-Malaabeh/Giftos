<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\FeaturedScope;
use App\Models\Scopes\OnSaleScope;
use App\Models\Scopes\InStockScope;
use App\Models\Casts\MoneyCast;
use App\Models\Casts\GalleryCast;
use App\Models\Casts\StatusCast;
use App\Models\Attributes\ProductAttributes;

class Product extends Model
{
    use HasFactory, SoftDeletes, ProductAttributes;

    protected static function boot(): void
    {
        parent::boot();

        // Global scopes temporarily disabled due to autoloading issues
        // static::addGlobalScope(new ActiveScope());
    }

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'stock',
        'price',
        'sale_price',
        'sku',
        'image_path',
        'gallery',
        'status',
        'views',
        'featured',
        'backorder_allowed',
        'backorder_eta',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'status' => StatusCast::class,
        'price' => MoneyCast::class,
        'sale_price' => MoneyCast::class,
        'gallery' => GalleryCast::class,
        'featured' => 'boolean',
        'backorder_allowed' => 'boolean',
        'backorder_eta' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cartItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeWithoutGlobalScopes(Builder $query): Builder
    {
        return $query->withoutGlobalScopes();
    }

    public function scopeWithInactive(Builder $query): Builder
    {
        return $query->withoutGlobalScope(ActiveScope::class);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->withoutGlobalScope(ActiveScope::class)
            ->where('featured', true)
            ->where('status', true);
    }

    public function scopeOnSale(Builder $query): Builder
    {
        return $query->withoutGlobalScope(ActiveScope::class)
            ->whereNotNull('sale_price')
            ->where('sale_price', '>', 0)
            ->where('status', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeLowStock(Builder $query, int $threshold = 10): Builder
    {
        return $query->where('stock', '>', 0)->where('stock', '<=', $threshold);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) {
            return $query;
        }
        return $query->where(function (Builder $query) use ($term) {
            $query->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    public function scopeCategoryId(Builder $query, ?int $categoryId): Builder
    {
        if (!$categoryId) {
            return $query;
        }
        return $query->where('category_id', $categoryId);
    }

    public function scopePopular(Builder $query, int $minViews = 100): Builder
    {
        return $query->where('views', '>=', $minViews)->orderByDesc('views');
    }

    public function scopePriceRange(Builder $query, ?float $min = null, ?float $max = null): Builder
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
        return $query;
    }

    public function scopeWithCategory(Builder $query): Builder
    {
        return $query->with('category');
    }
    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function media(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->ordered();
    }

    public function primaryMedia(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Media::class, 'mediable')->primary();
    }

    public function images(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->images()->ordered();
    }

    public function videos(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->videos()->ordered();
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->documents()->ordered();
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->approved()->topLevel();
    }

    public function allComments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function tags(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function approvedReviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->reviews()->where('approved', true);
    }

    public function getAvgRatingAttribute(): ?float
    {
        $average = $this->approvedReviews()->avg('rating');
        return $average !== null ? (float) $average : null;
    }

    // Backwards-compatible `name` attribute used in older tests/fixtures.
    public function getNameAttribute(): string
    {
        return $this->title;
    }

    public function setNameAttribute(string $value): void
    {
        $this->attributes['title'] = $value;
    }

    public function getReviewsCountAttribute(): int
    {
        return (int) $this->approvedReviews()->count();
    }

    public function variants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Variant::class);
    }

    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    // BelongsToMany Relationships
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_product')
            ->withPivot([
                'variant_id',
                'quantity',
                'price',
                'sale_price',
                'total',
                'product_name',
                'product_sku',
                'variant_name',
                'variant_attributes',
                'status',
                'notes',
                'metadata',
            ])
            ->withTimestamps()
            ->orderByPivot('created_at', 'desc');
    }

    public function completedOrders(): BelongsToMany
    {
        return $this->orders()->where('status', 'completed');
    }

    public function pendingOrders(): BelongsToMany
    {
        return $this->orders()->where('status', 'pending');
    }

    public function processingOrders(): BelongsToMany
    {
        return $this->orders()->where('status', 'processing');
    }

    // Order Statistics
    public function getTotalSalesAttribute(): float
    {
        return $this->orders()->sum('total');
    }

    public function getTotalQuantitySoldAttribute(): int
    {
        return $this->orders()->sum('quantity');
    }

    public function getOrderCountAttribute(): int
    {
        return $this->orders()->count();
    }

    public function getAverageOrderValueAttribute(): float
    {
        $orderCount = $this->getOrderCountAttribute();
        return $orderCount > 0 ? $this->getTotalSalesAttribute() / $orderCount : 0;
    }

    public function getSalesTrendAttribute(): array
    {
        return $this->orders()
            ->selectRaw('DATE(created_at) as date, SUM(total) as total_sales, SUM(quantity) as total_quantity')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get()
            ->toArray();
    }

    public function getTopCustomersAttribute(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->orders()
            ->with('user')
            ->selectRaw('user_id, SUM(total) as total_spent, COUNT(*) as order_count')
            ->groupBy('user_id')
            ->orderBy('total_spent', 'desc')
            ->limit(10)
            ->get();
    }

    // Order Methods
    public function isInOrder(int $orderId): bool
    {
        return $this->orders()->where('order_id', $orderId)->exists();
    }

    public function getQuantityInOrder(int $orderId): int
    {
        return $this->orders()->where('order_id', $orderId)->value('quantity') ?? 0;
    }

    public function getOrdersByStatus(string $status): \Illuminate\Database\Eloquent\Collection
    {
        return $this->orders()->wherePivot('status', $status)->get();
    }

    public function getOrdersInDateRange(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return $this->orders()
            ->wherePivotBetween('created_at', [$startDate, $endDate])
            ->get();
    }
}
