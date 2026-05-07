<?php
/**
 * Central application configuration
 * Reads from environment – never hardcode secrets here
 */
return [
    'app' => [
        'name'    => env('APP_NAME', 'NextGen Real Estate'),
        'env'     => env('APP_ENV', 'production'),
        'url'     => env('APP_URL', 'http://localhost'),
        'debug'   => env('APP_DEBUG', false),
        'key'     => env('APP_KEY'),
        'lang'    => env('APP_LANG', 'en'),
        'version' => '1.0.0',
    ],

    'database' => [
        'host'    => env('DB_HOST', '127.0.0.1'),
        'port'    => (int) env('DB_PORT', 3306),
        'name'    => env('DB_NAME', 'realestate_db'),
        'user'    => env('DB_USER', 'root'),
        'pass'    => env('DB_PASS', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'options' => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ],
    ],

    'session' => [
        'name'     => env('SESSION_NAME', 'realestate_session'),
        'lifetime' => (int) env('SESSION_LIFETIME', 7200),
    ],

    'upload' => [
        'max_size'     => (int) env('MAX_FILE_SIZE', 10485760),
        'image_types'  => explode(',', env('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,webp')),
        'video_types'  => explode(',', env('ALLOWED_VIDEO_TYPES', 'mp4,webm,mov')),
        'image_path'   => ROOT_PATH . '/public/uploads/images/',
        'video_path'   => ROOT_PATH . '/public/uploads/videos/',
    ],

    'pagination' => [
        'per_page' => (int) env('ITEMS_PER_PAGE', 12),
    ],

    'cache' => [
        'driver' => env('CACHE_DRIVER', 'file'),
        'ttl'    => (int) env('CACHE_TTL', 3600),
        'path'   => ROOT_PATH . '/storage/cache/',
    ],

    'mail' => [
        'driver'    => env('MAIL_DRIVER', 'log'),
        'host'      => env('MAIL_HOST'),
        'port'      => (int) env('MAIL_PORT', 587),
        'user'      => env('MAIL_USER'),
        'pass'      => env('MAIL_PASS'),
        'from'      => env('MAIL_FROM'),
        'from_name' => env('MAIL_FROM_NAME', 'Real Estate'),
    ],

    'maps' => [
        'google_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'payment' => [
        'sslcommerz' => [
            'store_id'   => env('SSLCOMMERZ_STORE_ID'),
            'store_pass' => env('SSLCOMMERZ_STORE_PASS'),
            'mode'       => env('SSLCOMMERZ_MODE', 'sandbox'),
        ],
        'bkash' => [
            'app_key'    => env('BKASH_APP_KEY'),
            'app_secret' => env('BKASH_APP_SECRET'),
        ],
    ],
];
