<?php
/**
 * Router — maps [METHOD, path] to [Controller, action, optional permission]
 * Supports :param segments.
 */
class Router
{
    private array $routes = [];

    /** Register a route */
    public function add(string $method, string $path, string $handler, ?string $permission = null): void
    {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => $path,
            'handler'    => $handler,      // 'ControllerClass@method'
            'permission' => $permission,
        ];
    }

    /** Dispatch the current request */
    public function dispatch(string $uri, string $method): void
    {
        // Strip query string and base path prefix
        $path = parse_url($uri, PHP_URL_PATH);
        $base = '/banking-system/public';
        if (str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        $path = '/' . trim($path, '/');
        if ($path === '/') $path = '/login';

        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            $params = [];
            if ($route['method'] !== $method) continue;
            if (!$this->match($route['path'], $path, $params)) continue;

            // ── Auth & Permission check ──────────────────────
            $publicRoutes = ['/login', '/logout', '/register', '/reset-password'];
            $isPublic = false;
            foreach ($publicRoutes as $pub) {
                if (str_starts_with($path, $pub)) { $isPublic = true; break; }
            }

            if (!$isPublic) {
                AuthMiddleware::check();
            }
            if ($route['permission']) {
                PermissionMiddleware::require($route['permission']);
            }

            // CSRF on every POST
            if ($method === 'POST') {
                CsrfMiddleware::validate();
            }

            // ── Dispatch to Controller@method ─────────────────
            [$class, $action] = explode('@', $route['handler']);
            $controller = new $class();
            $controller->$action($params);
            return;
        }

        // No match → 404
        http_response_code(404);
        include BASE_PATH . '/views/errors/404.php';
    }

    /** Match route pattern against actual path; extract :params */
    private function match(string $pattern, string $path, array &$params): bool
    {
        $regex = preg_replace('/:([a-zA-Z_]+)/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        if (preg_match($regex, $path, $matches)) {
            foreach ($matches as $k => $v) {
                if (is_string($k)) $params[$k] = $v;
            }
            return true;
        }
        return false;
    }
}
