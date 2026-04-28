<?php
/**
 * PermissionMiddleware — checks if the current user has a specific permission.
 */
class PermissionMiddleware
{
    public static function require(string $action): void
    {
        $permissions = Session::get('permissions', []);
        if (!in_array($action, $permissions, true)) {
            LogManager::log('UNAUTHORIZED_ACCESS', $action, 'failure', [
                'role' => Session::get('role', 'unknown'),
                'path' => $_SERVER['REQUEST_URI'] ?? '',
            ]);
            http_response_code(403);
            include BASE_PATH . '/views/errors/403.php';
            exit;
        }
    }

    /** Returns true if current user has the given permission */
    public static function has(string $action): bool
    {
        $permissions = Session::get('permissions', []);
        return in_array($action, $permissions, true);
    }
}
