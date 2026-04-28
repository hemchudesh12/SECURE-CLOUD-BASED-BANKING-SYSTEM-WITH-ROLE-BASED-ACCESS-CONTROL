<?php
/**
 * RateLimitMiddleware — per-IP rate limiting stored in rate_limits table.
 */
class RateLimitMiddleware
{
    /**
     * Check if the current IP has exceeded the limit for a given action.
     * Defaults: 10 attempts per 60-second window.
     *
     * @param string $action   e.g. 'login', 'transfer', 'register'
     * @param int    $maxHits  maximum allowed hits in the window
     * @param int    $windowSec window size in seconds
     */
    public static function check(string $action, int $maxHits = 10, int $windowSec = 60): void
    {
        $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $pdo = Database::getInstance();

        // Cleanup old windows
        $pdo->prepare(
            "DELETE FROM rate_limits WHERE ip_address = ? AND action = ?
             AND window_start < DATE_SUB(NOW(), INTERVAL ? SECOND)"
        )->execute([$ip, $action, $windowSec]);

        // Get or create current window
        $stmt = $pdo->prepare(
            "SELECT id, hit_count FROM rate_limits
             WHERE ip_address = ? AND action = ? AND window_start >= DATE_SUB(NOW(), INTERVAL ? SECOND)
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$ip, $action, $windowSec]);
        $row = $stmt->fetch();

        if ($row) {
            if ((int)$row['hit_count'] >= $maxHits) {
                http_response_code(429);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Too many requests. Please try again later.']);
                exit;
            }
            $pdo->prepare("UPDATE rate_limits SET hit_count = hit_count + 1 WHERE id = ?")
                ->execute([$row['id']]);
        } else {
            $pdo->prepare(
                "INSERT INTO rate_limits (ip_address, action, hit_count, window_start) VALUES (?,?,1,NOW())"
            )->execute([$ip, $action]);
        }
    }

    /**
     * Reset rate limit for an IP + action (e.g. after successful login).
     */
    public static function reset(string $action): void
    {
        $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $pdo = Database::getInstance();
        $pdo->prepare("DELETE FROM rate_limits WHERE ip_address = ? AND action = ?")
            ->execute([$ip, $action]);
    }
}
