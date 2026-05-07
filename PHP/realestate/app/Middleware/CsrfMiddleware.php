<?php

namespace App\Middleware;

use Core\Request\Request;
use Core\Response\Response;

/**
 * CsrfMiddleware – Verifies CSRF token on state-changing requests.
 */
class CsrfMiddleware
{
    private Request $request;

    // Methods that must carry a CSRF token
    private array $protected = ['POST', 'PUT', 'PATCH', 'DELETE'];

    // URI prefixes to exclude (e.g. webhooks)
    private array $except = ['/api/'];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(callable $next): void
    {
        if ($this->shouldVerify()) {
            $sessionToken = $_SESSION['_csrf_token'] ?? '';
            $inputToken   = $this->request->csrfToken() ?? '';

            if (!$sessionToken || !hash_equals($sessionToken, $inputToken)) {
                if ($this->request->isAjax() || $this->request->isJson()) {
                    Response::error('CSRF token mismatch.', 403);
                }
                Response::abort(403, 'CSRF token mismatch. Please go back and try again.');
            }
        }

        $next();
    }

    private function shouldVerify(): bool
    {
        if (!in_array($this->request->method(), $this->protected)) return false;

        $uri = $this->request->uri();
        foreach ($this->except as $prefix) {
            if (str_starts_with($uri, $prefix)) return false;
        }
        return true;
    }
}
