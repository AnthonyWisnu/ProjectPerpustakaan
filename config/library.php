<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Library Information
    |--------------------------------------------------------------------------
    |
    | Basic information about your library system
    |
    */

    'name' => env('LIBRARY_NAME', 'Perpustakaan Digital'),
    'tagline' => env('LIBRARY_TAGLINE', 'Sistem Manajemen Perpustakaan Modern'),
    'email' => env('LIBRARY_EMAIL', 'info@perpustakaan.com'),
    'phone' => env('LIBRARY_PHONE', '021-12345678'),
    'address' => env('LIBRARY_ADDRESS', 'Jl. Contoh No. 123, Jakarta'),

    /*
    |--------------------------------------------------------------------------
    | Operating Hours
    |--------------------------------------------------------------------------
    |
    | Library operating hours configuration
    |
    */

    'operating_hours' => [
        'weekdays' => env('LIBRARY_HOURS_WEEKDAYS', '08:00 - 17:00'),
        'saturday' => env('LIBRARY_HOURS_SATURDAY', '08:00 - 14:00'),
        'sunday' => env('LIBRARY_HOURS_SUNDAY', 'Tutup'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Loan Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for book loan functionality
    |
    */

    'loan' => [
        // Default loan duration in days
        'duration' => (int) env('LOAN_DURATION', 7),

        // Maximum books that can be borrowed at once
        'max_books' => (int) env('LOAN_MAX_BOOKS', 3),

        // Maximum loan extensions allowed
        'max_extensions' => (int) env('LOAN_MAX_EXTENSIONS', 1),

        // Extension duration in days
        'extension_duration' => (int) env('LOAN_EXTENSION_DURATION', 7),

        // Reminder notification days before due date
        'reminder_days' => (int) env('LOAN_REMINDER_DAYS', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reservation Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for book reservation functionality
    |
    */

    'reservation' => [
        // Maximum books in one reservation
        'max_books' => (int) env('RESERVATION_MAX_BOOKS', 3),

        // Reservation expiration time in hours
        'expiration_hours' => (int) env('RESERVATION_EXPIRATION_HOURS', 24),

        // Auto-cancel expired reservations
        'auto_cancel' => env('RESERVATION_AUTO_CANCEL', true),

        // Notification hours before expiration
        'expiring_notification_hours' => (int) env('RESERVATION_EXPIRING_HOURS', 6),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fine Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for late return fines
    |
    */

    'fine' => [
        // Fine rate per day (in Rupiah)
        'rate_per_day' => (int) env('FINE_RATE_PER_DAY', 1000),

        // Grace period before fines start (in days)
        'grace_period' => (int) env('FINE_GRACE_PERIOD', 0),

        // Maximum fine amount (in Rupiah)
        'max_amount' => (int) env('FINE_MAX_AMOUNT', 50000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Member Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for library members
    |
    */

    'member' => [
        // Member number prefix
        'number_prefix' => env('MEMBER_NUMBER_PREFIX', 'MBR'),

        // Member number format: {prefix}-{year}-{sequential}
        'number_format' => '{prefix}-{year}-{number}',

        // Auto-deactivate inactive members after X months
        'auto_deactivate_months' => (int) env('MEMBER_AUTO_DEACTIVATE_MONTHS', 12),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stock Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for book stock management
    |
    */

    'stock' => [
        // Low stock threshold for alerts
        'low_threshold' => (int) env('STOCK_LOW_THRESHOLD', 5),

        // Minimum stock level
        'minimum_stock' => (int) env('STOCK_MINIMUM', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for notification system
    |
    */

    'notification' => [
        // Enable email notifications
        'email_enabled' => env('NOTIFICATION_EMAIL_ENABLED', true),

        // Enable SMS notifications (requires SMS gateway)
        'sms_enabled' => env('NOTIFICATION_SMS_ENABLED', false),

        // Enable in-app notifications
        'database_enabled' => env('NOTIFICATION_DATABASE_ENABLED', true),

        // Notification channels
        'channels' => [
            'reservation_created' => ['mail', 'database'],
            'reservation_ready' => ['mail', 'database'],
            'reservation_expiring' => ['mail', 'database'],
            'loan_due_reminder' => ['mail', 'database'],
            'loan_overdue' => ['mail', 'database'],
            'fine_payment' => ['mail', 'database'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Log Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for activity logging
    |
    */

    'activity_log' => [
        // Enable activity logging
        'enabled' => env('ACTIVITY_LOG_ENABLED', true),

        // Days to keep logs
        'retention_days' => (int) env('ACTIVITY_LOG_RETENTION_DAYS', 90),

        // Actions to log
        'log_actions' => [
            'book_created',
            'book_updated',
            'book_deleted',
            'reservation_created',
            'reservation_cancelled',
            'loan_created',
            'loan_returned',
            'fine_paid',
            'member_created',
            'member_updated',
            'settings_updated',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for file uploads
    |
    */

    'upload' => [
        // Maximum file size in KB
        'max_size' => [
            'cover_image' => (int) env('UPLOAD_COVER_MAX_SIZE', 2048), // 2MB
            'profile_photo' => (int) env('UPLOAD_PROFILE_MAX_SIZE', 2048), // 2MB
            'import_file' => (int) env('UPLOAD_IMPORT_MAX_SIZE', 5120), // 5MB
        ],

        // Allowed file types
        'allowed_types' => [
            'images' => ['jpg', 'jpeg', 'png', 'gif'],
            'import' => ['xlsx', 'xls', 'csv'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Configuration
    |--------------------------------------------------------------------------
    |
    | Default pagination settings
    |
    */

    'pagination' => [
        'per_page' => (int) env('PAGINATION_PER_PAGE', 20),
        'books_per_page' => (int) env('PAGINATION_BOOKS', 12),
        'members_per_page' => (int) env('PAGINATION_MEMBERS', 20),
        'logs_per_page' => (int) env('PAGINATION_LOGS', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | QR Code & Barcode Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for QR code and barcode generation
    |
    */

    'qrcode' => [
        'size' => (int) env('QRCODE_SIZE', 200),
        'margin' => (int) env('QRCODE_MARGIN', 0),
        'format' => env('QRCODE_FORMAT', 'png'),
    ],

    'barcode' => [
        'type' => env('BARCODE_TYPE', 'CODE128'),
        'height' => (int) env('BARCODE_HEIGHT', 50),
        'width' => (int) env('BARCODE_WIDTH', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for library system
    |
    */

    'cache' => [
        // Cache duration in minutes
        'settings' => (int) env('CACHE_SETTINGS_DURATION', 60),
        'books' => (int) env('CACHE_BOOKS_DURATION', 30),
        'categories' => (int) env('CACHE_CATEGORIES_DURATION', 60),
    ],

];
