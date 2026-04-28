<?php
/**
 * BroadcastService — writes events to ws_outbox table.
 * The WebSocket server polls this table and pushes to connected clients.
 */
class BroadcastService
{
    /**
     * Send event to a specific user by user_id.
     */
    public static function toUser(int $userId, string $event, array $data = []): void
    {
        try {
            $pdo = Database::getInstance();
            $pdo->prepare(
                "INSERT INTO ws_outbox (user_id, event, data, sent) VALUES (?, ?, ?, 0)"
            )->execute([$userId, $event, json_encode($data)]);

            // Also persist as notification for debit/credit/info events
            if (in_array($event, ['notification', 'balance_updated', 'transaction_completed', 'account_frozen', 'scheduled_executed', 'support_reply'])) {
                self::persistNotification($userId, $event, $data);
            }
        } catch (Throwable $e) {
            // Non-fatal — log but don't break the transaction
            error_log('[BroadcastService::toUser] ' . $e->getMessage());
        }
    }

    /**
     * Send event to all users with a given role name.
     */
    public static function toRole(string $roleName, string $event, array $data = []): void
    {
        try {
            $pdo  = Database::getInstance();
            $stmt = $pdo->prepare(
                "SELECT u.id FROM users u JOIN roles r ON r.id = u.role_id WHERE r.name = ? AND u.is_active = 1"
            );
            $stmt->execute([$roleName]);
            $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($users as $userId) {
                self::toUser((int)$userId, $event, $data);
            }
        } catch (Throwable $e) {
            error_log('[BroadcastService::toRole] ' . $e->getMessage());
        }
    }

    /**
     * Broadcast to ALL active users.
     */
    public static function toAll(string $event, array $data = []): void
    {
        try {
            $pdo   = Database::getInstance();
            $users = $pdo->query("SELECT id FROM users WHERE is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($users as $userId) {
                self::toUser((int)$userId, $event, $data);
            }
        } catch (Throwable $e) {
            error_log('[BroadcastService::toAll] ' . $e->getMessage());
        }
    }

    /**
     * Persist notification to DB for bell-icon display.
     */
    private static function persistNotification(int $userId, string $event, array $data): void
    {
        try {
            $type    = $data['type']    ?? $event;
            $title   = $data['title']   ?? ucfirst(str_replace('_', ' ', $event));
            $message = $data['message'] ?? json_encode($data);

            $pdo = Database::getInstance();
            $pdo->prepare(
                "INSERT INTO notifications (user_id, type, title, message, data, is_read)
                 VALUES (?, ?, ?, ?, ?, 0)"
            )->execute([$userId, $type, $title, $message, json_encode($data)]);
        } catch (Throwable $e) {
            error_log('[BroadcastService::persistNotification] ' . $e->getMessage());
        }
    }

    /**
     * Fetch and mark as sent — called by WebSocket server polling loop.
     * Returns array of pending messages.
     */
    public static function fetchPending(): array
    {
        try {
            $pdo  = Database::getInstance();
            $stmt = $pdo->query(
                "SELECT id, user_id, event, data FROM ws_outbox WHERE sent = 0 ORDER BY id ASC LIMIT 100"
            );
            $rows = $stmt->fetchAll();

            if (!empty($rows)) {
                $ids = implode(',', array_column($rows, 'id'));
                $pdo->exec("UPDATE ws_outbox SET sent = 1 WHERE id IN ($ids)");
            }

            return $rows;
        } catch (Throwable $e) {
            error_log('[BroadcastService::fetchPending] ' . $e->getMessage());
            return [];
        }
    }
}
