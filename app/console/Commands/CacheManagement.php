<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheManagement extends Command
{
    protected $signature = 'cache:manage {action : The action to perform (clear, warm, stats, info)} 
                            {--tag= : Clear cache by tag}
                            {--key= : Clear specific cache key}
                            {--force : Force action without confirmation}';

    protected $description = 'Manage application cache';

    protected ?CacheService $cacheService;

    public function __construct(CacheService $cacheService = null)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'clear' => $this->clearCache(),
            'warm' => $this->warmCache(),
            'stats' => $this->showStats(),
            'info' => $this->showInfo(),
            default => $this->error("Unknown action: {$action}")
        };
    }

    protected function clearCache(): int
    {
        if (!$this->cacheService) {
            $this->error('Cache service is not available in testing environment');
            return 0;
        }

        if ($this->option('tag')) {
            $tag = $this->option('tag');
            
            if (!$this->option('force') && !$this->confirm("Clear all cache with tag '{$tag}'?")) {
                return 0;
            }

            $this->cacheService->forgetByTags([$tag]);
            $this->info("Cache cleared for tag: {$tag}");
            
        } elseif ($this->option('key')) {
            $key = $this->option('key');
            
            if (!$this->option('force') && !$this->confirm("Clear cache key '{$key}'?")) {
                return 0;
            }

            $this->cacheService->forget($key);
            $this->info("Cache cleared for key: {$key}");
            
        } else {
            if (!$this->option('force') && !$this->confirm('Clear ALL application cache?')) {
                return 0;
            }

            $this->cacheService->clear();
            $this->info('All cache cleared');
        }

        return 0;
    }

    protected function warmCache(): int
    {
        if (!$this->cacheService) {
            $this->error('Cache service is not available in testing environment');
            return 0;
        }

        $this->info('Warming up cache...');

        $this->cacheService->warmUp();

        // Warm up additional caches
        $this->warmProductCache();
        $this->warmCategoryCache();

        $this->info('Cache warmed up successfully');
        return 0;
    }

    protected function showStats(): int
    {
        if (!$this->cacheService) {
            $this->error('Cache service is not available in testing environment');
            return 0;
        }

        $stats = $this->cacheService->getStats();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Keys', $stats['keys_count']],
                ['Memory Usage', $stats['memory_usage']],
                ['Hit Rate', $stats['hit_rate'] . '%'],
            ]
        );

        // Show cache status
        $this->info("\nCache Status:");
        $this->showCacheStatus();

        return 0;
    }

    protected function showInfo(): int
    {
        $this->info('Cache Configuration:');
        $this->line('Driver: ' . config('cache.default'));
        $this->line('Prefix: ' . config('cache.prefix'));
        $this->line('Default TTL: ' . config('cache_strategy.default_ttl') . ' seconds');

        $this->info("\nCache Tags:");
        $tags = config('cache_strategy.tags');
        foreach ($tags as $key => $tag) {
            $this->line("  {$key}: {$tag}");
        }

        $this->info("\nCache TTLs:");
        $ttls = config('cache_strategy.ttl');
        foreach ($ttls as $key => $ttl) {
            $this->line("  {$key}: {$ttl} seconds");
        }

        return 0;
    }

    protected function warmProductCache(): void
    {
        if (!$this->cacheService) {
            return;
        }

        $this->line('Warming product cache...');

        // Warm up featured products
        $this->cacheService->remember('products_featured_8', function () {
            return \App\Models\Product::where('featured', true)
                ->where('active', true)
                ->with(['categories'])
                ->withAvg('reviews', 'rating')
                ->limit(8)
                ->get();
        }, 3600);

        // Warm up latest products
        $this->cacheService->remember('products_latest_12', function () {
            return \App\Models\Product::where('active', true)
                ->with(['categories'])
                ->withAvg('reviews', 'rating')
                ->latest()
                ->limit(12)
                ->get();
        }, 1800);

        $this->line('Product cache warmed');
    }

    protected function warmCategoryCache(): void
    {
        if (!$this->cacheService) {
            return;
        }

        $this->line('Warming category cache...');

        $this->cacheService->remember('categories_all', function () {
            return \App\Models\Category::withCount('products')->get();
        }, 7200);

        $this->line('Category cache warmed');
    }

    protected function showCacheStatus(): void
    {
        if (!$this->cacheService) {
            $this->line('  Cache service is not available');
            return;
        }

        $checks = [
            'products_featured_8' => 'Featured Products',
            'products_latest_12' => 'Latest Products',
            'products_on_sale' => 'Products on Sale',
            'categories_all' => 'All Categories',
        ];

        foreach ($checks as $key => $label) {
            $status = $this->cacheService->has($key) ? '✓ Cached' : '✗ Not cached';
            $this->line("  {$label}: {$status}");
        }
    }
}
