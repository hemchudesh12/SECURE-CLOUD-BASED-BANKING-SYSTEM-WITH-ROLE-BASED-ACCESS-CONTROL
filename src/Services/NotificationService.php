<?php
/**
 * NotificationService — store and retrieve in-app notifications.
 */
class NotificationService
{
    /** Get unread count for a user */
    public static function unreadCount(int $userId): int
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    /** Get latest N notifications for a user */
    public static function getForUser(int $userId, int $limit = 15): array
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /** Mark all notifications as read for a user */
    public static function markAllRead(int $userId): void
    {
        $pdo = Database::getInstance();
        $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")
            ->execute([$userId]);
    }

    /** Mark a single notification as read */
    public static function markRead(int $notifId, int $userId): void
    {
        $pdo = Database::getInstance();
        $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?")
            ->execute([$notifId, $userId]);
    }

    /** Create a notification */
    public static function create(int $userId, string $type, string $title, string $message, array $data = []): void
    {
        try {
            $pdo = Database::getInstance();
            $pdo->prepare(
                "INSERT INTO notifications (user_id, type, title, message, data, is_read) VALUES (?,?,?,?,?,0)"
            )->execute([$userId, $type, $title, $message, json_encode($data)]);
        } catch (Throwable $e) {
            error_log('[NotificationService::create] ' . $e->getMessage());
        }
    }
}
