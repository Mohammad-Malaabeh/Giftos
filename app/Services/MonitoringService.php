<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class MonitoringService
{
    protected LoggingService $loggingService;

    public function __construct(LoggingService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    /**
     * Get application health metrics
     */
    public function getHealthMetrics(): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'uptime' => $this->getUptime(),
            'memory_usage' => $this->getMemoryUsage(),
            'cpu_usage' => $this->getCpuUsage(),
            'database' => $this->getDatabaseMetrics(),
            'cache' => $this->getCacheMetrics(),
            'redis' => $this->getRedisMetrics(),
            'disk' => $this->getDiskUsage(),
            'active_sessions' => $this->getActiveSessions(),
            'queue_jobs' => $this->getQueueMetrics(),
        ];
    }

    /**
     * Get application performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'response_times' => $this->getResponseTimeMetrics(),
            'error_rates' => $this->getErrorRates(),
            'throughput' => $this->getThroughputMetrics(),
            'database_performance' => $this->getDatabasePerformance(),
            'cache_performance' => $this->getCachePerformance(),
            'slow_queries' => $this->getSlowQueries(),
        ];
    }

    /**
     * Get business metrics
     */
    public function getBusinessMetrics(): array
    {
        return [
            'users' => $this->getUserMetrics(),
            'orders' => $this->getOrderMetrics(),
            'products' => $this->getProductMetrics(),
            'revenue' => $this->getRevenueMetrics(),
            'conversion_rates' => $this->getConversionRates(),
        ];
    }

    /**
     * Record a metric
     */
    public function recordMetric(string $name, float $value, array $tags = []): void
    {
        $key = "metrics:{$name}:" . now()->format('Y-m-d-H-i');
        
        $data = [
            'value' => $value,
            'timestamp' => now()->timestamp,
            'tags' => $tags,
        ];

        Cache::put($key, $data, 3600); // Store for 1 hour
        
        // Also store in Redis for time-series analysis
        if (!app()->environment('testing') && class_exists('Redis')) {
            try {
                $redis = Redis::connection('default');
                $redis->zadd("metrics:{$name}:timeseries", now()->timestamp, json_encode($data));
                $redis->expire("metrics:{$name}:timeseries", 86400); // Keep for 24 hours
            } catch (\Exception $e) {
                Log::warning('Failed to store metric in Redis', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Get metrics for a specific time range
     */
    public function getMetricsInRange(string $name, \DateTime $start, \DateTime $end): array
    {
        if (app()->environment('testing') || !class_exists('Redis')) {
            return [];
        }
        
        try {
            $redis = Redis::connection('default');
            $startTimestamp = $start->timestamp;
            $endTimestamp = $end->timestamp;
            
            $results = $redis->zrangebyscore("metrics:{$name}:timeseries", $startTimestamp, $endTimestamp);
            
            return array_map(function($item) {
                return json_decode($item, true);
            }, $results);
        } catch (\Exception $e) {
            Log::warning('Failed to retrieve metrics from Redis', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Check if application is healthy
     */
    public function isHealthy(): bool
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'redis' => $this->checkRedis(),
            'disk' => $this->checkDiskSpace(),
        ];

        return !in_array(false, $checks);
    }

    /**
     * Get system uptime
     */
    protected function getUptime(): string
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return "Load: " . implode(', ', $load);
        }
        
        return 'Unknown';
    }

    /**
     * Get memory usage
     */
    protected function getMemoryUsage(): array
    {
        $memory = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        
        return [
            'current' => $this->formatBytes($memory),
            'peak' => $this->formatBytes($peak),
            'percentage' => round(($memory / $peak) * 100, 2),
        ];
    }

    /**
     * Get CPU usage
     */
    protected function getCpuUsage(): string
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg()[0];
            return round($load * 100, 2) . '%';
        }
        
        return 'Unknown';
    }

    /**
     * Get database metrics
     */
    protected function getDatabaseMetrics(): array
    {
        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();

            if ($driver === 'mysql') {
                $connections = DB::select('SHOW STATUS LIKE "Threads_connected"');
                $queries = DB::select('SHOW STATUS LIKE "Questions"');
                $uptime = DB::select('SHOW STATUS LIKE "Uptime"');

                return [
                    'connections' => $connections[0]->Value ?? 0,
                    'queries' => $queries[0]->Value ?? 0,
                    'uptime' => $uptime[0]->Value ?? 0,
                    'status' => 'connected',
                ];
            }

            // SQLite or other drivers: provide safe, limited metrics
            if ($driver === 'sqlite') {
                // SQLite does not expose server status; return lightweight info
                return [
                    'connections' => 1,
                    'queries' => 0,
                    'uptime' => 0,
                    'status' => 'connected',
                    'note' => 'Detailed DB metrics unavailable for sqlite',
                ];
            }

            // Generic fallback
            return [
                'status' => 'unknown',
                'note' => 'Database driver does not support detailed metrics',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get cache metrics
     */
    protected function getCacheMetrics(): array
    {
        try {
            $cacheService = app(CacheService::class);
            $stats = $cacheService->getStats();
            
            return [
                'status' => 'connected',
                'keys' => $stats['keys_count'],
                'memory_usage' => $stats['memory_usage'],
                'hit_rate' => $stats['hit_rate'],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get Redis metrics
     */
    protected function getRedisMetrics(): array
    {
        if (app()->environment('testing') || !class_exists('Redis')) {
            return ['status' => 'disabled', 'message' => 'Redis metrics disabled'];
        }
        
        try {
            $redis = Redis::connection('default');
            $info = $redis->info();
            
            return [
                'status' => 'connected',
                'used_memory' => $info['used_memory_human'] ?? 'N/A',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'total_commands_processed' => $info['total_commands_processed'] ?? 0,
                'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                'keyspace_misses' => $info['keyspace_misses'] ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get disk usage
     */
    protected function getDiskUsage(): array
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;
        
        return [
            'total' => $this->formatBytes($total),
            'free' => $this->formatBytes($free),
            'used' => $this->formatBytes($used),
            'percentage' => round(($used / $total) * 100, 2),
        ];
    }

    /**
     * Get active sessions
     */
    protected function getActiveSessions(): int
    {
        try {
            return DB::table('sessions')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get queue metrics
     */
    protected function getQueueMetrics(): array
    {
        try {
            $failed = DB::table('failed_jobs')->count();
            $pending = DB::table('jobs')->count();
            
            return [
                'failed' => $failed,
                'pending' => $pending,
                'status' => 'active',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check database connection
     */
    protected function checkDatabase(): bool
    {
        try {
            DB::select('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check cache connection
     */
    protected function checkCache(): bool
    {
        try {
            Cache::put('health_check', 'ok', 1);
            return Cache::get('health_check') === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check Redis connection
     */
    protected function checkRedis(): bool
    {
        if (app()->environment('testing') || !class_exists('Redis')) {
            return true; // Skip Redis check in testing or if Redis is not available
        }
        
        try {
            $redis = Redis::connection('default');
            $redis->ping();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check disk space
     */
    protected function checkDiskSpace(): bool
    {
        $free = disk_free_space('/');
        $total = disk_total_space('/');
        
        return ($free / $total) > 0.1; // At least 10% free
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    // Additional metric methods (simplified for brevity)
    protected function getResponseTimeMetrics(): array { return []; }
    protected function getErrorRates(): array { return []; }
    protected function getThroughputMetrics(): array { return []; }
    protected function getDatabasePerformance(): array { return []; }
    protected function getCachePerformance(): array { return []; }
    protected function getSlowQueries(): array { return []; }
    protected function getUserMetrics(): array { return []; }
    protected function getOrderMetrics(): array { return []; }
    protected function getProductMetrics(): array { return []; }
    protected function getRevenueMetrics(): array { return []; }
    protected function getConversionRates(): array { return []; }
}
