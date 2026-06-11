<?php

// .env file load koro manually
function loadEnv(string $path): void {
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        $_ENV[$key]   = $value;
        putenv("$key=$value");
    }
}

loadEnv(__DIR__ . '/../.env');

// App Constants
define('APP_NAME',  $_ENV['APP_NAME']  ?? 'RealEstate');
define('APP_URL',   $_ENV['APP_URL']   ?? 'http://localhost/PHP_ALL/realestate');
define('APP_ENV',   $_ENV['APP_ENV']   ?? 'production');
define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? false);

// DB Constants
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'realestate_db');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Paths
define('ROOT_PATH',   __DIR__ . '/../');
define('UPLOAD_PATH', ROOT_PATH . 'assets/uploads/');
define('UPLOAD_URL',  APP_URL . '/assets/uploads/');

// Error display
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}