<?php
/**
 * SupportController — support tickets and replies.
 */
class SupportController
{
    public function customerIndex(array $params = []): void
    {
        $userId = (int)Session::get('user_id');
        $pdo    = Database::getInstance();
        $stmt   = $pdo->prepare(
            "SELECT st.*, u.full_name AS assigned_name
             FROM support_tickets st
             LEFT JOIN users u ON u.id = st.assigned_to
             WHERE st.user_id = ?
             ORDER BY st.created_at DESC"
        );
        $stmt->execute([$userId]);
        $tickets = $stmt->fetchAll();
        $view = 'support';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function create(array $params = []): void
    {
        CsrfMiddleware::verify();
        $userId   = (int)Session::get('user_id');
        $subject  = trim($_POST['subject']  ?? '');
        $message  = trim($_POST['message']  ?? '');
        $priority = $_POST['priority'] ?? 'medium';

        if (empty($subject) || empty($message)) {
            Session::flash('error', 'Subject and message are required.');
            header('Location: /banking-system/public/customer/support');
            exit;
        }

        $pdo = Database::getInstance();
        $pdo->prepare(
            "INSERT INTO support_tickets (user_id, subject, message, priority) VALUES (?,?,?,?)"
        )->execute([$userId, $subject, $message, $priority]);

        $ticketId = $pdo->lastInsertId();
        LogManager::log('SUPPORT_TICKET_CREATED', 'support', 'success', ['ticket_id' => $ticketId]);

        // Notify admins and tellers
        BroadcastService::toRole('administrator', 'notification', [
            'title'   => 'New Support Ticket',
            'message' => "Ticket #{$ticketId}: {$subject}",
            'type'    => 'info',
        ]);
        BroadcastService::toRole('teller', 'notification', [
            'title'   => 'New Support Ticket',
            'message' => "Ticket #{$ticketId}: {$subject}",
            'type'    => 'info',
        ]);

        Session::flash('success', "Support ticket #{$ticketId} created successfully.");
        header('Location: /banking-system/public/customer/support');
        exit;
    }

    public function viewTicket(array $params = []): void
    {
        $ticketId = (int)($params['id'] ?? 0);
        $userId   = (int)Session::get('user_id');
        $role     = Session::get('role');
        $pdo      = Database::getInstance();

        $stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE id = ?");
        $stmt->execute([$ticketId]);
        $ticket = $stmt->fetch();

        // Customers can only view their own tickets
        if (!$ticket || ($role === 'customer' && (int)$ticket['user_id'] !== $userId)) {
            http_response_code(403);
            Session::flash('error', 'Ticket not found.');
            header('Location: /banking-system/public/customer/support');
            exit;
        }

        $repliesStmt = $pdo->prepare(
            "SELECT sr.*, u.full_name, u.username, r.name AS role_name
             FROM support_replies sr
             JOIN users u ON u.id = sr.user_id
             JOIN roles r ON r.id = u.role_id
             WHERE sr.ticket_id = ?
             ORDER BY sr.created_at ASC"
        );
        $repliesStmt->execute([$ticketId]);
        $replies = $repliesStmt->fetchAll();

        $view = 'support_ticket';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function reply(array $params = []): void
    {
        CsrfMiddleware::verify();
        $ticketId = (int)($params['id'] ?? 0);
        $userId   = (int)Session::get('user_id');
        $message  = trim($_POST['message'] ?? '');
        $role     = Session::get('role');

        if (empty($message)) {
            Session::flash('error', 'Reply message cannot be empty.');
            header("Location: /banking-system/public/support/ticket/{$ticketId}");
            exit;
        }

        $pdo = Database::getInstance();

        // Verify access
        $stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE id = ?");
        $stmt->execute([$ticketId]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            Session::flash('error', 'Ticket not found.');
            header('Location: /banking-system/public/customer/support');
            exit;
        }

        $pdo->prepare(
            "INSERT INTO support_replies (ticket_id, user_id, message) VALUES (?,?,?)"
        )->execute([$ticketId, $userId, $message]);

        // Update ticket status if staff replying
        if (in_array($role, ['administrator', 'teller'])) {
            $pdo->prepare(
                "UPDATE support_tickets SET status = 'in_progress', assigned_to = ?, updated_at = NOW() WHERE id = ?"
            )->execute([$userId, $ticketId]);

            // Notify customer
            BroadcastService::toUser((int)$ticket['user_id'], 'support_reply', [
                'ticket_id' => $ticketId,
                'from'      => Session::get('full_name'),
                'message'   => substr($message, 0, 100),
            ]);
        }

        LogManager::log('SUPPORT_REPLY', 'support', 'success', ['ticket_id' => $ticketId]);
        Session::flash('success', 'Reply posted.');

        $prefix = ($role === 'administrator') ? 'admin' : ($role === 'teller' ? 'teller' : 'customer');
        header("Location: /banking-system/public/support/ticket/{$ticketId}");
        exit;
    }

    public function updateStatus(array $params = []): void
    {
        CsrfMiddleware::verify();
        $ticketId = (int)($params['id'] ?? 0);
        $status   = $_POST['status'] ?? 'open';

        $allowed = ['open', 'in_progress', 'resolved', 'closed'];
        if (!in_array($status, $allowed)) {
            Session::flash('error', 'Invalid status.');
            header('Location: /banking-system/public/admin/support');
            exit;
        }

        $pdo = Database::getInstance();
        $pdo->prepare("UPDATE support_tickets SET status = ?, updated_at = NOW() WHERE id = ?")
            ->execute([$status, $ticketId]);

        LogManager::log('SUPPORT_STATUS_UPDATED', 'support', 'success', ['ticket_id' => $ticketId, 'status' => $status]);
        Session::flash('success', 'Ticket status updated.');
        header('Location: /banking-system/public/admin/support');
        exit;
    }

    /** Admin: list all tickets */
    public function adminIndex(array $params = []): void
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->query(
            "SELECT st.*, u.full_name AS customer_name, u.username AS customer_username,
                    a.full_name AS assigned_name
             FROM support_tickets st
             JOIN users u ON u.id = st.user_id
             LEFT JOIN users a ON a.id = st.assigned_to
             ORDER BY
                 FIELD(st.priority,'urgent','high','medium','low'),
                 st.created_at DESC"
        );
        $tickets = $stmt->fetchAll();
        $view = 'support_tickets';
        include BASE_PATH . '/views/layouts/main.php';
    }
}
