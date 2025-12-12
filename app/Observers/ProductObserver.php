<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    protected ?CacheService $cacheService;

    public function __construct(CacheService $cacheService = null)
    {
        // Don't use caching in testing environment
        if (!app()->environment('testing')) {
            $this->cacheService = $cacheService;
        }
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        if ($this->cacheService) {
            $this->cacheService->forgetByTags(['products', 'api_responses']);
            Log::info('Product created', ['product_id' => $product->id]);
        }
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        if ($this->cacheService) {
            $this->cacheService->forgetByTags(['products', 'api_responses']);
            
            // Clear specific product cache
            if ($product->slug) {
                $this->cacheService->forget("product_{$product->slug}");
            }
            
            Log::info('Product updated', ['product_id' => $product->id]);
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        if ($this->cacheService) {
            $this->cacheService->forgetByTags(['products', 'api_responses']);
            
            // Clear specific product cache
            if ($product->slug) {
                $this->cacheService->forget("product_{$product->slug}");
            }
            
            Log::info('Product deleted', ['product_id' => $product->id]);
        }
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        if ($this->cacheService) {
            $this->cacheService->forgetByTags(['products', 'api_responses']);
            Log::info('Product restored', ['product_id' => $product->id]);
        }
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        if ($this->cacheService) {
            $this->cacheService->forgetByTags(['products', 'api_responses']);
            
            // Clear specific product cache
            if ($product->slug) {
                $this->cacheService->forget("product_{$product->slug}");
            }
            
            Log::info('Product force deleted', ['product_id' => $product->id]);
        }
    }
}
