<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Maximum File Size
    |--------------------------------------------------------------------------
    |
    | Maximum file size in bytes. Default is 10MB.
    |
    */
    'max_file_size' => env('UPLOAD_MAX_FILE_SIZE', 10485760), // 10MB

    /*
    |--------------------------------------------------------------------------
    | Allowed MIME Types
    |--------------------------------------------------------------------------
    |
    | Whitelist of allowed MIME types for different file categories.
    |
    */
    'allowed_mime_types' => [
        'images' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ],
        'documents' => [
            'application/pdf',
        ],
        'videos' => [
            'video/mp4',
            'video/quicktime',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed File Extensions
    |--------------------------------------------------------------------------
    |
    | Whitelist of allowed file extensions for different file categories.
    |
    */
    'allowed_extensions' => [
        'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'documents' => ['pdf'],
        'videos' => ['mp4', 'mov'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Malware Scanning
    |--------------------------------------------------------------------------
    |
    | Enable malware scanning for uploaded files. Requires ClamAV or similar.
    |
    */
    'scan_enabled' => env('UPLOAD_SCAN_ENABLED', false),
    'scan_command' => env('UPLOAD_SCAN_COMMAND', 'clamscan'),
    'scan_timeout' => env('UPLOAD_SCAN_TIMEOUT', 30), // seconds
];
