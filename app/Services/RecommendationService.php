<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RecommendationService
{
    /**
     * Return products frequently purchased together with the given product.
     * Simple co-occurrence within the same orders.
     */
    public static function customersAlsoBought(int $productId, int $limit = 8, int $ttlSeconds = 1800): Collection
    {
        $cacheKey = "reco:also_bought:{$productId}:{$limit}";
        return Cache::remember($cacheKey, $ttlSeconds, function () use ($productId, $limit) {
            $counts = OrderItem::query()
                ->selectRaw('product_id, SUM(quantity) as qty_sum, COUNT(*) as cnt')
                ->whereIn('order_id', function ($q) use ($productId) {
                    $q->select('order_id')
                        ->from('order_items')
                        ->where('product_id', $productId);
                })
                ->where('product_id', '!=', $productId)
                ->groupBy('product_id')
                ->orderByDesc('cnt')
                ->orderByDesc('qty_sum')
                ->limit(50) // fetch top IDs then hydrate
                ->pluck('product_id')
                ->toArray();

            if (empty($counts)) {
                return collect();
            }

            $products = Product::query()
                ->active()
                ->whereIn('id', $counts)
                ->with('category')
                ->get()
                ->sortBy(fn($p) => array_search($p->id, $counts)); // preserve order

            return $products->take($limit)->values();
        });
    }

    /**
     * Related by category (already have in controller, but centralize + cache).
     */
    public static function relatedByCategory(Product $product, int $limit = 8, int $ttlSeconds = 900): Collection
    {
        $cacheKey = "reco:related_cat:{$product->id}:{$product->category_id}:{$limit}";
        return Cache::remember($cacheKey, $ttlSeconds, function () use ($product, $limit) {
            return Product::active()
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->inRandomOrder()
                ->take($limit)
                ->get();
        });
    }
}
