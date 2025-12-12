<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-specific configuration settings for the
    | Giftos e-commerce platform. These settings help protect against common
    | security vulnerabilities and ensure data protection.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Authentication Security
    |--------------------------------------------------------------------------
    |
    | Configure security settings for user authentication and authorization.
    |
    */

    'auth' => [
        // Password requirements
        'password' => [
            'min_length' => env('PASSWORD_MIN_LENGTH', 8),
            'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
            'require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),
            'require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
            'require_symbols' => env('PASSWORD_REQUIRE_SYMBOLS', false),
            'max_age' => env('PASSWORD_MAX_AGE', 90), // days - force password change
        ],

        // Session security
        'session' => [
            'lifetime' => env('SESSION_LIFETIME', 120), // minutes
            'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),
            'secure' => env('SESSION_SECURE', true),
            'http_only' => env('SESSION_HTTP_ONLY', true),
            'same_site' => env('SESSION_SAME_SITE', 'lax'),
            'encryption' => env('SESSION_ENCRYPTION', true),
            'regenerate_id' => true, // Regenerate ID on login
        ],

        // Rate limiting for authentication
        'rate_limiting' => [
            'login' => [
                'max_attempts' => env('AUTH_LOGIN_MAX_ATTEMPTS', 5),
                'decay_minutes' => env('AUTH_LOGIN_DECAY_MINUTES', 15),
                'lockout_duration' => env('AUTH_LOCKOUT_DURATION', 900), // seconds
            ],
            'registration' => [
                'max_attempts' => env('AUTH_REGISTRATION_MAX_ATTEMPTS', 3),
                'decay_minutes' => env('AUTH_REGISTRATION_DECAY_MINUTES', 60),
            ],
            'password_reset' => [
                'max_attempts' => env('AUTH_PASSWORD_RESET_MAX_ATTEMPTS', 3),
                'decay_minutes' => env('AUTH_PASSWORD_RESET_DECAY_MINUTES', 60),
            ],
        ],

        // Two-factor authentication
        '2fa' => [
            'enabled' => env('TWO_FACTOR_AUTH_ENABLED', false),
            'issuer' => env('TWO_FACTOR_ISSUER', 'Giftos'),
            'window' => env('TWO_FACTOR_WINDOW', 1), // Time window for TOTP
            'backup_codes' => env('TWO_FACTOR_BACKUP_CODES', true),
            'backup_codes_count' => env('TWO_FACTOR_BACKUP_CODES_COUNT', 10),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security
    |--------------------------------------------------------------------------
    |
    | Configure security settings for API endpoints.
    |
    */

    'api' => [
        // Sanctum configuration
        'sanctum' => [
            'expiration' => env('SANCTUM_TOKEN_EXPIRATION', 525600), // 1 year in minutes
            'personal_access_tokens' => env('SANCTUM_PERSONAL_ACCESS_TOKENS', true),
            'token_prefix' => env('SANCTUM_TOKEN_PREFIX', 'gft_'),
        ],

        // API rate limiting
        'rate_limiting' => [
            'authenticated' => [
                'requests_per_minute' => env('API_AUTHENTICATED_RPM', 1000),
                'requests_per_hour' => env('API_AUTHENTICATED_RPH', 10000),
                'requests_per_day' => env('API_AUTHENTICATED_RPD', 100000),
            ],
            'unauthenticated' => [
                'requests_per_minute' => env('API_UNAUTHENTICATED_RPM', 100),
                'requests_per_hour' => env('API_UNAUTHENTICATED_RPH', 1000),
                'requests_per_day' => env('API_UNAUTHENTICATED_RPD', 10000),
            ],
            'endpoints' => [
                'auth.login' => ['requests_per_minute' => 20],
                'auth.register' => ['requests_per_minute' => 10],
                'auth.password.reset' => ['requests_per_minute' => 5],
                'orders.create' => ['requests_per_minute' => 30],
                'checkout.process' => ['requests_per_minute' => 10],
            ],
        ],

        // API throttling
        'throttling' => [
            'enabled' => env('API_THROTTLING_ENABLED', true),
            'adaptive' => env('API_ADAPTIVE_THROTTLING', true),
            'burst_limit' => env('API_BURST_LIMIT', 10),
        ],

        // API encryption
        'encryption' => [
            'enabled' => env('API_ENCRYPTION_ENABLED', false),
            'algorithm' => env('API_ENCRYPTION_ALGORITHM', 'AES-256-GCM'),
            'key_rotation_days' => env('API_KEY_ROTATION_DAYS', 90),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Protection
    |--------------------------------------------------------------------------
    |
    | Configure data protection and privacy settings.
    |
    */

    'data_protection' => [
        // Data encryption
        'encryption' => [
            'algorithm' => 'AES-256-CBC',
            'key_rotation_days' => env('ENCRYPTION_KEY_ROTATION_DAYS', 365),
            'encrypt_sensitive_fields' => env('ENCRYPT_SENSITIVE_FIELDS', true),
            'sensitive_fields' => [
                'users' => ['email', 'phone', 'address'],
                'orders' => ['billing_address', 'shipping_address'],
                'payments' => ['card_number', 'cvv', 'expiry'],
            ],
        ],

        // Data retention
        'retention' => [
            'logs' => env('LOG_RETENTION_DAYS', 30),
            'audit_trails' => env('AUDIT_TRAIL_RETENTION_DAYS', 365),
            'user_data' => env('USER_DATA_RETENTION_DAYS', 2555), // 7 years
            'order_data' => env('ORDER_DATA_RETENTION_DAYS', 3650), // 10 years
            'payment_data' => env('PAYMENT_DATA_RETENTION_DAYS', 1825), // 5 years
        ],

        // Data anonymization
        'anonymization' => [
            'enabled' => env('DATA_ANONYMIZATION_ENABLED', true),
            'schedule' => env('DATA_ANONYMIZATION_SCHEDULE', '0 2 * * 0'), // Weekly at 2 AM Sunday
            'fields' => [
                'users' => ['email', 'phone', 'address'],
                'orders' => ['customer_notes'],
            ],
        ],

        // GDPR compliance
        'gdpr' => [
            'enabled' => env('GDPR_COMPLIANCE_ENABLED', true),
            'cookie_consent' => env('GDPR_COOKIE_CONSENT', true),
            'data_portability' => env('GDPR_DATA_PORTABILITY', true),
            'right_to_be_forgotten' => env('GDPR_RIGHT_TO_BE_FORGOTTEN', true),
            'privacy_policy_url' => env('GDPR_PRIVACY_POLICY_URL', '/privacy-policy'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Web Security
    |--------------------------------------------------------------------------
    |
    | Configure web security headers and protections.
    |
    */

    'web' => [
        // Content Security Policy
        'csp' => [
            'enabled' => env('CSP_ENABLED', true),
            'report_only' => env('CSP_REPORT_ONLY', false),
            'report_uri' => env('CSP_REPORT_URI'),
            'directives' => [
                'default-src' => ["'self'"],
                'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'", "https://cdn.jsdelivr.net"],
                'style-src' => ["'self'", "'unsafe-inline'", "https://fonts.googleapis.com"],
                'img-src' => ["'self'", "data:", "https:", "blob:"],
                'font-src' => ["'self'", "https://fonts.gstatic.com"],
                'connect-src' => ["'self'", "https://api.stripe.com"],
                'frame-src' => ["'self'", "https://js.stripe.com"],
                'object-src' => ["'none'"],
                'base-uri' => ["'self'"],
                'form-action' => ["'self'"],
                'frame-ancestors' => ["'none'"],
                'upgrade-insecure-requests' => [],
            ],
        ],

        // Security headers
        'headers' => [
            'x_frame_options' => 'SAMEORIGIN',
            'x_content_type_options' => 'nosniff',
            'x_xss_protection' => '1; mode=block',
            'strict_transport_security' => [
                'max_age' => env('HSTS_MAX_AGE', 31536000),
                'include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
                'preload' => env('HSTS_PRELOAD', false),
            ],
            'referrer_policy' => 'strict-origin-when-cross-origin',
            'permissions_policy' => [
                'geolocation' => [],
                'microphone' => [],
                'camera' => [],
                'payment' => [],
                'usb' => [],
            ],
        ],

        // Cross-Origin Resource Sharing
        'cors' => [
            'enabled' => env('CORS_ENABLED', true),
            'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000,http://localhost:8080')),
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
            'exposed_headers' => ['X-Total-Count', 'X-Page-Count'],
            'max_age' => env('CORS_MAX_AGE', 86400),
            'supports_credentials' => env('CORS_CREDENTIALS', true),
        ],

        // CSRF protection
        'csrf' => [
            'enabled' => env('CSRF_PROTECTION_ENABLED', true),
            'token_refresh_interval' => env('CSRF_TOKEN_REFRESH', 3600), // seconds
            'exclude_routes' => [
                'api/*',
                'webhook/*',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Validation and Sanitization
    |--------------------------------------------------------------------------
    |
    | Configure input validation and sanitization rules.
    |
    */

    'validation' => [
        // Input sanitization
        'sanitization' => [
            'enabled' => env('INPUT_SANITIZATION_ENABLED', true),
            'strip_tags' => env('STRIP_TAGS_ENABLED', true),
            'trim_strings' => env('TRIM_STRINGS_ENABLED', true),
            'remove_null_bytes' => env('REMOVE_NULL_BYTES_ENABLED', true),
        ],

        // SQL injection protection
        'sql_injection' => [
            'enabled' => env('SQL_INJECTION_PROTECTION_ENABLED', true),
            'blacklist' => [
                'DROP',
                'DELETE',
                'INSERT',
                'UPDATE',
                'UNION',
                'SELECT',
                'EXEC',
                'ALTER',
                'CREATE',
                'TRUNCATE',
            ],
        ],

        // XSS protection
        'xss' => [
            'enabled' => env('XSS_PROTECTION_ENABLED', true),
            'html_purifier' => env('HTML_PURIFIER_ENABLED', false),
            'encoding' => 'UTF-8',
        ],

        // File upload security
        'file_uploads' => [
            'max_size' => env('MAX_UPLOAD_SIZE', 10240), // KB
            'allowed_types' => explode(',', env('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx')),
            'scan_uploads' => env('SCAN_UPLOADS_ENABLED', false),
            'quarantine_uploads' => env('QUARANTINE_UPLOADS', true),
            'virus_scanner' => env('VIRUS_SCANNER', 'clamav'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Auditing
    |--------------------------------------------------------------------------
    |
    | Configure security monitoring and auditing settings.
    |
    */

    'monitoring' => [
        // Security event logging
        'logging' => [
            'enabled' => env('SECURITY_LOGGING_ENABLED', true),
            'log_level' => env('SECURITY_LOG_LEVEL', 'warning'),
            'events' => [
                'auth.login.failed',
                'auth.login.success',
                'auth.password.reset',
                'auth.2fa.failed',
                'api.rate_limit.exceeded',
                'security.csrf.invalid',
                'security.xss.detected',
                'security.sql_injection.detected',
                'security.suspicious_activity',
            ],
        ],

        // Intrusion detection
        'intrusion_detection' => [
            'enabled' => env('INTRUSION_DETECTION_ENABLED', false),
            'rules_file' => env('INTRUSION_RULES_FILE', 'security/rules.json'),
            'alert_threshold' => env('INTRUSION_ALERT_THRESHOLD', 5),
            'block_duration' => env('INTRUSION_BLOCK_DURATION', 3600), // seconds
        ],

        // Security scanning
        'scanning' => [
            'enabled' => env('SECURITY_SCANNING_ENABLED', true),
            'schedule' => env('SECURITY_SCAN_SCHEDULE', '0 3 * * *'), // Daily at 3 AM
            'vulnerability_database' => env('VULNERABILITY_DB_URL', 'https://nvd.nist.gov/feeds/json/cve/1.1/'),
        ],

        // Audit trails
        'audit' => [
            'enabled' => env('AUDIT_TRAILS_ENABLED', true),
            'retention_days' => env('AUDIT_TRAIL_RETENTION_DAYS', 365),
            'log_user_actions' => env('LOG_USER_ACTIONS', true),
            'log_admin_actions' => env('LOG_ADMIN_ACTIONS', true),
            'sensitive_data_masking' => env('SENSITIVE_DATA_MASKING', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup and Recovery
    |--------------------------------------------------------------------------
    |
    | Configure backup and recovery security settings.
    |
    */

    'backup' => [
        // Encryption
        'encryption' => [
            'enabled' => env('BACKUP_ENCRYPTION_ENABLED', true),
            'password' => env('BACKUP_ENCRYPTION_PASSWORD'),
            'algorithm' => 'AES-256-CBC',
        ],

        // Storage security
        'storage' => [
            'local_encryption' => env('LOCAL_BACKUP_ENCRYPTION', true),
            'cloud_encryption' => env('CLOUD_BACKUP_ENCRYPTION', true),
            'access_control' => env('BACKUP_ACCESS_CONTROL', true),
        ],

        // Retention and rotation
        'retention' => [
            'daily' => env('BACKUP_DAILY_RETENTION', 7),
            'weekly' => env('BACKUP_WEEKLY_RETENTION', 4),
            'monthly' => env('BACKUP_MONTHLY_RETENTION', 12),
            'yearly' => env('BACKUP_YEARLY_RETENTION', 5),
        ],

        // Verification
        'verification' => [
            'enabled' => env('BACKUP_VERIFICATION_ENABLED', true),
            'checksum_algorithm' => 'sha256',
            'test_restoration' => env('BACKUP_TEST_RESTORATION', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Security
    |--------------------------------------------------------------------------
    |
    | Configure environment-specific security settings.
    |
    */

    'environment' => [
        'production' => [
            'debug' => false,
            'error_reporting' => 0,
            'display_errors' => false,
            'log_errors' => true,
            'force_https' => true,
            'strict_transport_security' => true,
        ],
        'staging' => [
            'debug' => false,
            'error_reporting' => E_ALL & ~E_DEPRECATED & ~E_STRICT,
            'display_errors' => false,
            'log_errors' => true,
            'force_https' => true,
            'strict_transport_security' => true,
        ],
        'local' => [
            'debug' => true,
            'error_reporting' => E_ALL,
            'display_errors' => true,
            'log_errors' => true,
            'force_https' => false,
            'strict_transport_security' => false,
        ],
        'testing' => [
            'debug' => false,
            'error_reporting' => 0,
            'display_errors' => false,
            'log_errors' => false,
            'force_https' => false,
            'strict_transport_security' => false,
        ],
    ],
];
