<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MonitoringService;
use App\Services\LoggingService;

class MonitoringCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:run {action : The action to perform (health, metrics, cleanup, report)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run monitoring tasks such as health checks, metrics collection, and cleanup';

    /**
     * The monitoring service instance.
     *
     * @var MonitoringService
     */
    protected $monitoring;

    /**
     * The logging service instance.
     *
     * @var LoggingService
     */
    protected $logger;

    /**
     * Create a new command instance.
     *
     * @param MonitoringService $monitoring
     * @param LoggingService $logger
     */
    public function __construct(MonitoringService $monitoring, LoggingService $logger)
    {
        parent::__construct();
        $this->monitoring = $monitoring;
        $this->logger = $logger;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        try {
            switch ($action) {
                case 'health':
                    $this->runHealthChecks();
                    break;
                case 'metrics':
                    $this->collectMetrics();
                    break;
                case 'cleanup':
                    $this->cleanupOldData();
                    break;
                case 'report':
                    $this->generateReport();
                    break;
                default:
                    $this->error("Invalid action: {$action}");
                    $this->info('Available actions: health, metrics, cleanup, report');
                    return 1;
            }

            $this->info("Monitoring task '{$action}' completed successfully");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error running monitoring task '{$action}': {$e->getMessage()}");
            $this->logger->logSystem('monitoring_command_error', [
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 'error');
            return 1;
        }
    }

    /**
     * Run health checks.
     */
    protected function runHealthChecks()
    {
        $this->info('Running health checks...');

        $health = $this->monitoring->getHealthStatus();

        $this->table(
            ['Service', 'Status', 'Response Time', 'Details'],
            array_map(function ($service, $status) {
                return [
                    $service,
                    $status['healthy'] ? '✅ Healthy' : '❌ Unhealthy',
                    $status['response_time'] . 'ms',
                    $status['details'] ?? '',
                ];
            }, array_keys($health), $health)
        );

        $overallHealthy = collect($health)->every(fn($status) => $status['healthy']);

        if ($overallHealthy) {
            $this->info('✅ All systems healthy');
        } else {
            $this->warn('⚠️  Some systems are unhealthy');
        }

        $this->logger->logSystem('health_check_completed', [
            'overall_healthy' => $overallHealthy,
            'services' => $health,
        ]);
    }

    /**
     * Collect metrics.
     */
    protected function collectMetrics()
    {
        $this->info('Collecting system metrics...');

        $metrics = $this->monitoring->collectSystemMetrics();

        $this->table(
            ['Metric', 'Value', 'Unit'],
            [
                ['CPU Usage', $metrics['cpu']['percent'], '%'],
                ['Memory Usage', $metrics['memory']['percent'], '%'],
                ['Memory Used', $metrics['memory']['used'], 'MB'],
                ['Memory Total', $metrics['memory']['total'], 'MB'],
                ['Disk Usage', $metrics['disk']['percent'], '%'],
                ['Disk Used', $metrics['disk']['used'], 'GB'],
                ['Disk Total', $metrics['disk']['total'], 'GB'],
                ['Load Average (1m)', $metrics['load'][0] ?? 'N/A', ''],
                ['Load Average (5m)', $metrics['load'][1] ?? 'N/A', ''],
                ['Load Average (15m)', $metrics['load'][2] ?? 'N/A', ''],
            ]
        );

        $this->monitoring->recordMetric('system_metrics_collected', 1, [
            'cpu_percent' => $metrics['cpu']['percent'],
            'memory_percent' => $metrics['memory']['percent'],
            'disk_percent' => $metrics['disk']['percent'],
        ]);

        $this->logger->logSystem('metrics_collected', $metrics);
    }

    /**
     * Clean up old monitoring data.
     */
    protected function cleanupOldData()
    {
        $this->info('Cleaning up old monitoring data...');

        $retentionDays = config('monitoring.default_retention');
        $cutoffDate = now()->subDays($retentionDays['metrics']);

        $this->info("Removing data older than {$cutoffDate->toDateString()}...");

        // Clean up old metrics
        $deletedMetrics = $this->monitoring->cleanupOldMetrics($cutoffDate);
        $this->info("Deleted {$deletedMetrics} metric records");

        // Clean up old health checks
        $deletedHealthChecks = $this->monitoring->cleanupOldHealthChecks($cutoffDate);
        $this->info("Deleted {$deletedHealthChecks} health check records");

        // Clean up old alerts
        $deletedAlerts = $this->monitoring->cleanupOldAlerts($cutoffDate);
        $this->info("Deleted {$deletedAlerts} alert records");

        $this->logger->logSystem('cleanup_completed', [
            'deleted_metrics' => $deletedMetrics,
            'deleted_health_checks' => $deletedHealthChecks,
            'deleted_alerts' => $deletedAlerts,
            'cutoff_date' => $cutoffDate,
        ]);
    }

    /**
     * Generate monitoring report.
     */
    protected function generateReport()
    {
        $this->info('Generating monitoring report...');

        $report = [
            'timestamp' => now()->toISOString(),
            'health' => $this->monitoring->getHealthStatus(),
            'metrics' => $this->monitoring->getMetricsSummary(),
            'performance' => $this->monitoring->getPerformanceMetrics(),
            'business' => $this->monitoring->getBusinessMetrics(),
        ];

        // Display report summary
        $this->info('=== Monitoring Report ===');
        $this->info('Generated at: ' . $report['timestamp']);
        $this->newLine();

        // Health Summary
        $this->info('🏥 Health Status:');
        $healthSummary = collect($report['health'])->map(function ($status, $service) {
            return [$service, $status['healthy'] ? '✅' : '❌'];
        });
        $this->table(['Service', 'Status'], $healthSummary->toArray());
        $this->newLine();

        // Performance Summary
        $this->info('⚡ Performance Metrics:');
        $perf = $report['performance'];
        $this->table(
            ['Metric', 'Value'],
            [
                ['Average Response Time', ($perf['avg_response_time'] ?? 0) . 'ms'],
                ['Requests per Minute', $perf['requests_per_minute'] ?? 0],
                ['Error Rate', ($perf['error_rate'] ?? 0) . '%'],
                ['Memory Usage', ($perf['memory_usage'] ?? 0) . '%'],
                ['CPU Usage', ($perf['cpu_usage'] ?? 0) . '%'],
            ]
        );
        $this->newLine();

        // Business Summary
        $this->info('💰 Business Metrics (Last 24h):');
        $biz = $report['business'];
        $this->table(
            ['Metric', 'Value'],
            [
                ['Orders', $biz['orders_count'] ?? 0],
                ['Revenue', '$' . number_format($biz['revenue'] ?? 0, 2)],
                ['New Users', $biz['user_registrations'] ?? 0],
                ['Cart Additions', $biz['cart_additions'] ?? 0],
                ['Conversion Rate', ($biz['conversion_rate'] ?? 0) . '%'],
            ]
        );

        // Save report to file
        $reportPath = storage_path("logs/monitoring-report-" . now()->format('Y-m-d-H-i-s') . ".json");
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));
        $this->info("📄 Full report saved to: {$reportPath}");

        $this->logger->logSystem('report_generated', [
            'report_path' => $reportPath,
            'health_healthy' => collect($report['health'])->every(fn($s) => $s['healthy']),
            'performance_summary' => $report['performance'],
            'business_summary' => $report['business'],
        ]);
    }
}
