<?php

namespace App\Controllers;

use Core\Request\Request;
use Core\Response\Response;
use Core\Database\Database;

/**
 * BaseController – All controllers extend this.
 *
 * Provides:
 *  - View rendering with layouts
 *  - Redirect helpers
 *  - Flash messages
 *  - Basic validation
 *  - Auth checks
 *  - CSRF verification
 */
abstract class BaseController
{
    protected Request  $request;
    protected Database $db;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->db      = \core\App::db();
    }

    // ------------------------------------------------------------------ //
    //  View rendering                                                      //
    // ------------------------------------------------------------------ //

    /**
     * Render a view inside the default layout.
     *
     * @param string $view   Dot-notation: 'property.index' → Views/property/index.php
     * @param array  $data   Variables to extract into the view
     * @param string $layout Layout file (default: 'main')
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        $data['flash'] = $this->getFlash();
        $data['auth']  = $this->authUser();

        $viewFile   = ROOT_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        $layoutFile = ROOT_PATH . '/app/Views/layouts/' . $layout . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: $view");
        }

        extract($data, EXTR_SKIP);

        // Capture view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render inside layout
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Render a view WITHOUT any layout (for partials / AJAX).
     */
    protected function partial(string $view, array $data = []): void
    {
        $viewFile = ROOT_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Partial not found: $view");
        }
        extract($data, EXTR_SKIP);
        require $viewFile;
    }

    // ------------------------------------------------------------------ //
    //  Response helpers                                                    //
    // ------------------------------------------------------------------ //

    protected function redirect(string $url): void
    {
        Response::redirect($url);
    }

    protected function back(): void
    {
        Response::back();
    }

    protected function json(mixed $data, int $status = 200): void
    {
        Response::json($data, $status);
    }

    protected function success(mixed $data = null, string $msg = 'Success', int $status = 200): void
    {
        Response::success($data, $msg, $status);
    }

    protected function error(string $msg, int $status = 400, mixed $errors = null): void
    {
        Response::error($msg, $status, $errors);
    }

    protected function abort(int $code, string $msg = ''): void
    {
        Response::abort($code, $msg);
    }

    // ------------------------------------------------------------------ //
    //  Flash messages                                                      //
    // ------------------------------------------------------------------ //

    protected function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type][] = $message;
    }

    protected function getFlash(): array
    {
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flash;
    }

    // ------------------------------------------------------------------ //
    //  Validation                                                          //
    // ------------------------------------------------------------------ //

    /**
     * Validate request inputs.
     *
     * Rules: required | min:N | max:N | email | numeric | in:a,b,c | unique:table,column
     *
     * @throws \InvalidArgumentException on failure (stores errors in session)
     * @return array Validated data
     */
    protected function validate(array $rules): array
    {
        $errors = [];
        $data   = [];

        foreach ($rules as $field => $ruleString) {
            $value = $this->request->input($field);
            $ruleList = explode('|', $ruleString);

            foreach ($ruleList as $rule) {
                [$ruleName, $param] = array_pad(explode(':', $rule, 2), 2, null);

                switch ($ruleName) {
                    case 'required':
                        if ($value === null || $value === '') {
                            $errors[$field][] = ucfirst($field) . ' is required.';
                        }
                        break;

                    case 'email':
                        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = ucfirst($field) . ' must be a valid email.';
                        }
                        break;

                    case 'min':
                        if (strlen((string)$value) < (int)$param) {
                            $errors[$field][] = ucfirst($field) . " must be at least $param characters.";
                        }
                        break;

                    case 'max':
                        if (strlen((string)$value) > (int)$param) {
                            $errors[$field][] = ucfirst($field) . " may not exceed $param characters.";
                        }
                        break;

                    case 'numeric':
                        if ($value !== null && $value !== '' && !is_numeric($value)) {
                            $errors[$field][] = ucfirst($field) . ' must be numeric.';
                        }
                        break;

                    case 'in':
                        $allowed = explode(',', $param);
                        if ($value && !in_array($value, $allowed)) {
                            $errors[$field][] = ucfirst($field) . ' is invalid.';
                        }
                        break;

                    case 'unique':
                        [$table, $column] = explode(',', $param);
                        $exists = $this->db->selectOne(
                            "SELECT id FROM `$table` WHERE `$column` = ?",
                            [$value]
                        );
                        if ($exists) {
                            $errors[$field][] = ucfirst($field) . ' is already taken.';
                        }
                        break;
                }
            }

            $data[$field] = $value;
        }

        if (!empty($errors)) {
            $_SESSION['_validation_errors'] = $errors;
            $_SESSION['_old_input']         = $data;
            $this->back();
            exit;
        }

        return $data;
    }

    protected function validationErrors(): array
    {
        $errors = $_SESSION['_validation_errors'] ?? [];
        unset($_SESSION['_validation_errors']);
        return $errors;
    }

    protected function oldInput(string $key, mixed $default = ''): mixed
    {
        $old = $_SESSION['_old_input'] ?? [];
        unset($_SESSION['_old_input']);
        return $old[$key] ?? $default;
    }

    // ------------------------------------------------------------------ //
    //  CSRF                                                                //
    // ------------------------------------------------------------------ //

    protected function verifyCsrf(): void
    {
        $token = $this->request->csrfToken();
        if (!$token || !hash_equals($_SESSION['_csrf_token'] ?? '', $token)) {
            $this->abort(403, 'CSRF token mismatch.');
        }
    }

    protected function generateCsrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    // ------------------------------------------------------------------ //
    //  Auth helpers                                                        //
    // ------------------------------------------------------------------ //

    protected function authUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }

    protected function requireAuth(): void
    {
        if (!$this->isLoggedIn()) {
            $this->flash('error', 'Please log in to continue.');
            $this->redirect('/auth/login');
            exit;
        }
    }

    protected function requireRole(string ...$roles): void
    {
        $this->requireAuth();
        $userRole = $_SESSION['user']['role'] ?? '';
        if (!in_array($userRole, $roles)) {
            $this->abort(403, 'Access denied.');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireRole('admin');
    }
}
