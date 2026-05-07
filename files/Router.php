<?php

namespace Core\Router;

use Core\Request\Request;

/**
 * Router – Maps URI patterns to controller actions.
 *
 * Supports:
 *  - GET / POST / PUT / DELETE / PATCH
 *  - Named parameters  (/property/{slug})
 *  - Route groups with prefix + shared middleware
 *  - Middleware stack
 *  - Named routes for URL generation
 */
class Router
{
    /** @var array<string, array> */
    private array $routes = [];

    /** @var array<string, string> Named route → URI template */
    private array $namedRoutes = [];

    /** @var array Current group settings during group() callback */
    private array $currentGroup = ['prefix' => '', 'middleware' => []];

    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    // ------------------------------------------------------------------ //
    //  Registration                                                        //
    // ------------------------------------------------------------------ //

    public function get(string $uri, array|callable $action): RouteEntry
    {
        return $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, array|callable $action): RouteEntry
    {
        return $this->addRoute('POST', $uri, $action);
    }

    public function put(string $uri, array|callable $action): RouteEntry
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    public function delete(string $uri, array|callable $action): RouteEntry
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    public function patch(string $uri, array|callable $action): RouteEntry
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    /** Register GET + POST for the same URI (useful for forms) */
    public function any(string $uri, array|callable $action): void
    {
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $this->addRoute($method, $uri, $action);
        }
    }

    // ------------------------------------------------------------------ //
    //  Grouping                                                            //
    // ------------------------------------------------------------------ //

    public function group(array $attributes, callable $callback): void
    {
        $previousGroup = $this->currentGroup;

        $this->currentGroup = [
            'prefix'     => $previousGroup['prefix'] . ($attributes['prefix'] ?? ''),
            'middleware' => array_merge($previousGroup['middleware'], $attributes['middleware'] ?? []),
        ];

        $callback($this);

        $this->currentGroup = $previousGroup;
    }

    // ------------------------------------------------------------------ //
    //  Dispatch                                                            //
    // ------------------------------------------------------------------ //

    public function dispatch(): void
    {
        $method = $this->request->method();
        $uri    = $this->normalizePath($this->request->uri());

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $params = $this->matchUri($route['pattern'], $uri);
            if ($params === null) continue;

            // Run middleware stack
            $this->runMiddleware($route['middleware'], function () use ($route, $params) {
                $this->callAction($route['action'], $params);
            });

            return;
        }

        // No route matched
        if ($method !== 'GET') {
            // Check if URI exists for GET (Method Not Allowed)
            foreach ($this->routes as $route) {
                $params = $this->matchUri($route['pattern'], $uri);
                if ($params !== null) {
                    \Core\Response\Response::abort(405, "Method $method not allowed.");
                    return;
                }
            }
        }

        \Core\Response\Response::abort(404, "Page not found: $uri");
    }

    // ------------------------------------------------------------------ //
    //  URL generation                                                      //
    // ------------------------------------------------------------------ //

    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \RuntimeException("Named route not found: $name");
        }

        $uri = $this->namedRoutes[$name];
        foreach ($params as $key => $value) {
            $uri = str_replace('{' . $key . '}', $value, $uri);
        }
        return env('APP_URL', '') . $uri;
    }

    // ------------------------------------------------------------------ //
    //  Internals                                                           //
    // ------------------------------------------------------------------ //

    private function addRoute(string $method, string $uri, array|callable $action): RouteEntry
    {
        $fullUri  = $this->currentGroup['prefix'] . '/' . ltrim($uri, '/');
        $fullUri  = $this->normalizePath($fullUri);
        $pattern  = $this->buildPattern($fullUri);
        $entry    = new RouteEntry($method, $fullUri, $pattern, $action, $this->currentGroup['middleware']);

        $this->routes[] = &$entry->toArray();

        return $entry;
    }

    private function buildPattern(string $uri): string
    {
        // Replace {param} with named capture groups
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    private function matchUri(string $pattern, string $uri): ?array
    {
        if (!preg_match($pattern, $uri, $matches)) {
            return null;
        }
        // Return only named captures
        return array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
    }

    private function normalizePath(string $path): string
    {
        // Strip script name from path if running in subdirectory
        $base = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($base !== '/' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        return '/' . ltrim($path, '/');
    }

    private function callAction(array|callable $action, array $params): void
    {
        if (is_callable($action)) {
            call_user_func_array($action, [$this->request, ...$params]);
            return;
        }

        [$controllerClass, $method] = $action;

        // Auto-namespace if not fully qualified
        if (!str_contains($controllerClass, '\\')) {
            $controllerClass = 'App\\Controllers\\' . $controllerClass;
        }

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller not found: $controllerClass");
        }

        $controller = new $controllerClass($this->request);

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Method $method not found in $controllerClass");
        }

        call_user_func_array([$controller, $method], $params);
    }

    private function runMiddleware(array $middleware, callable $final): void
    {
        $stack = array_reverse($middleware);
        $next  = $final;

        foreach ($stack as $mw) {
            $currentNext = $next;
            $next = function () use ($mw, $currentNext) {
                if (!str_contains($mw, '\\')) {
                    $mw = 'App\\Middleware\\' . $mw;
                }
                (new $mw($this->request))->handle($currentNext);
            };
        }

        $next();
    }
}

// ------------------------------------------------------------------ //
//  RouteEntry – Fluent helper returned by get/post/etc.              //
// ------------------------------------------------------------------ //

class RouteEntry
{
    private array $data;

    public function __construct(
        string         $method,
        string         $uri,
        string         $pattern,
        array|callable $action,
        array          $middleware
    ) {
        $this->data = compact('method', 'uri', 'pattern', 'action', 'middleware');
    }

    public function name(string $name): static
    {
        // Name is stored externally by Router – this is just fluent sugar
        // The Router needs access; we store it here and Router reads it
        $this->data['name'] = $name;
        return $this;
    }

    public function middleware(string|array $middleware): static
    {
        $add = is_array($middleware) ? $middleware : [$middleware];
        $this->data['middleware'] = array_merge($this->data['middleware'], $add);
        return $this;
    }

    /** Called by Router via reference */
    public function &toArray(): array
    {
        return $this->data;
    }
}
