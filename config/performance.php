<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Optimization Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration settings for optimizing the performance
    | of the Giftos e-commerce application. These settings help improve response
    | times, reduce server load, and enhance user experience.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching strategies for different parts of the application.
    | These settings help reduce database queries and improve response times.
    |
    */

    'cache' => [
        // Default cache TTL in seconds
        'default_ttl' => env('CACHE_DEFAULT_TTL', 3600),

        // Cache TTLs for different data types
        'ttls' => [
            'products' => [
                'index' => 900,        // 15 minutes
                'show' => 3600,        // 1 hour
                'featured' => 1800,    // 30 minutes
                'search' => 300,       // 5 minutes
            ],
            'categories' => [
                'index' => 7200,       // 2 hours
                'show' => 3600,        // 1 hour
                'tree' => 14400,       // 4 hours
            ],
            'orders' => [
                'stats' => 300,        // 5 minutes
                'analytics' => 600,    // 10 minutes
            ],
            'users' => [
                'profile' => 1800,     // 30 minutes
                'preferences' => 3600, // 1 hour
            ],
        ],

        // Cache tags for easy invalidation
        'tags' => [
            'products' => ['products', 'catalog'],
            'categories' => ['categories', 'catalog'],
            'orders' => ['orders', 'analytics'],
            'users' => ['users', 'profiles'],
        ],

        // Cache warming strategies
        'warming' => [
            'enabled' => env('CACHE_WARMING_ENABLED', true),
            'schedule' => '0 */6 * * *', // Every 6 hours
            'endpoints' => [
                'api/v1/products/featured',
                'api/v1/products/latest',
                'api/v1/categories',
                'api/v1/products/on-sale',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Optimization
    |--------------------------------------------------------------------------
    |
    | Configure database optimization settings to improve query performance.
    |
    */

    'database' => [
        // Query logging configuration
        'query_log' => [
            'enabled' => env('DB_QUERY_LOG', false),
            'slow_query_threshold' => env('DB_SLOW_QUERY_THRESHOLD', 1000), // milliseconds
        ],

        // Connection pooling
        'pooling' => [
            'enabled' => env('DB_POOLING_ENABLED', true),
            'max_connections' => env('DB_MAX_CONNECTIONS', 100),
            'min_connections' => env('DB_MIN_CONNECTIONS', 10),
        ],

        // Read replica configuration
        'read_replicas' => [
            'enabled' => env('DB_READ_REPLICAS_ENABLED', false),
            'hosts' => explode(',', env('DB_READ_REPLICAS', '')),
        ],

        // Index suggestions
        'indexes' => [
            'products' => [
                'status' => 'btree',
                'category_id' => 'btree',
                'price' => 'btree',
                'created_at' => 'btree',
                'title' => 'fulltext',
                'description' => 'fulltext',
            ],
            'orders' => [
                'user_id' => 'btree',
                'status' => 'btree',
                'created_at' => 'btree',
                'total' => 'btree',
            ],
            'users' => [
                'email' => 'unique',
                'created_at' => 'btree',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configure queue settings for optimal background job processing.
    |
    */

    'queue' => [
        // Worker configuration
        'workers' => [
            'default' => [
                'connection' => 'redis',
                'queue' => 'default',
                'sleep' => 3,
                'tries' => 3,
                'max_time' => 3600,
                'memory' => 128,
            ],
            'high_priority' => [
                'connection' => 'redis',
                'queue' => 'high',
                'sleep' => 1,
                'tries' => 3,
                'max_time' => 1800,
                'memory' => 256,
            ],
            'low_priority' => [
                'connection' => 'redis',
                'queue' => 'low',
                'sleep' => 5,
                'tries' => 3,
                'max_time' => 7200,
                'memory' => 64,
            ],
        ],

        // Job batching
        'batching' => [
            'enabled' => true,
            'batch_size' => 100,
            'timeout' => 3600,
        ],

        // Failed job handling
        'failed_jobs' => [
            'retry_after' => 300, // seconds
            'max_attempts' => 3,
            'auto_retry' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CDN and Asset Optimization
    |--------------------------------------------------------------------------
    |
    | Configure CDN and asset optimization settings.
    |
    */

    'assets' => [
        // CDN configuration
        'cdn' => [
            'enabled' => env('CDN_ENABLED', false),
            'url' => env('CDN_URL'),
            'fallback_url' => env('APP_URL'),
        ],

        // Image optimization
        'images' => [
            'auto_optimize' => env('IMAGE_AUTO_OPTIMIZE', true),
            'quality' => env('IMAGE_QUALITY', 85),
            'formats' => ['webp', 'avif', 'jpg'],
            'variants' => [
                'thumbnail' => [150, 150],
                'medium' => [300, 300],
                'large' => [800, 800],
            ],
        ],

        // Asset versioning
        'versioning' => [
            'enabled' => true,
            'strategy' => 'hash', // 'hash' or 'timestamp'
        ],

        // Minification
        'minification' => [
            'css' => env('CSS_MINIFY', true),
            'js' => env('JS_MINIFY', true),
            'html' => env('HTML_MINIFY', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Optimization
    |--------------------------------------------------------------------------
    |
    | Configure API-specific optimization settings.
    |
    */

    'api' => [
        // Response compression
        'compression' => [
            'enabled' => env('API_COMPRESSION_ENABLED', true),
            'level' => env('API_COMPRESSION_LEVEL', 6),
        ],

        // Pagination
        'pagination' => [
            'default_per_page' => 15,
            'max_per_page' => 100,
        ],

        // Rate limiting
        'rate_limiting' => [
            'authenticated' => [
                'requests_per_minute' => 1000,
                'requests_per_hour' => 10000,
            ],
            'unauthenticated' => [
                'requests_per_minute' => 100,
                'requests_per_hour' => 1000,
            ],
        ],

        // Response caching
        'response_cache' => [
            'enabled' => env('API_RESPONSE_CACHE_ENABLED', true),
            'default_ttl' => 900, // 15 minutes
            'exclude_patterns' => [
                '*/auth/*',
                '*/orders/*',
                '*/cart/*',
                '*/wishlist/*',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Metrics
    |--------------------------------------------------------------------------
    |
    | Configure performance monitoring and metrics collection.
    |
    */

    'monitoring' => [
        // Performance metrics
        'metrics' => [
            'enabled' => env('PERFORMANCE_METRICS_ENABLED', true),
            'collection_interval' => 60, // seconds
            'retention_period' => 7, // days
        ],

        // Request monitoring
        'requests' => [
            'enabled' => env('REQUEST_MONITORING_ENABLED', true),
            'slow_threshold' => env('SLOW_REQUEST_THRESHOLD_MS', 1000), // milliseconds
            'log_to_database' => env('LOG_REQUESTS_TO_DB', true),
            'log_slow_requests' => env('LOG_SLOW_REQUESTS', true),
            'log_all_requests' => env('LOG_ALL_REQUESTS', false),
            'exclude_paths' => [
                '_debugbar/*',
                'telescope/*',
                'horizon/*',
                'api/documentation*',
            ],
        ],

        // Database query monitoring
        'database' => [
            'enabled' => env('DB_QUERY_MONITORING_ENABLED', true),
            'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD_MS', 500), // milliseconds
            'log_slow_queries' => env('LOG_SLOW_QUERIES', true),
            'log_all_queries' => env('LOG_ALL_QUERIES', false),
        ],

        // Memory usage monitoring
        'memory' => [
            'enabled' => env('MEMORY_MONITORING_ENABLED', true),
            'alert_threshold' => env('MEMORY_ALERT_THRESHOLD', 512), // MB
        ],

        // Response time monitoring
        'response_time' => [
            'enabled' => env('RESPONSE_TIME_MONITORING_ENABLED', true),
            'alert_threshold' => env('RESPONSE_TIME_ALERT_THRESHOLD', 2000), // milliseconds
        ],

        // Storage for monitoring data
        'storage' => [
            'enabled' => env('MONITORING_STORAGE_ENABLED', true),
            'driver' => env('MONITORING_STORAGE_DRIVER', 'database'), // database, redis, file
            'retention_days' => env('MONITORING_RETENTION_DAYS', 30),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Performance Balance
    |--------------------------------------------------------------------------
    |
    | Configure settings that balance security with performance.
    |
    */

    'security_balance' => [
        // Session security vs performance
        'session' => [
            'encryption' => env('SESSION_ENCRYPTION', false), // Disable for performance
            'secure' => env('SESSION_SECURE', true),
            'http_only' => env('SESSION_HTTP_ONLY', true),
            'same_site' => env('SESSION_SAME_SITE', 'lax'),
        ],

        // CSRF protection
        'csrf' => [
            'enabled' => env('CSRF_PROTECTION_ENABLED', true),
            'token_refresh_interval' => env('CSRF_TOKEN_REFRESH', 3600), // seconds
        ],

        // Rate limiting strictness
        'rate_limiting' => [
            'strict_mode' => env('RATE_LIMITING_STRICT', false),
            'adaptive_threshold' => env('RATE_LIMITING_ADAPTIVE', true),
        ],
    ],
];
