<?php

namespace App\Middleware;

use Core\Request\Request;
use Core\Response\Response;

/**
 * AuthMiddleware – Requires an authenticated session.
 */
class AuthMiddleware
{
    public function __construct(private Request $request) {}

    public function handle(callable $next): void
    {
        if (!isset($_SESSION['user'])) {
            if ($this->request->isAjax() || $this->request->isJson()) {
                Response::error('Unauthenticated.', 401);
            }
            $_SESSION['_intended'] = $this->request->uri();
            Response::redirect('/auth/login');
        }
        $next();
    }
}

/**
 * AdminMiddleware – Requires admin role.
 */
class AdminMiddleware
{
    public function __construct(private Request $request) {}

    public function handle(callable $next): void
    {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            Response::abort(403, 'Access denied.');
        }
        $next();
    }
}

/**
 * AgentMiddleware – Requires agent or admin role.
 */
class AgentMiddleware
{
    public function __construct(private Request $request) {}

    public function handle(callable $next): void
    {
        $role = $_SESSION['user']['role'] ?? '';
        if (!in_array($role, ['agent', 'admin'])) {
            Response::abort(403, 'Agent access required.');
        }
        $next();
    }
}

/**
 * GuestMiddleware – Redirects authenticated users away from login/register.
 */
class GuestMiddleware
{
    public function __construct(private Request $request) {}

    public function handle(callable $next): void
    {
        if (isset($_SESSION['user'])) {
            Response::redirect('/dashboard');
        }
        $next();
    }
}

/**
 * ApiMiddleware – Validates Bearer token for API routes.
 */
class ApiMiddleware
{
    public function __construct(private Request $request) {}

    public function handle(callable $next): void
    {
        // Add Bearer token / API key validation in Phase 9
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-TOKEN');

        if ($this->request->method() === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        $next();
    }
}
