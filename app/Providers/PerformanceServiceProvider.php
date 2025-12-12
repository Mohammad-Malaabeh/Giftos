<?php

namespace App\Providers;

use App\Http\Middleware\PerformanceMonitoring;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Only register the middleware in non-console environments
        if (!$this->app->runningInConsole()) {
            // Register the performance monitoring middleware
            $this->app['router']->aliasMiddleware('performance', PerformanceMonitoring::class);

            // Register query listener if query logging is enabled
            if (config('performance.monitoring.database.enabled')) {
                $this->registerQueryListener();
            }
        }
    }

    protected function registerQueryListener()
    {
        $slowQueryThreshold = config('performance.monitoring.database.slow_query_threshold');
        $logAllQueries = config('performance.monitoring.database.log_all_queries');
        $logSlowQueries = config('performance.monitoring.database.log_slow_queries');

        if ($logAllQueries || $logSlowQueries) {
            DB::listen(function ($query) use ($slowQueryThreshold, $logAllQueries, $logSlowQueries) {
                $isSlow = $query->time > $slowQueryThreshold;
                
                if (($logAllQueries && !$isSlow) || ($logSlowQueries && $isSlow)) {
                    $logData = [
                        'query' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time . 'ms',
                        'connection' => $query->connectionName,
                        'is_slow' => $isSlow,
                    ];

                    if ($isSlow) {
                        Log::warning('Slow query detected', $logData);
                    } else if ($logAllQueries) {
                        Log::debug('Query executed', $logData);
                    }
                }
            });
        }
    }
}
