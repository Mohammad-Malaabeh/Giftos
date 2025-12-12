<?php

return [
    // Basic settings
    'title' => config('app.name') . ' API Documentation',
    'description' => 'Automatically generated API documentation using Scribe.',
    'base_url' => env('APP_URL', 'http://localhost'),

    // Output path (publicly accessible)
    'type' => 'laravel',
    'routes' => [
        // We'll let Scribe automatically discover routes
    ],

    'auth' => [
        'enabled' => true,
        'default' => [
            'in' => 'bearer',
            'name' => 'Authorization',
            'value' => 'Bearer {token}',
        ],
    ],

    'example_languages' => ['bash', 'javascript', 'php'],

    'routes_group' => 'api',

    'generate' => [
        'generate_examples' => true,
        'generate_response' => true,
    ],

    'output' => [
        'docs' => public_path('docs'),
    ],
];
