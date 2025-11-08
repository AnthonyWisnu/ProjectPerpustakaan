<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Library Name
    |--------------------------------------------------------------------------
    */

    'name' => env('LIBRARY_NAME', 'Digital Library System'),
    'code' => env('LIBRARY_CODE', 'DLS'),

    /*
    |--------------------------------------------------------------------------
    | Reservation Settings
    |--------------------------------------------------------------------------
    */

    'reservation' => [
        'max_active_reservations' => env('MAX_ACTIVE_RESERVATIONS', 3),
        'max_books_per_reservation' => env('MAX_BOOKS_PER_RESERVATION', 3),
        'expiry_hours' => env('RESERVATION_EXPIRY_HOURS', 24),
        'reminder_before_expiry_hours' => env('RESERVATION_REMINDER_HOURS', 6),
        'enable_qr_code' => env('RESERVATION_ENABLE_QR', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Loan Settings
    |--------------------------------------------------------------------------
    */

    'loan' => [
        'duration_days' => env('LOAN_DURATION_DAYS', 7),
        'max_active_loans' => env('MAX_ACTIVE_LOANS', 5),
        'allow_extension' => env('LOAN_ALLOW_EXTENSION', true),
        'extension_days' => env('LOAN_EXTENSION_DAYS', 7),
        'max_extensions' => env('LOAN_MAX_EXTENSIONS', 1),
        'due_date_reminders' => [3, 1],
        'fine_grace_period_days' => env('LOAN_FINE_GRACE_PERIOD', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fine Settings
    |--------------------------------------------------------------------------
    */

    'fine' => [
        'rate_per_day' => env('FINE_RATE_PER_DAY', 1000),
        'max_amount' => env('FINE_MAX_AMOUNT', 50000),
        'allow_partial_payment' => env('FINE_ALLOW_PARTIAL', false),
        'block_reservations_if_unpaid' => env('FINE_BLOCK_RESERVATIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Member Settings
    |--------------------------------------------------------------------------
    */

    'member' => [
        'number_prefix' => env('MEMBER_NUMBER_PREFIX', 'MBR'),
        'number_length' => env('MEMBER_NUMBER_LENGTH', 6),
        'default_status' => 'active',
        'require_email_verification' => env('MEMBER_REQUIRE_VERIFICATION', false),
        'card_expiry_months' => env('MEMBER_CARD_EXPIRY', 12),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stock Settings
    |--------------------------------------------------------------------------
    */

    'stock' => [
        'low_stock_threshold' => env('STOCK_LOW_THRESHOLD', 2),
        'enable_barcode' => env('STOCK_ENABLE_BARCODE', true),
        'barcode_prefix' => env('STOCK_BARCODE_PREFIX', 'BK'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */

    'notification' => [
        'email_enabled' => env('NOTIFICATION_EMAIL_ENABLED', true),
        'sms_enabled' => env('NOTIFICATION_SMS_ENABLED', false),
        'database_enabled' => env('NOTIFICATION_DATABASE_ENABLED', true),
        'types' => [
            'reservation_created' => true,
            'reservation_ready' => true,
            'reservation_expiring' => true,
            'reservation_expired' => true,
            'loan_created' => true,
            'loan_due_reminder' => true,
            'loan_overdue' => true,
            'fine_payment_received' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Log Settings
    |--------------------------------------------------------------------------
    */

    'activity_log' => [
        'enabled' => env('ACTIVITY_LOG_ENABLED', true),
        'retention_days' => env('ACTIVITY_LOG_RETENTION_DAYS', 90),
        'log_events' => [
            'login', 'logout', 'book_create', 'book_update', 'book_delete',
            'reservation_create', 'reservation_cancel', 'loan_create',
            'loan_return', 'fine_payment', 'user_create', 'user_update',
            'settings_update',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    */

    'upload' => [
        'book_cover' => [
            'max_size' => env('UPLOAD_COVER_MAX_SIZE', 2048), // KB
            'allowed_types' => ['jpg', 'jpeg', 'png', 'webp'],
            'disk' => 'public',
            'path' => 'covers',
        ],
        'profile_picture' => [
            'max_size' => env('UPLOAD_PROFILE_MAX_SIZE', 1024), // KB
            'allowed_types' => ['jpg', 'jpeg', 'png'],
            'disk' => 'public',
            'path' => 'profiles',
        ],
        'book_import' => [
            'max_size' => env('UPLOAD_IMPORT_MAX_SIZE', 5120), // KB
            'allowed_types' => ['xlsx', 'csv'],
            'disk' => 'local',
            'path' => 'imports',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination & Cache Settings
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'per_page' => env('PAGINATION_PER_PAGE', 15),
        'per_page_options' => [10, 15, 25, 50, 100],
    ],

    'cache' => [
        'settings_ttl' => env('CACHE_SETTINGS_TTL', 86400), // 24 hours
        'popular_books_ttl' => env('CACHE_POPULAR_BOOKS_TTL', 3600), // 1 hour
        'statistics_ttl' => env('CACHE_STATISTICS_TTL', 1800), // 30 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */

    'features' => [
        'allow_guest_browsing' => env('FEATURE_GUEST_BROWSING', true),
        'enable_reviews' => env('FEATURE_ENABLE_REVIEWS', false),
        'enable_ratings' => env('FEATURE_ENABLE_RATINGS', false),
        'enable_wishlist' => env('FEATURE_ENABLE_WISHLIST', false),
        'enable_reading_history' => env('FEATURE_ENABLE_READING_HISTORY', true),
        'enable_book_recommendations' => env('FEATURE_ENABLE_RECOMMENDATIONS', false),
        'enable_social_sharing' => env('FEATURE_ENABLE_SOCIAL_SHARING', false),
        'enable_api' => env('FEATURE_ENABLE_API', false),
    ],
];
