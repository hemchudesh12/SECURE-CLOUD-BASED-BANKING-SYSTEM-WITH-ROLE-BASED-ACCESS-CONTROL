<?php
/**
 * AuthMiddleware — ensures user is logged in and session is valid.
 * Binds session to IP + User-Agent.
 */
class AuthMiddleware
{
    public static function check(): void
    {
        if (!Session::isLoggedIn()) {
            header('Location: /banking-system/public/login');
            exit;
        }

        // Bind check: same IP and User-Agent
        $currentIp = $_SERVER['REMOTE_ADDR']     ?? '';
        $currentUa = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (Session::get('ip') !== $currentIp || Session::get('ua') !== $currentUa) {
            LogManager::log('SESSION_HIJACK_DETECTED', 'session', 'failure', [
                'expected_ip' => Session::get('ip'),
                'actual_ip'   => $currentIp,
            ]);
            Session::destroy();
            header('Location: /banking-system/public/login?error=session');
            exit;
        }

        // Check timeout
        Session::checkTimeout();
    }
}
