<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Services Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for internal application services
    |
    */

    'file_upload' => [
        'max_size' => env('FILE_UPLOAD_MAX_SIZE', 10 * 1024 * 1024), // 10MB
        'allowed_types' => [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'documents' => ['pdf', 'doc', 'docx', 'txt'],
        ],
        'storage_path' => env('FILE_STORAGE_PATH', 'public'),
    ],

    'notifications' => [
        'email_enabled' => env('NOTIFICATIONS_EMAIL_ENABLED', true),
        'push_enabled' => env('NOTIFICATIONS_PUSH_ENABLED', false),
        'sms_enabled' => env('NOTIFICATIONS_SMS_ENABLED', false),
    ],

    'family' => [
        'max_members' => env('FAMILY_MAX_MEMBERS', 100),
        'auto_suggestions' => env('FAMILY_AUTO_SUGGESTIONS', true),
        'relationship_verification' => env('FAMILY_RELATIONSHIP_VERIFICATION', true),
    ],

    'search' => [
        'max_results' => env('SEARCH_MAX_RESULTS', 50),
        'min_query_length' => env('SEARCH_MIN_QUERY_LENGTH', 2),
        'enable_fuzzy_search' => env('SEARCH_ENABLE_FUZZY', true),
    ],

    'analytics' => [
        'enabled' => env('ANALYTICS_ENABLED', true),
        'retention_days' => env('ANALYTICS_RETENTION_DAYS', 365),
        'track_user_activity' => env('ANALYTICS_TRACK_USER_ACTIVITY', true),
    ],

    'events' => [
        'real_time_enabled' => env('EVENTS_REAL_TIME_ENABLED', true),
        'webhook_url' => env('EVENTS_WEBHOOK_URL'),
        'max_retries' => env('EVENTS_MAX_RETRIES', 3),
    ],

];
