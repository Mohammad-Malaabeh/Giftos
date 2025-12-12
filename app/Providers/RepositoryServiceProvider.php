<?php

namespace App\Providers;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\ProductRepository;
use App\Repositories\OrderRepository;
use App\Repositories\CachedProductRepository;
use App\Services\CacheService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Don't register CacheService in testing environment
        if (!$this->app->environment('testing')) {
            $this->app->singleton(CacheService::class, function ($app) {
                return new CacheService();
            });
        }

        // Use cached repositories in production
        $this->app->bind(ProductRepositoryInterface::class, function ($app) {
            if ($app->environment('production')) {
                return $app->make(CachedProductRepository::class);
            }

            return $app->make(ProductRepository::class);
        });
        
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
