<?php
class TellerController
{
    public function dashboard(array $params = []): void
    {
        $pdo = Database::getInstance();
        $stats = $pdo->query("SELECT
            (SELECT COUNT(*) FROM transactions WHERE status='pending') AS pending_count,
            (SELECT COUNT(*) FROM transactions WHERE DATE(created_at)=CURDATE() AND status='completed') AS today_completed,
            (SELECT COALESCE(SUM(amount),0) FROM transactions WHERE DATE(created_at)=CURDATE() AND status='completed') AS today_volume,
            (SELECT COUNT(*) FROM fraud_flags WHERE review_status='pending') AS fraud_pending,
            (SELECT COUNT(*) FROM support_tickets WHERE status='open') AS open_tickets
        ")->fetch();

        $pendingTxns = $pdo->query("
            SELECT t.*, fa.account_number AS from_acc, ta.account_number AS to_acc, u.full_name AS initiator_name
            FROM transactions t
            LEFT JOIN accounts fa ON fa.id=t.from_account_id
            LEFT JOIN accounts ta ON ta.id=t.to_account_id
            LEFT JOIN users u ON u.id=t.initiated_by
            WHERE t.status='pending' ORDER BY t.created_at ASC LIMIT 10
        ")->fetchAll();

        $recentTxns = $pdo->query("
            SELECT t.*, fa.account_number AS from_acc, ta.account_number AS to_acc
            FROM transactions t
            LEFT JOIN accounts fa ON fa.id=t.from_account_id
            LEFT JOIN accounts ta ON ta.id=t.to_account_id
            WHERE t.status='completed' ORDER BY t.created_at DESC LIMIT 8
        ")->fetchAll();

        $view = 'dashboard';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function searchAccounts(array $params = []): void
    {
        $query = trim($_GET['q'] ?? $_POST['q'] ?? '');
        $results = [];
        if (!empty($query)) {
            $pdo  = Database::getInstance();
            $stmt = $pdo->prepare("
                SELECT a.*, u.full_name, u.username, u.email, u.phone, u.is_active AS user_active
                FROM accounts a JOIN users u ON u.id=a.user_id
                WHERE a.account_number LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?
                LIMIT 20
            ");
            $like = '%'.$query.'%';
            $stmt->execute([$like, $like, $like, $like]);
            $results = $stmt->fetchAll();
        }
        $view = 'search';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function viewAccount(array $params = []): void
    {
        $accountId = (int)($params['id'] ?? 0);
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT a.*, u.full_name, u.username, u.email, u.phone, u.created_at AS user_created,
                   u.last_login_at, u.is_active AS user_active, u.login_failures
            FROM accounts a JOIN users u ON u.id=a.user_id WHERE a.id=?
        ");
        $stmt->execute([$accountId]);
        $viewAccount = $stmt->fetch();
        $history = TransactionService::getHistory($accountId, [], 1);
        $view = 'view_account';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function showDeposit(array $params = []): void
    {
        $pdo = Database::getInstance();
        $accounts = $pdo->query("
            SELECT a.*, u.full_name, u.username FROM accounts a JOIN users u ON u.id=a.user_id
            WHERE a.is_active=1 AND u.role_id=3 ORDER BY u.full_name ASC
        ")->fetchAll();
        $view = 'deposit';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function processDeposit(array $params = []): void
    {
        CsrfMiddleware::verify();
        $accountId  = (int)($_POST['account_id'] ?? 0);
        $amount     = (float)($_POST['amount']   ?? 0);
        $description = trim($_POST['description']?? 'Teller deposit');
        $result = TransactionService::deposit($accountId, $amount, $description);
        if ($result['success']) Session::flash('success', "Deposit of ₹".number_format($amount,2)." completed. Ref: {$result['reference']}");
        else Session::flash('error', $result['error']);
        header('Location: /banking-system/public/teller/deposit'); exit;
    }

    public function showWithdrawal(array $params = []): void
    {
        $pdo = Database::getInstance();
        $accounts = $pdo->query("
            SELECT a.*, u.full_name, u.username FROM accounts a JOIN users u ON u.id=a.user_id
            WHERE a.is_active=1 AND u.role_id=3 ORDER BY u.full_name ASC
        ")->fetchAll();
        $view = 'withdrawal';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function processWithdrawal(array $params = []): void
    {
        CsrfMiddleware::verify();
        $accountId   = (int)($_POST['account_id'] ?? 0);
        $amount      = (float)($_POST['amount']   ?? 0);
        $description = trim($_POST['description'] ?? 'Teller withdrawal');
        $result = TransactionService::withdrawal($accountId, $amount, $description);
        if ($result['success']) Session::flash('success', "Withdrawal of ₹".number_format($amount,2)." completed. Ref: {$result['reference']}");
        else Session::flash('error', $result['error']);
        header('Location: /banking-system/public/teller/withdrawal'); exit;
    }

    public function showApprovals(array $params = []): void
    {
        $pdo = Database::getInstance();
        $pendingTxns = $pdo->query("
            SELECT t.*, fa.account_number AS from_acc, ta.account_number AS to_acc,
                   uf.full_name AS from_holder, ut.full_name AS to_holder, i.full_name AS initiator_name
            FROM transactions t
            LEFT JOIN accounts fa ON fa.id=t.from_account_id
            LEFT JOIN accounts ta ON ta.id=t.to_account_id
            LEFT JOIN users uf ON uf.id=fa.user_id
            LEFT JOIN users ut ON ut.id=ta.user_id
            LEFT JOIN users i  ON i.id=t.initiated_by
            WHERE t.status='pending' ORDER BY t.created_at ASC
        ")->fetchAll();
        $view = 'approvals';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function processApproval(array $params = []): void
    {
        CsrfMiddleware::verify();
        $txnId  = (int)($params['id'] ?? 0);
        $result = TransactionService::approvePending($txnId, (int)Session::get('user_id'));
        if ($result['success']) Session::flash('success', 'Transaction approved and funds transferred.');
        else Session::flash('error', $result['error']);
        header('Location: /banking-system/public/teller/approvals'); exit;
    }

    public function processRejection(array $params = []): void
    {
        CsrfMiddleware::verify();
        $txnId  = (int)($params['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? 'Rejected by teller');
        $result = TransactionService::rejectPending($txnId, (int)Session::get('user_id'), $reason);
        if ($result['success']) Session::flash('success', 'Transaction rejected.');
        else Session::flash('error', $result['error']);
        header('Location: /banking-system/public/teller/approvals'); exit;
    }

    public function fraudAlerts(array $params = []): void
    {
        $pdo = Database::getInstance();
        $flags = $pdo->query("
            SELECT ff.*, t.reference_number, t.amount, t.created_at AS txn_date,
                   fa.account_number AS from_acc, ta.account_number AS to_acc
            FROM fraud_flags ff
            JOIN transactions t ON t.id=ff.transaction_id
            LEFT JOIN accounts fa ON fa.id=t.from_account_id
            LEFT JOIN accounts ta ON ta.id=t.to_account_id
            WHERE ff.review_status='pending'
            ORDER BY ff.risk_score DESC, ff.created_at DESC
        ")->fetchAll();
        $view = 'fraud_alerts';
        include BASE_PATH . '/views/layouts/main.php';
    }
}
