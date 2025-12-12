<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\MonitoringService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MonitoringController extends Controller
{
    protected MonitoringService $monitoringService;

    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;

        // Apply middleware
        $this->middleware('auth:sanctum')->except(['health', 'metrics']);
        $this->middleware('can:admin.access')->except(['health', 'metrics']);
    }

    /**
     * Get application health status
     */
    public function health(): JsonResponse
    {
        $isHealthy = $this->monitoringService->isHealthy();
        $metrics = $this->monitoringService->getHealthMetrics();

        return response()->json([
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
            'environment' => config('app.env'),
            'metrics' => $metrics,
        ], $isHealthy ? 200 : 503);
    }

    /**
     * Get application metrics
     */
    public function metrics(Request $request): JsonResponse
    {
        $type = $request->get('type', 'health');

        $metrics = match ($type) {
            'health' => $this->monitoringService->getHealthMetrics(),
            'performance' => $this->monitoringService->getPerformanceMetrics(),
            'business' => $this->monitoringService->getBusinessMetrics(),
            default => response()->json(['error' => 'Invalid metric type'], 400),
        };

        if ($metrics instanceof JsonResponse) {
            return $metrics;
        }

        return response()->json([
            'type' => $type,
            'timestamp' => now()->toISOString(),
            'metrics' => $metrics,
        ]);
    }

    /**
     * Get metrics for a specific time range
     */
    public function metricsHistory(Request $request): JsonResponse
    {
        $request->validate([
            'metric' => 'required|string',
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);

        $metric = $request->get('metric');
        $start = new \DateTime($request->get('start'));
        $end = new \DateTime($request->get('end'));

        $data = $this->monitoringService->getMetricsInRange($metric, $start, $end);

        return response()->json([
            'metric' => $metric,
            'period' => [
                'start' => $start->toISOString(),
                'end' => $end->toISOString(),
            ],
            'data' => $data,
            'count' => count($data),
        ]);
    }

    /**
     * Get system logs (admin only)
     */
    public function logs(Request $request): JsonResponse
    {
        $request->validate([
            'level' => 'in:debug,info,warning,error,critical',
            'limit' => 'integer|min:1|max:1000',
            'search' => 'string|max:255',
        ]);

        $level = $request->get('level', 'info');
        $limit = $request->get('limit', 100);
        $search = $request->get('search');

        // This would typically read from a log file or logging service
        // For now, we'll return a placeholder response
        return response()->json([
            'logs' => [
                [
                    'timestamp' => now()->toISOString(),
                    'level' => 'info',
                    'message' => 'Sample log entry',
                    'context' => [],
                ],
            ],
            'filters' => [
                'level' => $level,
                'limit' => $limit,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Get cache statistics
     */
    public function cacheStats(): JsonResponse
    {
        $cacheService = app(\App\Services\CacheService::class);
        $stats = $cacheService->getStats();

        return response()->json([
            'cache_stats' => $stats,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Clear application cache (admin only)
     */
    public function clearCache(): JsonResponse
    {
        $cacheService = app(\App\Services\CacheService::class);
        $cacheService->clear();

        return response()->json([
            'message' => 'Application cache cleared successfully',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Warm up application cache (admin only)
     */
    public function warmCache(): JsonResponse
    {
        $cacheService = app(\App\Services\CacheService::class);
        $cacheService->warmUp();

        return response()->json([
            'message' => 'Application cache warmed up successfully',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get database statistics
     */
    public function databaseStats(): JsonResponse
    {
        try {
            $connection = \DB::connection();
            $driver = $connection->getDriverName();
            $tableStats = [];

            if ($driver === 'mysql') {
                $tables = \DB::select('SHOW TABLES');
                foreach ($tables as $table) {
                    $tableName = array_values((array) $table)[0];
                    $count = \DB::table($tableName)->count();
                    $size = \DB::select("SELECT 
                        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb' 
                        FROM information_schema.TABLES 
                        WHERE table_schema = DATABASE() AND table_name = ?", [$tableName]);

                    $tableStats[$tableName] = [
                        'rows' => $count,
                        'size_mb' => $size[0]->size_mb ?? 0,
                    ];
                }
            } elseif ($driver === 'sqlite') {
                $tables = \DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                foreach ($tables as $table) {
                    $tableName = $table->name ?? array_values((array) $table)[0];
                    $count = \DB::table($tableName)->count();
                    $tableStats[$tableName] = [
                        'rows' => $count,
                        'size_mb' => 0, // SQLite does not expose table size easily
                    ];
                }
            } else {
                // Unknown driver: try generic approach - enumerate tables via schema
                try {
                    $tables = \Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
                    foreach ($tables as $tableName) {
                        $count = \DB::table($tableName)->count();
                        $tableStats[$tableName] = [
                            'rows' => $count,
                            'size_mb' => 0,
                        ];
                    }
                } catch (\Exception $ex) {
                    return response()->json([
                        'error' => 'Unsupported database driver for statistics',
                        'message' => $ex->getMessage(),
                    ], 500);
                }
            }

            return response()->json([
                'database' => $connection->getDatabaseName(),
                'tables' => $tableStats,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve database statistics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get queue statistics
     */
    public function queueStats(): JsonResponse
    {
        try {
            $failed = \DB::table('failed_jobs')->count();
            $pending = \DB::table('jobs')->count();
            $processed = \DB::table('job_batches')->count();

            return response()->json([
                'queues' => [
                    'failed_jobs' => $failed,
                    'pending_jobs' => $pending,
                    'processed_batches' => $processed,
                ],
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve queue statistics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user statistics
     */
    public function userStats(): JsonResponse
    {
        try {
            $total = \App\Models\User::count();
            $active = \App\Models\User::where('email_verified_at', '!=', null)->count();
            $recent = \App\Models\User::where('created_at', '>=', now()->subDays(30))->count();

            return response()->json([
                'users' => [
                    'total' => $total,
                    'active' => $active,
                    'recent_registrations' => $recent,
                    'verification_rate' => $total > 0 ? round(($active / $total) * 100, 2) : 0,
                ],
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve user statistics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
