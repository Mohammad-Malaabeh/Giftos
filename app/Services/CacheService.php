<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CacheService
{
    private const DEFAULT_TTL = 3600; // 1 hour
    private const LONG_TTL = 86400; // 24 hours
    private const SHORT_TTL = 300; // 5 minutes

    private string $prefix = 'giftos_';

    public function remember(string $key, callable $callback, int $ttl = self::DEFAULT_TTL): mixed
    {
        $fullKey = $this->prefix . $key;
        return Cache::remember($fullKey, $ttl, $callback);
    }

    public function rememberWithTags(array $tags, string $key, callable $callback, int $ttl = self::DEFAULT_TTL): mixed
    {
        $fullKey = $this->prefix . $key;
        
        if ($this->supportsTags()) {
            return Cache::tags($this->formatTags($tags))->remember($fullKey, $ttl, $callback);
        }
        
        return Cache::remember($fullKey, $ttl, $callback);
    }

    public function forever(string $key, callable $callback): mixed
    {
        $fullKey = $this->prefix . $key;
        return Cache::rememberForever($fullKey, $callback);
    }

    public function foreverWithTags(array $tags, string $key, callable $callback): mixed
    {
        $fullKey = $this->prefix . $key;
        
        if ($this->supportsTags()) {
            return Cache::tags($this->formatTags($tags))->rememberForever($fullKey, $callback);
        }
        
        return Cache::rememberForever($fullKey, $callback);
    }

    public function put(string $key, mixed $value, int $ttl = self::DEFAULT_TTL): bool
    {
        $fullKey = $this->prefix . $key;
        return Cache::put($fullKey, $value, $ttl);
    }

    public function putWithTags(array $tags, string $key, mixed $value, int $ttl = self::DEFAULT_TTL): bool
    {
        $fullKey = $this->prefix . $key;
        
        if ($this->supportsTags()) {
            return Cache::tags($this->formatTags($tags))->put($fullKey, $value, $ttl);
        }
        
        return Cache::put($fullKey, $value, $ttl);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $fullKey = $this->prefix . $key;
        return Cache::get($fullKey, $default);
    }

    public function has(string $key): bool
    {
        $fullKey = $this->prefix . $key;
        return Cache::has($fullKey);
    }

    public function forget(string $key): bool
    {
        $fullKey = $this->prefix . $key;
        return Cache::forget($fullKey);
    }

    public function forgetByTags(array $tags): bool
    {
        if ($this->supportsTags()) {
            return Cache::tags($this->formatTags($tags))->flush();
        }
        
        return false;
    }

    public function clear(): bool
    {
        return Cache::flush();
    }

    public function increment(string $key, int $value = 1): int
    {
        $fullKey = $this->prefix . $key;
        return Cache::increment($fullKey, $value);
    }

    public function decrement(string $key, int $value = 1): int
    {
        $fullKey = $this->prefix . $key;
        return Cache::decrement($fullKey, $value);
    }

    public function lock(string $key, int $seconds = 10): \Illuminate\Cache\Lock
    {
        $fullKey = $this->prefix . $key;
        return Cache::lock($fullKey, $seconds);
    }

    // Product caching methods
    public function getProducts(?callable $callback = null, int $ttl = self::DEFAULT_TTL): Collection|array
    {
        $key = 'products:active:with_relations';
        
        if ($callback) {
            return $this->remember($key, $callback, $ttl);
        }

        return $this->remember($key, function () {
            return \App\Models\Product::with(['category', 'variants'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('status', true)
                ->get();
        }, $ttl);
    }

    public function getProduct(int $id, ?callable $callback = null, int $ttl = self::LONG_TTL): ?\App\Models\Product
    {
        $key = "product:{$id}:full";
        
        if ($callback) {
            return $this->remember($key, $callback, $ttl);
        }

        return $this->remember($key, function () use ($id) {
            return \App\Models\Product::with(['category', 'variants.options', 'reviews.user'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->find($id);
        }, $ttl);
    }

    public function getProductBySlug(string $slug, ?callable $callback = null, int $ttl = self::LONG_TTL): ?\App\Models\Product
    {
        $key = "product:slug:{$slug}:full";
        
        if ($callback) {
            return $this->remember($key, $callback, $ttl);
        }

        return $this->remember($key, function () use ($slug) {
            return \App\Models\Product::with(['category', 'variants.options', 'reviews.user'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('slug', $slug)
                ->first();
        }, $ttl);
    }

    public function getFeaturedProducts(int $limit = 8, int $ttl = self::DEFAULT_TTL): Collection
    {
        $key = "products:featured:{$limit}";
        
        return $this->remember($key, function () use ($limit) {
            return \App\Models\Product::with(['category', 'variants'])
                ->withAvg('reviews', 'rating')
                ->where('featured', true)
                ->where('status', true)
                ->orderBy('views', 'desc')
                ->take($limit)
                ->get();
        }, $ttl);
    }

    public function getLatestProducts(int $limit = 12, int $ttl = self::DEFAULT_TTL): Collection
    {
        $key = "products:latest:{$limit}";
        
        return $this->remember($key, function () use ($limit) {
            return \App\Models\Product::with(['category', 'variants'])
                ->withAvg('reviews', 'rating')
                ->where('status', true)
                ->latest()
                ->take($limit)
                ->get();
        }, $ttl);
    }

    public function getProductsOnSale(int $ttl = self::DEFAULT_TTL): Collection
    {
        $key = 'products:on_sale';
        
        return $this->remember($key, function () {
            return \App\Models\Product::with(['category', 'variants'])
                ->withAvg('reviews', 'rating')
                ->where('sale_price', '>', 0)
                ->where('status', true)
                ->orderBy('sale_price', 'asc')
                ->get();
        }, $ttl);
    }

    // Category caching methods
    public function getCategories(?callable $callback = null, int $ttl = self::LONG_TTL): Collection|array
    {
        $key = 'categories:active:with_counts';
        
        if ($callback) {
            return $this->remember($key, $callback, $ttl);
        }

        return $this->remember($key, function () {
            return \App\Models\Category::with(['parent', 'children'])
                ->withCount(['products' => function ($query) {
                    $query->where('status', true);
                }])
                ->where('status', true)
                ->orderBy('name')
                ->get();
        }, $ttl);
    }

    public function getCategory(int $id, int $ttl = self::LONG_TTL): ?\App\Models\Category
    {
        $key = "category:{$id}:with_products";
        
        return $this->remember($key, function () use ($id) {
            return \App\Models\Category::with(['parent', 'children', 'products' => function ($query) {
                $query->where('status', true)->take(12);
            }])
            ->withCount(['products' => function ($query) {
                $query->where('status', true);
            }])
            ->find($id);
        }, $ttl);
    }

    // User caching methods
    public function getUser(int $id, int $ttl = self::DEFAULT_TTL): ?\App\Models\User
    {
        $key = "user:{$id}:profile";
        
        return $this->remember($key, function () use ($id) {
            return \App\Models\User::with(['addresses', 'orders' => function ($query) {
                $query->latest()->take(5);
            }])->find($id);
        }, $ttl);
    }

    // Search caching
    public function getSearchResults(string $query, array $filters = [], int $ttl = self::SHORT_TTL): Collection
    {
        $key = 'search:' . md5($query . serialize($filters));
        
        return $this->remember($key, function () use ($query, $filters) {
            $productQuery = \App\Models\Product::with(['category', 'variants'])
                ->withAvg('reviews', 'rating')
                ->where('status', true);

            if (!empty($query)) {
                $productQuery->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhere('sku', 'like', "%{$query}%");
                });
            }

            if (!empty($filters['category'])) {
                $productQuery->whereHas('category', function ($q) use ($filters) {
                    $q->where('slug', $filters['category']);
                });
            }

            if (isset($filters['price_min'])) {
                $productQuery->where('price', '>=', $filters['price_min']);
            }

            if (isset($filters['price_max'])) {
                $productQuery->where('price', '<=', $filters['price_max']);
            }

            if (!empty($filters['featured'])) {
                $productQuery->where('featured', true);
            }

            if (!empty($filters['on_sale'])) {
                $productQuery->where('sale_price', '>', 0);
            }

            return $productQuery->get();
        }, $ttl);
    }

    // Cache invalidation methods
    public function invalidateProduct(int $productId): void
    {
        $product = \App\Models\Product::find($productId);
        
        if ($product) {
            $this->forget("product:{$productId}:full");
            $this->forget("product:slug:{$product->slug}:full");
            $this->forget('products:active:with_relations');
            $this->forget('products:featured:8');
            $this->forget('products:featured:12');
            $this->forget('products:latest:12');
            $this->forget('products:on_sale');
            
            if ($product->category_id) {
                $this->forget("category:{$product->category_id}:with_products");
                $this->forget('categories:active:with_counts');
            }
            
            $this->clearSearchCache();
        }
    }

    public function invalidateCategory(int $categoryId): void
    {
        $this->forget("category:{$categoryId}:with_products");
        $this->forget('categories:active:with_counts');
        $this->forget('products:active:with_relations');
        $this->clearSearchCache();
    }

    public function invalidateUser(int $userId): void
    {
        $this->forget("user:{$userId}:profile");
    }

    public function clearSearchCache(): void
    {
        $pattern = 'search:*';
        if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
            $this->deleteKeysByPattern($pattern);
        }
    }

    public function clearProductCache(): void
    {
        $patterns = [
            'products:*',
            'product:*',
            'search:*'
        ];

        foreach ($patterns as $pattern) {
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                $this->deleteKeysByPattern($pattern);
            }
        }
    }

    /**
     * Delete Redis keys matching a pattern using SCAN to avoid blocking Redis.
     */
    private function deleteKeysByPattern(string $pattern): void
    {
        try {
            $redis = Redis::connection('cache');
            $fullPattern = config('cache.prefix') . ':' . $this->prefix . $pattern;

            $cursor = '0';
            do {
                // Use the SCAN command to iterate keys in a non-blocking way
                $result = $redis->command('SCAN', [$cursor, 'MATCH', $fullPattern, 'COUNT', 1000]);

                if (!is_array($result) || count($result) < 2) {
                    break;
                }

                $cursor = (string) $result[0];
                $keys = $result[1] ?? [];

                if (!empty($keys)) {
                    // delete in chunks to avoid argument length limits
                    foreach (array_chunk($keys, 500) as $chunk) {
                        $redis->del($chunk);
                    }
                }
            } while ($cursor !== '0');
        } catch (\Exception $e) {
            // Non-fatal: log and continue
            try {
                if (class_exists('\Illuminate\Support\Facades\Log')) {
                    \Illuminate\Support\Facades\Log::warning('Failed to delete keys by pattern', ['pattern' => $pattern, 'error' => $e->getMessage()]);
                }
            } catch (\Throwable $_) {
                // ignore logging failures
            }
        }
    }

    // Enhanced cache statistics
    public function getStats(): array
    {
        if (config('cache.default') === 'redis' && class_exists('Redis')) {
            $redis = Redis::connection('cache');
            
            return [
                'keys_count' => $redis->dbSize(),
                'memory_usage' => $redis->info('memory')['used_memory_human'] ?? 'N/A',
                'hit_rate' => $this->calculateHitRate(),
            ];
        }
        
        return [
            'keys_count' => 'N/A',
            'memory_usage' => 'N/A',
            'hit_rate' => 'N/A',
        ];
    }

    public function warmUp(): void
    {
        $this->remember('categories_all', function () {
            return \App\Models\Category::withCount('products')->get();
        }, 7200);

        $this->remember('products_featured', function () {
            return \App\Models\Product::where('featured', true)
                ->where('status', true)
                ->with(['category'])
                ->withAvg('reviews', 'rating')
                ->limit(8)
                ->get();
        }, 3600);

        $this->remember('products_latest', function () {
            return \App\Models\Product::where('status', true)
                ->with(['category'])
                ->withAvg('reviews', 'rating')
                ->latest()
                ->limit(12)
                ->get();
        }, 1800);
    }

    private function formatTags(array $tags): array
    {
        return array_map(fn($tag) => $this->prefix . $tag, $tags);
    }

    private function supportsTags(): bool
    {
        $store = Cache::getStore();
        return $store instanceof \Illuminate\Cache\TaggableStore;
    }

    private function calculateHitRate(): float
    {
        if (config('cache.default') === 'redis' && class_exists('Redis')) {
            $redis = Redis::connection('cache');
            $stats = $redis->info('stats');
            
            $hits = $stats['keyspace_hits'] ?? 0;
            $misses = $stats['keyspace_misses'] ?? 0;
            $total = $hits + $misses;

            return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
        }
        
        return 0;
    }
}
