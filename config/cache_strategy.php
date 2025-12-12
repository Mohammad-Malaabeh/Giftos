<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Strategy Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the caching strategy used throughout
    | the application. You can configure cache TTLs, tags, and other settings.
    |
    */

    'default_ttl' => env('CACHE_DEFAULT_TTL', 3600), // 1 hour

    'prefix' => env('CACHE_PREFIX', 'giftos_'),

    'tags' => [
        'products' => 'products',
        'categories' => 'categories',
        'users' => 'users',
        'orders' => 'orders',
        'reviews' => 'reviews',
        'api_responses' => 'api_responses',
    ],

    'ttl' => [
        // Products
        'products_index' => 900, // 15 minutes
        'products_show' => 3600, // 1 hour
        'products_featured' => 1800, // 30 minutes
        'products_latest' => 900, // 15 minutes
        'products_on_sale' => 1800, // 30 minutes
        'products_search' => 900, // 15 minutes
        'products_by_category' => 1800, // 30 minutes
        'products_by_price_range' => 1800, // 30 minutes
        'products_in_stock' => 900, // 15 minutes
        'products_out_of_stock' => 900, // 15 minutes

        // Categories
        'categories_index' => 7200, // 2 hours
        'categories_show' => 3600, // 1 hour
        'categories_with_products' => 1800, // 30 minutes

        // Users
        'user_profile' => 1800, // 30 minutes
        'user_stats' => 900, // 15 minutes

        // Orders
        'order_stats' => 900, // 15 minutes
        'order_history' => 1800, // 30 minutes

        // Reviews
        'reviews_by_product' => 1800, // 30 minutes
        'review_stats' => 3600, // 1 hour

        // API Responses
        'api_products_index' => 900, // 15 minutes
        'api_products_show' => 3600, // 1 hour
        'api_categories_index' => 7200, // 2 hours
        'api_categories_show' => 3600, // 1 hour
    ],

    'warm_up' => [
        'enabled' => env('CACHE_WARM_UP', true),
        'on_boot' => env('CACHE_WARM_UP_ON_BOOT', false),
        'schedule' => '0 */6 * * *', // Every 6 hours
    ],

    'monitoring' => [
        'enabled' => env('CACHE_MONITORING', true),
        'log_slow_queries' => env('CACHE_LOG_SLOW_QUERIES', true),
        'slow_query_threshold' => env('CACHE_SLOW_QUERY_THRESHOLD', 100), // milliseconds
    ],

    'redis' => [
        'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
        'prefix' => env('REDIS_CACHE_PREFIX', 'giftos_cache_'),
    ],

    'strategies' => [
        'read_through' => [
            'enabled' => true,
            'repositories' => [
                'product' => 'cached',
                'category' => 'cached',
                'user' => 'standard',
                'order' => 'standard',
            ],
        ],
        
        'write_through' => [
            'enabled' => true,
            'invalidate_on_update' => true,
            'delayed_invalidation' => false,
        ],

        'cache_aside' => [
            'enabled' => true,
            'automatic_invalidation' => true,
        ],
    ],

    'compression' => [
        'enabled' => env('CACHE_COMPRESSION', false),
        'threshold' => env('CACHE_COMPRESSION_THRESHOLD', 1024), // bytes
    ],

    'serialization' => [
        'method' => env('CACHE_SERIALIZATION', 'json'), // json, serialize, igbinary
    ],

    'debugging' => [
        'enabled' => env('CACHE_DEBUG', false),
        'log_cache_hits' => false,
        'log_cache_misses' => false,
        'log_cache_writes' => false,
    ],
];
