<?php

/**
 * Public Entry Point
 * ──────────────────
 * All web traffic is routed here via .htaccess
 * This is the ONLY public PHP file.
 */

declare(strict_types=1);

// Security: block direct access to sensitive paths
if (isset($_SERVER['REQUEST_URI'])) {
    $blocked = ['/config/', '/storage/', '/core/', '/app/', '/.env'];
    foreach ($blocked as $path) {
        if (str_starts_with($_SERVER['REQUEST_URI'], $path)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }
}

// ── Constants ──────────────────────────────────────────────────── //
define('ROOT_PATH', dirname(__DIR__));
define('START_TIME', microtime(true));

// ── Bootstrap ─────────────────────────────────────────────────── //
require_once ROOT_PATH . '/core/App.php';

// ── Launch ────────────────────────────────────────────────────── //
try {
    \core\App::boot();
} catch (Throwable $e) {
    $debug = $_ENV['APP_DEBUG'] ?? false;

    if ($debug) {
        echo '<pre style="background:#fee;color:#900;padding:1rem;font-family:monospace;">';
        echo '<strong>Error: </strong>' . htmlspecialchars($e->getMessage()) . "\n\n";
        echo '<strong>File:</strong> '  . $e->getFile() . ':' . $e->getLine() . "\n\n";
        echo '<strong>Stack trace:</strong>' . "\n" . $e->getTraceAsString();
        echo '</pre>';
    } else {
        http_response_code(500);
        echo '<h1>500 – Internal Server Error</h1><p>Something went wrong. Please try again later.</p>';
    }

    error_log('[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
}
