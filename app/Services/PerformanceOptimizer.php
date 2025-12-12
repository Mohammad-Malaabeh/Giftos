<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class PerformanceOptimizer
{
    /**
     * Cache query results with automatic invalidation.
     */
    public function cacheQuery(string $key, callable $callback, int $ttl = 3600): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Cache database query results with tags for easy invalidation.
     */
    public function cacheQueryWithTags(string $key, array $tags, callable $callback, int $ttl = 3600): mixed
    {
        if (config('cache.default') === 'redis') {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Invalidate cache by tags.
     */
    public function invalidateCacheByTags(array $tags): void
    {
        if (config('cache.default') === 'redis') {
            Cache::tags($tags)->flush();
        }
    }

    /**
     * Get query performance metrics.
     */
    public function getQueryMetrics(): array
    {
        if (config('cache.default') !== 'redis') {
            return [];
        }

        $redis = Redis::connection();
        $info = $redis->info('memory');

        return [
            'memory_usage' => $this->formatBytes($info['used_memory']),
            'memory_peak' => $this->formatBytes($info['used_memory_peak']),
            'cache_hit_rate' => $this->calculateCacheHitRate($redis),
        ];
    }

    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Calculate cache hit rate.
     */
    protected function calculateCacheHitRate($redis): float
    {
        $stats = $redis->info('stats');
        
        if (!isset($stats['keyspace_hits']) || !isset($stats['keyspace_misses'])) {
            return 0.0;
        }

        $hits = (int) $stats['keyspace_hits'];
        $misses = (int) $stats['keyspace_misses'];
        $total = $hits + $misses;

        return $total > 0 ? round(($hits / $total) * 100, 2) : 0.0;
    }

    /**
     * Optimize database queries by adding appropriate indexes.
     */
    public function suggestIndexes(): array
    {
        // This would typically analyze slow query logs
        return [
            'products' => [
                'title' => 'For search functionality',
                'category_id' => 'For category filtering',
                'price' => 'For price-based sorting',
                'status' => 'For active product filtering',
                'created_at' => 'For date-based sorting',
            ],
            'orders' => [
                'user_id' => 'For user order history',
                'status' => 'For order status filtering',
                'created_at' => 'For date-based sorting',
                'total' => 'For revenue calculations',
            ],
            'users' => [
                'email' => 'For authentication',
                'created_at' => 'For user analytics',
            ],
        ];
    }

    /**
     * Get performance recommendations.
     */
    public function getPerformanceRecommendations(): array
    {
        $recommendations = [];

        // Check cache driver
        if (config('cache.default') === 'file') {
            $recommendations[] = 'Consider using Redis for better cache performance';
        }

        // Check session driver
        if (config('session.driver') === 'file') {
            $recommendations[] = 'Consider using database or Redis for session storage';
        }

        // Check queue driver
        if (config('queue.default') === 'sync') {
            $recommendations[] = 'Configure Redis queue driver for better performance';
        }

        // Check debug mode
        if (config('app.debug')) {
            $recommendations[] = 'Disable debug mode in production';
        }

        return $recommendations;
    }
}
