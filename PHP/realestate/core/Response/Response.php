<?php

namespace Core\Response;

/**
 * Response – Builds and sends HTTP responses.
 */
class Response
{
    private int $statusCode = 200;
    private array $headers  = [];
    private string $body    = '';

    private static array $statusTexts = [
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        422 => 'Unprocessable Entity',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
    ];

    // ------------------------------------------------------------------ //
    //  Fluent setters                                                      //
    // ------------------------------------------------------------------ //

    public function setStatus(int $code): static
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;
        return $this;
    }

    // ------------------------------------------------------------------ //
    //  Send                                                                //
    // ------------------------------------------------------------------ //

    public function send(): void
    {
        $text = self::$statusTexts[$this->statusCode] ?? 'Unknown';
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->body;
    }

    // ------------------------------------------------------------------ //
    //  Static factories                                                    //
    // ------------------------------------------------------------------ //

    /**
     * Send a JSON response and exit.
     */
    public static function json(
        mixed $data,
        int   $status  = 200,
        array $headers = []
    ): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        header('X-Content-Type-Options: nosniff');

        foreach ($headers as $name => $value) {
            header("$name: $value");
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Standard API success response.
     */
    public static function success(mixed $data = null, string $message = 'Success', int $status = 200): void
    {
        self::json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Standard API error response.
     */
    public static function error(string $message, int $status = 400, mixed $errors = null): void
    {
        $payload = [
            'status'  => false,
            'message' => $message,
        ];
        if ($errors !== null) {
            $payload['errors'] = $errors;
        }
        self::json($payload, $status);
    }

    /**
     * Redirect to a URL.
     */
    public static function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header("Location: $url");
        exit;
    }

    /**
     * Redirect back (uses Referer header or fallback).
     */
    public static function back(string $fallback = '/'): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? $fallback;
        self::redirect($referer);
    }

    /**
     * Render a view file and echo output.
     */
    public static function view(string $viewPath, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        extract($data, EXTR_SKIP);

        $file = ROOT_PATH . '/app/Views/' . str_replace('.', '/', $viewPath) . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("View not found: $viewPath");
        }
        require $file;
    }

    /**
     * Abort with an HTTP error page.
     */
    public static function abort(int $status, string $message = ''): void
    {
        http_response_code($status);
        $text = self::$statusTexts[$status] ?? 'Error';
        // TODO: load dedicated error views when available
        echo "<h1>$status $text</h1><p>" . htmlspecialchars($message) . "</p>";
        exit;
    }
}
