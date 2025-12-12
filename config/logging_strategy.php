<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Logging Strategy Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the logging strategy used throughout
    | the application. You can configure log levels, channels, and other settings.
    |
    */

    'default_level' => env('LOG_DEFAULT_LEVEL', 'info'),

    'channels' => [
        'activity' => [
            'driver' => 'daily',
            'path' => storage_path('logs/activity.log'),
            'level' => env('LOG_ACTIVITY_LEVEL', 'info'),
            'days' => 30,
            'replace_placeholders' => true,
        ],

        'api' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api.log'),
            'level' => env('LOG_API_LEVEL', 'info'),
            'days' => 14,
            'replace_placeholders' => true,
        ],

        'database' => [
            'driver' => 'daily',
            'path' => storage_path('logs/database.log'),
            'level' => env('LOG_DATABASE_LEVEL', 'warning'),
            'days' => 7,
            'replace_placeholders' => true,
        ],

        'security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => env('LOG_SECURITY_LEVEL', 'warning'),
            'days' => 90,
            'replace_placeholders' => true,
        ],

        'errors' => [
            'driver' => 'daily',
            'path' => storage_path('logs/errors.log'),
            'level' => env('LOG_ERRORS_LEVEL', 'error'),
            'days' => 30,
            'replace_placeholders' => true,
        ],

        'performance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/performance.log'),
            'level' => env('LOG_PERFORMANCE_LEVEL', 'info'),
            'days' => 7,
            'replace_placeholders' => true,
        ],

        'cache' => [
            'driver' => 'daily',
            'path' => storage_path('logs/cache.log'),
            'level' => env('LOG_CACHE_LEVEL', 'debug'),
            'days' => 3,
            'replace_placeholders' => true,
        ],

        'business' => [
            'driver' => 'daily',
            'path' => storage_path('logs/business.log'),
            'level' => env('LOG_BUSINESS_LEVEL', 'info'),
            'days' => 90,
            'replace_placeholders' => true,
        ],

        'system' => [
            'driver' => 'daily',
            'path' => storage_path('logs/system.log'),
            'level' => env('LOG_SYSTEM_LEVEL', 'info'),
            'days' => 14,
            'replace_placeholders' => true,
        ],
    ],

    'sensitive_data' => [
        // Fields to mask in logs
        'mask' => [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'secret',
            'credit_card',
            'ssn',
            'bank_account',
        ],

        // Fields to completely remove from logs
        'remove' => [
            'file_content',
            'binary_data',
        ],
    ],

    'request_logging' => [
        'enabled' => env('LOG_REQUESTS', true),
        'exclude_paths' => [
            'health',
            'metrics',
            'ping',
            '_debugbar',
            'telescope',
        ],
        'max_request_size' => env('LOG_MAX_REQUEST_SIZE', 10240), // 10KB
        'include_response_body' => env('LOG_INCLUDE_RESPONSE', false),
        'max_response_size' => env('LOG_MAX_RESPONSE_SIZE', 1024), // 1KB
    ],

    'query_logging' => [
        'enabled' => env('LOG_QUERIES', false),
        'slow_query_threshold' => env('LOG_SLOW_QUERY_THRESHOLD', 100), // milliseconds
        'exclude_patterns' => [
            'SELECT 1',
            'SHOW TABLES',
            'DESCRIBE',
        ],
    ],

    'performance_monitoring' => [
        'enabled' => env('PERFORMANCE_MONITORING', true),
        'slow_request_threshold' => env('SLOW_REQUEST_THRESHOLD', 2000), // milliseconds
        'memory_threshold' => env('MEMORY_THRESHOLD', 128), // MB
        'cpu_threshold' => env('CPU_THRESHOLD', 80), // percentage
    ],

    'alerting' => [
        'enabled' => env('LOG_ALERTING', false),
        'channels' => ['slack', 'email'],
        'levels' => ['critical', 'emergency'],
        'rate_limit' => [
            'max_alerts_per_hour' => 10,
            'cooldown_minutes' => 5,
        ],
    ],

    'retention' => [
        'default_days' => 30,
        'error_days' => 90,
        'audit_days' => 365,
        'debug_days' => 7,
    ],

    'formatting' => [
        'json_logs' => env('LOG_JSON', false),
        'include_context' => true,
        'include_stack_trace' => true,
        'date_format' => 'Y-m-d H:i:s',
    ],

    'integration' => [
        'elasticsearch' => [
            'enabled' => env('ELASTICSEARCH_ENABLED', false),
            'host' => env('ELASTICSEARCH_HOST', 'localhost'),
            'port' => env('ELASTICSEARCH_PORT', 9200),
            'index' => env('ELASTICSEARCH_INDEX', 'laravel_logs'),
        ],

        'splunk' => [
            'enabled' => env('SPLUNK_ENABLED', false),
            'host' => env('SPLUNK_HOST'),
            'token' => env('SPLUNK_TOKEN'),
            'index' => env('SPLUNK_INDEX'),
        ],

        'papertrail' => [
            'enabled' => env('PAPERTRAIL_ENABLED', false),
            'host' => env('PAPERTRAIL_HOST'),
            'port' => env('PAPERTRAIL_PORT'),
        ],
    ],
];
