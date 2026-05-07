<?php

namespace App\Helpers;

/**
 * Logger – Simple PSR-3 inspired file logger.
 */
class Logger
{
    private static string $logPath = '';

    public static function init(string $path = ''): void
    {
        self::$logPath = $path ?: ROOT_PATH . '/storage/logs/';
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function security(string $message, array $context = []): void
    {
        self::write('SECURITY', $message, $context, 'security.log');
    }

    public static function activity(string $action, ?int $userId = null, array $extra = []): void
    {
        $context = array_merge(['user_id' => $userId], $extra);
        self::write('ACTIVITY', $action, $context, 'activity.log');

        // Also log to DB if available
        try {
            $db = \App::db();
            $db->insert('logs', [
                'type'    => 'activity',
                'user_id' => $userId,
                'action'  => $action,
                'details' => json_encode($extra),
                'ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable) {
            // Fail silently – logging should never break the app
        }
    }

    private static function write(string $level, string $message, array $context = [], string $file = 'app.log'): void
    {
        if (!self::$logPath) self::init();

        $timestamp = date('Y-m-d H:i:s');
        $ctx       = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        $line      = "[$timestamp] [$level] $message$ctx" . PHP_EOL;

        $dailyFile = self::$logPath . date('Y-m-d') . '_' . $file;
        error_log($line, 3, $dailyFile);
    }
}
