<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $product)
    {
        parent::__construct($product);
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->query
            ->with(['category', 'variants.options', 'reviews.user'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('slug', $slug)
            ->first();
    }

    public function findByCategory(int $categoryId): Collection
    {
        return $this->query
            ->with(['category', 'variants'])
            ->withAvg('reviews', 'rating')
            ->whereHas('category', function (Builder $query) use ($categoryId) {
                $query->where('id', $categoryId);
            })
            ->get();
    }

    public function search(string $query): self
    {
        $this->query
            ->where(function (Builder $q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%");
            });

        return $this;
    }

    public function getFeatured(int $limit = 8): Collection
    {
        return $this->query
            ->with(['category', 'variants'])
            ->withAvg('reviews', 'rating')
            ->where('featured', true)
            ->where('status', true)
            ->orderBy('views', 'desc')
            ->take($limit)
            ->get();
    }

    public function getLatest(int $limit = 12): Collection
    {
        return $this->query
            ->with(['category', 'variants'])
            ->withAvg('reviews', 'rating')
            ->where('status', true)
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getOnSale(): Collection
    {
        return $this->query
            ->with(['category', 'variants'])
            ->withAvg('reviews', 'rating')
            ->where('sale_price', '>', 0)
            ->where('status', true)
            ->orderBy('sale_price', 'asc')
            ->get();
    }

    public function getInStock(): Collection
    {
        return $this->query->where('stock', '>', 0)
            ->where('status', true)
            ->get();
    }

    public function getOutOfStock(): Collection
    {
        return $this->query->where('stock', '<=', 0)
            ->where('status', true)
            ->get();
    }

    public function getByPriceRange(float $min, float $max): Collection
    {
        return $this->query->whereBetween('price', [$min, $max])
            ->where('status', true)
            ->get();
    }

    public function getActive(): Collection
    {
        return $this->query
            ->with(['category', 'variants'])
            ->withAvg('reviews', 'rating')
            ->where('status', true)
            ->get();
    }

    public function getInactive(): Collection
    {
        return $this->query->where('status', false)->get();
    }

    public function withVariants(): self
    {
        $this->query->with(['variants.options']);
        return $this;
    }

    public function withCategories(): self
    {
        $this->query->with('category');
        return $this;
    }

    public function withReviews(): self
    {
        $this->query->with(['reviews.user']);
        return $this;
    }

    public function withAverageRating(): self
    {
        $this->query->withAvg('reviews', 'rating');
        return $this;
    }

    public function withReviewsCount(): self
    {
        $this->query->withCount('reviews');
        return $this;
    }

    public function incrementViews(int $productId): void
    {
        $this->model->where('id', $productId)->increment('views');
    }

    public function getActiveWithFilters(array $filters = []): Builder
    {
        $query = $this->query
            ->with(['category', 'variants'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('status', true);

        if (isset($filters['category'])) {
            $query->whereHas('category', function (Builder $q) use ($filters) {
                $q->where('slug', $filters['category']);
            });
        }

        if (isset($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if (isset($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }

        if (isset($filters['featured'])) {
            $query->where('featured', true);
        }

        if (isset($filters['on_sale'])) {
            $query->where('sale_price', '>', 0);
        }

        if (isset($filters['search'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%")
                    ->orWhere('sku', 'like', "%{$filters['search']}%");
            });
        }

        return $query;
    }

    public function paginateActiveWithFilters(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->getActiveWithFilters($filters)
            ->paginate($perPage);
    }

    // Chainable convenience methods required by the interface
    public function featured(): self
    {
        $this->query->where('featured', true)->where('status', true);
        return $this;
    }

    public function active(): self
    {
        $this->query->where('status', true);
        return $this;
    }

    public function onSale(): self
    {
        $this->query->where('sale_price', '>', 0)->where('status', true);
        return $this;
    }
}
