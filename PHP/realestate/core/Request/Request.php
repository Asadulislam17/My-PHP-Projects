<?php

namespace Core\Request;

/**
 * Request – Wraps the current HTTP request.
 *
 * Provides sanitized access to GET/POST/FILES/headers and
 * CSRF token management.
 */
class Request
{
    private array $get;
    private array $post;
    private array $files;
    private array $server;
    private array $headers;
    private ?array $jsonBody = null;

    public function __construct()
    {
        $this->get     = $_GET    ?? [];
        $this->post    = $_POST   ?? [];
        $this->files   = $_FILES  ?? [];
        $this->server  = $_SERVER ?? [];
        $this->headers = $this->parseHeaders();

        // Parse JSON body if Content-Type is application/json
        $contentType = $this->header('Content-Type', '');
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $this->jsonBody = json_decode($raw, true) ?? [];
        }
    }

    // ------------------------------------------------------------------ //
    //  Input retrieval                                                     //
    // ------------------------------------------------------------------ //

    /** Get a sanitized GET parameter */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->sanitize($this->get[$key] ?? $default);
    }

    /** Get a sanitized POST parameter */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->sanitize($this->post[$key] ?? $default);
    }

    /** Get a value from JSON body (API requests) */
    public function json(string $key, mixed $default = null): mixed
    {
        return $this->jsonBody[$key] ?? $default;
    }

    /**
     * Get from POST, then JSON body, then GET (in that priority).
     */
    public function input(string $key, mixed $default = null): mixed
    {
        if (isset($this->post[$key])) {
            return $this->sanitize($this->post[$key]);
        }
        if ($this->jsonBody !== null && isset($this->jsonBody[$key])) {
            return $this->jsonBody[$key];
        }
        return $this->sanitize($this->get[$key] ?? $default);
    }

    /** Return all POST + GET inputs merged (POST takes priority) */
    public function all(): array
    {
        return array_merge($this->get, $this->post, $this->jsonBody ?? []);
    }

    /** Return only specified keys */
    public function only(array $keys): array
    {
        $all = $this->all();
        return array_intersect_key($all, array_flip($keys));
    }

    /** Return all except specified keys */
    public function except(array $keys): array
    {
        $all = $this->all();
        return array_diff_key($all, array_flip($keys));
    }

    public function has(string $key): bool
    {
        return isset($this->post[$key]) || isset($this->get[$key])
            || isset($this->jsonBody[$key]);
    }

    // ------------------------------------------------------------------ //
    //  Files                                                               //
    // ------------------------------------------------------------------ //

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    // ------------------------------------------------------------------ //
    //  Request meta                                                        //
    // ------------------------------------------------------------------ //

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function isGet(): bool  { return $this->method() === 'GET'; }
    public function isPost(): bool { return $this->method() === 'POST'; }

    public function isAjax(): bool
    {
        return strtolower($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    public function isJson(): bool
    {
        return str_contains($this->header('Content-Type', ''), 'application/json');
    }

    public function uri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        return strtok($uri, '?'); // Strip query string
    }

    public function fullUri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function baseUrl(): string
    {
        $scheme = (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $this->server['HTTP_HOST'] ?? 'localhost';
        return "$scheme://$host";
    }

    public function ip(): string
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($this->server[$key])) {
                return explode(',', $this->server[$key])[0];
            }
        }
        return '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $normalized = strtolower(str_replace('-', '_', $key));
        return $this->headers[$normalized] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $auth = $this->header('Authorization', '');
        if (str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }
        return null;
    }

    // ------------------------------------------------------------------ //
    //  CSRF                                                                //
    // ------------------------------------------------------------------ //

    public function csrfToken(): ?string
    {
        return $this->post('_token') ?? $this->header('X-CSRF-TOKEN');
    }

    // ------------------------------------------------------------------ //
    //  Sanitization                                                        //
    // ------------------------------------------------------------------ //

    private function sanitize(mixed $value): mixed
    {
        if (is_string($value)) {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }
        return $value;
    }

    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name           = strtolower(substr($key, 5));
                $headers[$name] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $headers[strtolower($key)] = $value;
            }
        }
        return $headers;
    }
}
