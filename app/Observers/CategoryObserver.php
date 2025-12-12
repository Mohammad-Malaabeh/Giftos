<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class CategoryObserver
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
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        if ($this->cacheService) {
            $this->cacheService->forgetByTags(['categories', 'api_responses']);
            Log::info('Category created', ['category_id' => $category->id]);
        }
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        if ($this->cacheService) {
            $this->cacheService->forgetByTags(['categories', 'api_responses']);
            
            // Clear specific category cache
            if ($category->slug) {
                $this->cacheService->forget("category_{$category->slug}");
            }
            
            Log::info('Category updated', ['category_id' => $category->id]);
        }
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        if ($this->cacheService) {
            $this->cacheService->forgetByTags(['categories', 'api_responses']);
            
            // Clear specific category cache
            if ($category->slug) {
                $this->cacheService->forget("category_{$category->slug}");
            }
            
            Log::info('Category deleted', ['category_id' => $category->id]);
        }
    }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void
    {
        if ($this->cacheService) {
            $this->cacheService->forgetByTags(['categories', 'api_responses']);
            Log::info('Category restored', ['category_id' => $category->id]);
        }
    }

    /**
     * Handle the Category "force deleted" event.
     */
    public function forceDeleted(Category $category): void
    {
        if ($this->cacheService) {
            $this->cacheService->forgetByTags(['categories', 'api_responses']);
            
            // Clear specific category cache
            if ($category->slug) {
                $this->cacheService->forget("category_{$category->slug}");
            }
            
            Log::info('Category force deleted', ['category_id' => $category->id]);
        }
    }
}
