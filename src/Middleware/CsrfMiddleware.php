<?php
/**
 * CsrfMiddleware — validates CSRF token on every POST request.
 */
class CsrfMiddleware
{
    public static function validate(): void
    {
        $sessionToken = Session::get('csrf_token', '');
        $postToken    = $_POST['csrf_token'] ?? '';

        if (!$sessionToken || !hash_equals($sessionToken, $postToken)) {
            LogManager::log('CSRF_FAILURE', 'request', 'failure', [
                'path' => $_SERVER['REQUEST_URI'] ?? '',
            ]);
            http_response_code(403);
            include BASE_PATH . '/views/errors/403.php';
            exit;
        }
    }

    public static function verify(): void
    {
        self::validate();
    }

    /** Render the CSRF hidden input field */
    public static function field(): string
    {
        $token = htmlspecialchars(Session::get('csrf_token', ''), ENT_QUOTES, 'UTF-8');
        return "<input type=\"hidden\" name=\"csrf_token\" value=\"{$token}\">";
    }
}
