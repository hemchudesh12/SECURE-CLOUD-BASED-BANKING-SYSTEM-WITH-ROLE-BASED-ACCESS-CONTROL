<?php
/**
 * Session — secure session management utilities
 */
class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;

        $cfg = require BASE_PATH . '/config/app.php';

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => false,   // set true in production (HTTPS)
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_name('BKSESSID');
        session_start();

        // Generate CSRF token once per session
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /** Call after successful login */
    public static function afterLogin(array $user, array $permissions): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']     = $user['id'];
        $_SESSION['username']    = $user['username'];
        $_SESSION['full_name']   = $user['full_name'] ?? $user['username'];
        $_SESSION['role']        = $user['role_name'];
        $_SESSION['role_id']     = $user['role_id'];
        $_SESSION['permissions'] = $permissions;
        $_SESSION['ip']          = $_SERVER['REMOTE_ADDR']  ?? '';
        $_SESSION['ua']          = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['last_active'] = time();
        // Regenerate CSRF token on login
        $_SESSION['csrf_token']  = bin2hex(random_bytes(32));
    }

    /** Destroy session completely */
    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    /** Check inactivity timeout; update last_active */
    public static function checkTimeout(): void
    {
        $timeout = 1800; // 30 minutes
        if (isset($_SESSION['last_active']) &&
            (time() - $_SESSION['last_active']) > $timeout) {
            self::destroy();
            header('Location: /banking-system/public/login?timeout=1');
            exit;
        }
        $_SESSION['last_active'] = time();
    }

    /** Seconds remaining before timeout */
    public static function secondsRemaining(): int
    {
        if (!isset($_SESSION['last_active'])) return 0;
        $elapsed = time() - $_SESSION['last_active'];
        return max(0, 1800 - $elapsed);
    }

    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /** Flash messages */
    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type][] = $message;
    }

    public static function getFlash(): array
    {
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flash;
    }
}
