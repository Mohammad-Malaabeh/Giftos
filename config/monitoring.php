<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the monitoring system used throughout
    | the application. You can configure metrics collection, health checks, and
    | alerting settings.
    |
    */

    'enabled' => env('MONITORING_ENABLED', true),

    'default_retention' => [
        'metrics' => env('METRICS_RETENTION_DAYS', 7), // days
        'health_checks' => env('HEALTH_CHECKS_RETENTION_DAYS', 30), // days
        'alerts' => env('ALERTS_RETENTION_DAYS', 90), // days
    ],

    'metrics' => [
        'collection_interval' => env('METRICS_COLLECTION_INTERVAL', 60), // seconds
        'batch_size' => env('METRICS_BATCH_SIZE', 100),
        'compression' => env('METRICS_COMPRESSION', true),
        
        'types' => [
            'system' => [
                'cpu_usage',
                'memory_usage',
                'disk_usage',
                'network_io',
                'load_average',
            ],
            'application' => [
                'request_count',
                'response_time',
                'error_rate',
                'throughput',
                'active_sessions',
            ],
            'business' => [
                'orders_count',
                'revenue',
                'user_registrations',
                'cart_additions',
                'conversions',
            ],
            'database' => [
                'query_count',
                'slow_queries',
                'connection_count',
                'query_time',
                'deadlocks',
            ],
            'cache' => [
                'hit_rate',
                'miss_rate',
                'eviction_rate',
                'memory_usage',
                'key_count',
            ],
        ],
    ],

    'health_checks' => [
        'interval' => env('HEALTH_CHECK_INTERVAL', 300), // seconds
        'timeout' => env('HEALTH_CHECK_TIMEOUT', 10), // seconds
        
        'checks' => [
            'database' => [
                'enabled' => true,
                'timeout' => 5,
                'max_connections' => 10,
            ],
            'cache' => [
                'enabled' => true,
                'timeout' => 2,
                'test_key' => 'health_check',
            ],
            'redis' => [
                'enabled' => true,
                'timeout' => 2,
                'test_key' => 'health_check',
            ],
            'queue' => [
                'enabled' => true,
                'timeout' => 5,
                'max_failed_jobs' => 10,
            ],
            'storage' => [
                'enabled' => true,
                'timeout' => 3,
                'min_free_space' => 100, // MB
            ],
            'external_services' => [
                'enabled' => true,
                'timeout' => 10,
                'services' => [
                    'payment_gateway' => env('PAYMENT_GATEWAY_URL'),
                    'email_service' => env('EMAIL_SERVICE_URL'),
                ],
            ],
        ],
    ],

    'performance' => [
        'slow_request_threshold' => env('SLOW_REQUEST_THRESHOLD', 2000), // milliseconds
        'memory_threshold' => env('MEMORY_THRESHOLD', 128), // MB
        'cpu_threshold' => env('CPU_THRESHOLD', 80), // percentage
        
        'profiling' => [
            'enabled' => env('PERFORMANCE_PROFILING', false),
            'sample_rate' => env('PROFILING_SAMPLE_RATE', 0.1), // 10%
            'max_samples' => env('MAX_PROFILE_SAMPLES', 1000),
        ],
    ],

    'alerting' => [
        'enabled' => env('ALERTING_ENABLED', false),
        
        'channels' => [
            'slack' => [
                'enabled' => env('SLACK_ALERTS', false),
                'webhook_url' => env('SLACK_WEBHOOK_URL'),
                'channel' => env('SLACK_ALERT_CHANNEL', '#alerts'),
                'username' => 'Laravel Monitor',
                'icon_emoji' => ':warning:',
            ],
            'email' => [
                'enabled' => env('EMAIL_ALERTS', false),
                'to' => explode(',', env('ALERT_EMAILS', '')),
                'from' => env('ALERT_EMAIL_FROM', 'monitoring@example.com'),
            ],
            'webhook' => [
                'enabled' => env('WEBHOOK_ALERTS', false),
                'url' => env('WEBHOOK_ALERT_URL'),
                'secret' => env('WEBHOOK_ALERT_SECRET'),
            ],
        ],
        
        'rules' => [
            'high_error_rate' => [
                'enabled' => true,
                'threshold' => 5, // percentage
                'window' => 300, // seconds
                'severity' => 'warning',
            ],
            'high_response_time' => [
                'enabled' => true,
                'threshold' => 5000, // milliseconds
                'window' => 300, // seconds
                'severity' => 'warning',
            ],
            'low_disk_space' => [
                'enabled' => true,
                'threshold' => 10, // percentage
                'severity' => 'critical',
            ],
            'high_memory_usage' => [
                'enabled' => true,
                'threshold' => 90, // percentage
                'severity' => 'warning',
            ],
            'database_connection_issues' => [
                'enabled' => true,
                'threshold' => 5, // failed connections
                'window' => 60, // seconds
                'severity' => 'critical',
            ],
            'queue_processing_issues' => [
                'enabled' => true,
                'threshold' => 100, // failed jobs
                'window' => 300, // seconds
                'severity' => 'warning',
            ],
        ],
        
        'rate_limiting' => [
            'max_alerts_per_hour' => env('MAX_ALERTS_PER_HOUR', 10),
            'cooldown_minutes' => env('ALERT_COOLDOWN_MINUTES', 5),
        ],
    ],

    'dashboard' => [
        'enabled' => env('MONITORING_DASHBOARD', true),
        'refresh_interval' => env('DASHBOARD_REFRESH_INTERVAL', 30), // seconds
        'time_ranges' => [
            '1h' => 'Last Hour',
            '6h' => 'Last 6 Hours',
            '24h' => 'Last 24 Hours',
            '7d' => 'Last 7 Days',
            '30d' => 'Last 30 Days',
        ],
    ],

    'storage' => [
        'driver' => env('MONITORING_STORAGE_DRIVER', 'redis'),
        'connection' => env('MONITORING_REDIS_CONNECTION', 'default'),
        
        'redis' => [
            'prefix' => env('MONITORING_REDIS_PREFIX', 'monitoring:'),
            'ttl' => env('MONITORING_REDIS_TTL', 604800), // 7 days
        ],
        
        'database' => [
            'table' => 'monitoring_metrics',
            'connection' => env('MONITORING_DB_CONNECTION', 'default'),
        ],
    ],

    'cleanup' => [
        'enabled' => true,
        'schedule' => '0 2 * * *', // Daily at 2 AM
        'retention_days' => [
            'metrics' => env('METRICS_RETENTION_DAYS', 7),
            'health_checks' => env('HEALTH_CHECKS_RETENTION_DAYS', 30),
            'alerts' => env('ALERTS_RETENTION_DAYS', 90),
        ],
    ],

    'security' => [
        'authentication_required' => env('MONITORING_AUTH_REQUIRED', true),
        'allowed_ips' => explode(',', env('MONITORING_ALLOWED_IPS', '')),
        'rate_limit' => env('MONITORING_RATE_LIMIT', 60), // requests per minute
        'sensitive_data_masking' => true,
    ],

    'integrations' => [
        'prometheus' => [
            'enabled' => env('PROMETHEUS_ENABLED', false),
            'port' => env('PROMETHEUS_PORT', 9090),
            'metrics_path' => '/metrics',
        ],
        
        'grafana' => [
            'enabled' => env('GRAFANA_ENABLED', false),
            'url' => env('GRAFANA_URL'),
            'api_key' => env('GRAFANA_API_KEY'),
        ],
        
        'datadog' => [
            'enabled' => env('DATADOG_ENABLED', false),
            'api_key' => env('DATADOG_API_KEY'),
            'app_key' => env('DATADOG_APP_KEY'),
        ],
        
        'new_relic' => [
            'enabled' => env('NEW_RELIC_ENABLED', false),
            'app_name' => env('NEW_RELIC_APP_NAME'),
            'license_key' => env('NEW_RELIC_LICENSE_KEY'),
        ],
    ],
];
