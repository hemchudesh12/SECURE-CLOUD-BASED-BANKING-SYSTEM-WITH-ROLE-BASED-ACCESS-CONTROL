<?php
/**
 * NotificationController — mark read, list, AJAX endpoint.
 */
class NotificationController
{
    public function markRead(array $params = []): void
    {
        $userId = (int)Session::get('user_id');
        NotificationService::markAllRead($userId);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        $role = Session::get('role', 'customer');
        $prefix = ($role === 'administrator') ? 'admin' : $role;
        header("Location: /banking-system/public/{$prefix}/dashboard");
        exit;
    }

    public function list(array $params = []): void
    {
        header('Content-Type: application/json');
        $userId = (int)Session::get('user_id');
        $notifs = NotificationService::getForUser($userId, 20);
        $count  = NotificationService::unreadCount($userId);
        echo json_encode(['notifications' => $notifs, 'unread_count' => $count]);
        exit;
    }

    /** AJAX: get unread count only */
    public function count(array $params = []): void
    {
        header('Content-Type: application/json');
        $userId = (int)Session::get('user_id');
        echo json_encode(['unread_count' => NotificationService::unreadCount($userId)]);
        exit;
    }
}
