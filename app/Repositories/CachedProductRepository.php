<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CachedProductRepository extends ProductRepository implements ProductRepositoryInterface
{
    protected CacheService $cacheService;

    public function __construct(Product $product, CacheService $cacheService)
    {
        parent::__construct($product);
        $this->cacheService = $cacheService;
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->cacheService->remember(
            "product_slug_{$slug}",
            function () use ($slug) {
                return parent::findBySlug($slug);
            },
            3600 // 1 hour
        );
    }

    public function findByCategory(int $categoryId): Collection
    {
        return $this->cacheService->rememberWithTags(
            ['products', 'category_' . $categoryId],
            "products_category_{$categoryId}",
            function () use ($categoryId) {
                return parent::findByCategory($categoryId);
            },
            1800 // 30 minutes
        );
    }

    public function search(string $query): Collection
    {
        $cacheKey = "products_search_" . md5($query);
        
        return $this->cacheService->remember(
            $cacheKey,
            function () use ($query) {
                return parent::search($query);
            },
            900 // 15 minutes
        );
    }

    public function getFeatured(int $limit = 8): Collection
    {
        return $this->cacheService->remember(
            "products_featured_{$limit}",
            function () use ($limit) {
                return parent::getFeatured($limit);
            },
            3600 // 1 hour
        );
    }

    public function getLatest(int $limit = 12): Collection
    {
        return $this->cacheService->remember(
            "products_latest_{$limit}",
            function () use ($limit) {
                return parent::getLatest($limit);
            },
            1800 // 30 minutes
        );
    }

    public function getOnSale(): Collection
    {
        return $this->cacheService->remember(
            "products_on_sale",
            function () {
                return parent::getOnSale();
            },
            1800 // 30 minutes
        );
    }

    public function getInStock(): Collection
    {
        return $this->cacheService->remember(
            "products_in_stock",
            function () {
                return parent::getInStock();
            },
            900 // 15 minutes
        );
    }

    public function getOutOfStock(): Collection
    {
        return $this->cacheService->remember(
            "products_out_of_stock",
            function () {
                return parent::getOutOfStock();
            },
            900 // 15 minutes
        );
    }

    public function getByPriceRange(float $min, float $max): Collection
    {
        $cacheKey = "products_price_range_{$min}_{$max}";
        
        return $this->cacheService->remember(
            $cacheKey,
            function () use ($min, $max) {
                return parent::getByPriceRange($min, $max);
            },
            1800 // 30 minutes
        );
    }

    public function getActive(): Collection
    {
        return $this->cacheService->remember(
            "products_active",
            function () {
                return parent::getActive();
            },
            900 // 15 minutes
        );
    }

    public function getInactive(): Collection
    {
        return $this->cacheService->remember(
            "products_inactive",
            function () {
                return parent::getInactive();
            },
            900 // 15 minutes
        );
    }

    public function create(array $data): Product
    {
        $product = parent::create($data);
        
        // Clear relevant caches
        $this->clearProductCaches();
        
        return $product;
    }

    public function update($id, array $data): Product
    {
        $product = parent::update($id, $data);
        
        // Clear relevant caches
        $this->clearProductCaches();
        
        if ($product->slug) {
            $this->cacheService->forget("product_slug_{$product->slug}");
        }
        
        return $product;
    }

    public function delete($id): bool
    {
        $product = $this->find($id);
        $result = parent::delete($id);
        
        if ($result && $product) {
            // Clear relevant caches
            $this->clearProductCaches();
            
            if ($product->slug) {
                $this->cacheService->forget("product_slug_{$product->slug}");
            }
        }
        
        return $result;
    }

    public function incrementViews($productId): void
    {
        parent::incrementViews($productId);
        
        // Clear cached product if exists
        $product = $this->find($productId);
        if ($product && $product->slug) {
            $this->cacheService->forget("product_slug_{$product->slug}");
        }
    }

    public function getActiveWithFilters(array $filters = []): Builder
    {
        $cacheKey = "products_filtered_" . md5(serialize($filters));
        
        // For complex queries, we'll cache the results, not the builder
        $results = $this->cacheService->remember(
            $cacheKey,
            function () use ($filters) {
                $query = parent::getActiveWithFilters($filters);
                return $query->get();
            },
            900 // 15 minutes
        );
        
        // Return a builder-like object with the cached results
        return Product::whereIn('id', $results->pluck('id'));
    }

    /**
     * Clear all product-related caches
     */
    private function clearProductCaches(): void
    {
        $this->cacheService->forgetByTags(['products']);
        $this->cacheService->forget('products_active');
        $this->cacheService->forget('products_inactive');
        $this->cacheService->forget('products_in_stock');
        $this->cacheService->forget('products_out_of_stock');
        $this->cacheService->forget('products_on_sale');
        $this->cacheService->forget('products_featured_8');
        $this->cacheService->forget('products_latest_12');
    }

    /**
     * Warm up product cache
     */
    public function warmUpCache(): void
    {
        $this->cacheService->warmUp();
        
        // Additional product-specific cache warming
        $this->getFeatured(8);
        $this->getLatest(12);
        $this->getOnSale();
        $this->getInStock();
    }

    /**
     * Get cache statistics for products
     */
    public function getCacheStats(): array
    {
        return [
            'featured_products_cached' => $this->cacheService->has('products_featured_8'),
            'latest_products_cached' => $this->cacheService->has('products_latest_12'),
            'on_sale_products_cached' => $this->cacheService->has('products_on_sale'),
            'active_products_cached' => $this->cacheService->has('products_active'),
        ];
    }
}
