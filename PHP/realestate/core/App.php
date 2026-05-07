<?php
namespace core;
use Core\Database\Database;
use Core\Request\Request;
use Core\Router\Router;

/**
 * Application Kernel
 */
class App
{
    private static array $config   = [];
    private static ?Database $db   = null;
    private static ?Request $req   = null;
    private static ?Router $router = null;

    public static function boot(): void
    {
        // 1. Environment & config
        require_once ROOT_PATH . '/config/Env.php';
        \Env::load(ROOT_PATH . '/.env');

        self::$config = require ROOT_PATH . '/config/config.php';

        // 2. PHP settings
        self::configurePHP();

        // 3. Autoloader
        spl_autoload_register([static::class, 'autoload']);

        // 4. Session - মেথড কল করা হয়েছে
        self::startSession();

        // 5. Database
        self::$db = Database::getInstance(self::$config['database']);

        // 6. Request & Router
        self::$req    = new Request();
        self::$router = new Router(self::$req);

        // 7. Load routes
        $router = self::$router; 
        require_once ROOT_PATH . '/routes/web.php';
        require_once ROOT_PATH . '/routes/api.php';

        // 8. Dispatch
        self::$router->dispatch();
    }

    public static function db(): Database { return self::$db; }
    public static function config(string $key, mixed $default = null): mixed
    {
        $keys  = explode('.', $key);
        $value = self::$config;
        foreach ($keys as $k) {
            if (!isset($value[$k])) return $default;
            $value = $value[$k];
        }
        return $value;
    }
    public static function request(): Request { return self::$req; }
    public static function router(): Router { return self::$router; }

    private static function configurePHP(): void
    {
        $debug = env('APP_DEBUG', false);
        error_reporting($debug ? E_ALL : E_ERROR | E_WARNING | E_PARSE);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', ROOT_PATH . '/storage/logs/php_errors.log');
        date_default_timezone_set('Asia/Dhaka');
        mb_internal_encoding('UTF-8');
        if (!headers_sent()) {
            header('X-Frame-Options: SAMEORIGIN');
            header('X-Content-Type-Options: nosniff');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
        }
    }

    private static function startSession(): void
    {
        $cfg = self::$config['session'];
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.gc_maxlifetime', (string) $cfg['lifetime']);
        session_name($cfg['name']);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // CSRF Token Generation
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    private static function autoload(string $class): void
    {
        $classPath = str_replace('\\', '/', $class);
        $path = ROOT_PATH . '/' . $classPath . '.php';
        $lowerPath = ROOT_PATH . '/core/' . strtolower($classPath) . '.php';

        if (file_exists($path)) {
            require_once $path;
            return;
        } elseif (file_exists($lowerPath)) {
            require_once $lowerPath;
            return;
        }

        $searchDirs = [
            ROOT_PATH . '/app/Controllers/',
            ROOT_PATH . '/app/Models/',
            ROOT_PATH . '/app/Services/',
            ROOT_PATH . '/core/',
        ];

        $file = basename($classPath) . '.php';
        foreach ($searchDirs as $dir) {
            if (file_exists($dir . $file)) {
                require_once $dir . $file;
                return;
            }
        }
    }
}
