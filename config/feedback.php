<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feedback Settings
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration for the feedback system.
    | You can modify these values to customize the feedback functionality.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Enable Feedback System
    |--------------------------------------------------------------------------
    |
    | This option controls if the feedback system is enabled.
    |
    */
    'enabled' => env('FEEDBACK_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | These options control the notification behavior for new feedback.
    |
    */
    'notifications' => [
        // Enable email notifications for new feedback
        'enabled' => env('FEEDBACK_NOTIFICATIONS_ENABLED', true),
        
        // Email address to send notifications to
        'mail_to' => env('FEEDBACK_MAIL_TO', env('MAIL_FROM_ADDRESS')),
        
        // Email subject for notifications
        'subject' => 'New Feedback Submitted',
    ],

    /*
    |--------------------------------------------------------------------------
    | Feedback Types
    |--------------------------------------------------------------------------
    |
    | These are the available feedback types that users can select from.
    | You can add, remove, or modify these as needed.
    |
    */
    'types' => [
        'bug' => [
            'label' => 'Bug Report',
            'icon' => 'bug',
            'color' => 'danger',
        ],
        'feature' => [
            'label' => 'Feature Request',
            'icon' => 'lightbulb',
            'color' => 'success',
        ],
        'suggestion' => [
            'label' => 'Suggestion',
            'icon' => 'comment',
            'color' => 'info',
        ],
        'other' => [
            'label' => 'Other',
            'icon' => 'ellipsis-h',
            'color' => 'secondary',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feedback Statuses
    |--------------------------------------------------------------------------
    |
    | These are the available statuses that feedback can have.
    | You can add, remove, or modify these as needed.
    |
    */
    'statuses' => [
        'new' => [
            'label' => 'New',
            'color' => 'primary',
        ],
        'read' => [
            'label' => 'Read',
            'color' => 'secondary',
        ],
        'in_progress' => [
            'label' => 'In Progress',
            'color' => 'warning',
        ],
        'resolved' => [
            'label' => 'Resolved',
            'color' => 'success',
        ],
        'closed' => [
            'label' => 'Closed',
            'color' => 'dark',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | These options control the pagination settings for the feedback list.
    |
    */
    'pagination' => [
        'per_page' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to the feedback routes.
    |
    */
    'middleware' => [
        'web',
        'auth',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | These options control the route configuration for the feedback system.
    |
    */
    'routes' => [
        'prefix' => 'admin/feedback',
        'name' => 'admin.feedback.',
        'middleware' => ['web', 'auth'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | These options control the performance-related settings for the feedback system.
    |
    */
    'performance' => [
        // Enable caching of feedback data
        'cache_enabled' => env('FEEDBACK_CACHE_ENABLED', true),
        
        // Cache duration in minutes
        'cache_duration' => env('FEEDBACK_CACHE_DURATION', 60),
        
        // Number of feedback items to show in the dashboard
        'recent_items' => 5,
    ],
];
