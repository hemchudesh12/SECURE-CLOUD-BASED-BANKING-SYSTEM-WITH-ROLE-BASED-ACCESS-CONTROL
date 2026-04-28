<?php
class AdminController
{
    public function dashboard(array $params = []): void
    {
        $pdo = Database::getInstance();
        $stats = $pdo->query("SELECT
            (SELECT COUNT(*) FROM users) AS total_users,
            (SELECT COUNT(*) FROM users WHERE is_active=1) AS active_users,
            (SELECT COUNT(*) FROM accounts) AS total_accounts,
            (SELECT COUNT(*) FROM accounts WHERE is_active=1) AS active_accounts,
            (SELECT COUNT(*) FROM transactions WHERE DATE(created_at)=CURDATE()) AS today_txns,
            (SELECT COUNT(*) FROM transactions WHERE status='pending') AS pending_txns,
            (SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='transfer' AND DATE(created_at)=CURDATE() AND status='completed') AS today_transfer_volume,
            (SELECT COALESCE(SUM(balance),0) FROM accounts WHERE is_active=1) AS total_deposits,
            (SELECT COUNT(*) FROM audit_logs WHERE outcome='failure' AND created_at >= DATE_SUB(NOW(),INTERVAL 24 HOUR)) AS failures_24h,
            (SELECT COUNT(*) FROM users WHERE DATE(created_at) >= DATE_SUB(CURDATE(),INTERVAL 7 DAY)) AS new_users_week,
            (SELECT COUNT(*) FROM fraud_flags WHERE review_status='pending') AS fraud_pending,
            (SELECT COUNT(*) FROM support_tickets WHERE status='open') AS open_tickets
        ")->fetch();

        $auditLogs = $pdo->query("SELECT * FROM audit_logs ORDER BY id DESC LIMIT 10")->fetchAll();
        $chartData = $pdo->query("
            SELECT DATE(created_at) AS day, type, COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS vol
            FROM transactions WHERE created_at >= DATE_SUB(CURDATE(),INTERVAL 7 DAY) AND status IN ('completed','pending')
            GROUP BY DATE(created_at),type ORDER BY day ASC
        ")->fetchAll();
        $topAccounts = $pdo->query("
            SELECT a.account_number,a.balance,a.account_type,u.full_name,u.username
            FROM accounts a JOIN users u ON u.id=a.user_id WHERE a.is_active=1 ORDER BY a.balance DESC LIMIT 5
        ")->fetchAll();
        $recentUsers = $pdo->query("
            SELECT u.username,u.full_name,u.email,u.created_at,r.name AS role_name
            FROM users u JOIN roles r ON r.id=u.role_id ORDER BY u.created_at DESC LIMIT 5
        ")->fetchAll();
        $roleStats = $pdo->query("
            SELECT r.name, COUNT(u.id) AS count FROM roles r LEFT JOIN users u ON u.role_id=r.id GROUP BY r.id,r.name
        ")->fetchAll();

        $view = 'dashboard';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function listUsers(array $params = []): void
    {
        $pdo  = Database::getInstance();
        $users = $pdo->query("
            SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id=u.role_id ORDER BY u.created_at DESC
        ")->fetchAll();
        $roles = RbacService::getAllRoles();
        $view  = 'users';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function showCreateUser(array $params = []): void
    {
        $roles = RbacService::getAllRoles();
        $view  = 'create_user';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function createUser(array $params = []): void
    {
        $username = trim($_POST['username']  ?? '');
        $email    = trim($_POST['email']     ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password']       ?? '';
        $roleId   = (int)($_POST['role_id']  ?? 3);

        $errors = [];
        if (empty($username))   $errors[] = 'Username required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
        if (empty($fullName))   $errors[] = 'Full name required.';
        $pwdErr = AuthService::validatePassword($password);
        if ($pwdErr) $errors[] = $pwdErr;

        if ($errors) { foreach ($errors as $e) Session::flash('error', $e); header('Location: /banking-system/public/admin/users/create'); exit; }

        $pdo  = Database::getInstance();
        $chk  = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $chk->execute([$username, $email]);
        if ($chk->fetch()) { Session::flash('error', 'Username or email already exists.'); header('Location: /banking-system/public/admin/users/create'); exit; }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $ins  = $pdo->prepare("INSERT INTO users (username,email,password_hash,full_name,role_id,is_active,email_verified) VALUES(?,?,?,?,?,1,1)");
        $ins->execute([$username, $email, $hash, $fullName, $roleId]);
        $newId = (int)$pdo->lastInsertId();

        if ($roleId == 3) {
            $initialDeposit = (float)($_POST['initial_deposit'] ?? 0);
            if ($initialDeposit < 1000) {
                Session::flash('error', 'Customer accounts require a minimum initial deposit of ₹1,000.');
                header('Location: /banking-system/public/admin/users/create'); exit;
            }
            $accNum = 'ACC-'.date('Y').str_pad($newId, 6, '0', STR_PAD_LEFT);
            $pdo->prepare("INSERT INTO accounts (user_id,account_number,account_type,balance) VALUES(?,?,'savings',?)")->execute([$newId, $accNum, $initialDeposit]);
        }

        LogManager::log('ACCOUNT_CREATED', 'user', 'success', ['username' => $username, 'role_id' => $roleId], $newId);
        Session::flash('success', "User '{$username}' created.");
        header('Location: /banking-system/public/admin/users'); exit;
    }

    public function editUser(array $params = []): void
    {
        $userId = (int)($params['id'] ?? 0);
        $pdo    = Database::getInstance();
        $stmt   = $pdo->prepare("SELECT u.*,r.name AS role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=?");
        $stmt->execute([$userId]);
        $editUser = $stmt->fetch();
        $roles    = RbacService::getAllRoles();
        $view     = 'edit_user';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function updateUser(array $params = []): void
    {
        $userId   = (int)($params['id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email']     ?? '');
        $roleId   = (int)($_POST['role_id']  ?? 0);

        if ($userId === (int)Session::get('user_id') && $roleId !== (int)Session::get('role_id')) {
            Session::flash('error', 'You cannot change your own role.');
            header('Location: /banking-system/public/admin/users/'.$userId.'/edit'); exit;
        }

        $pdo = Database::getInstance();
        $pdo->prepare("UPDATE users SET full_name=?,email=?,role_id=? WHERE id=?")->execute([$fullName, $email, $roleId, $userId]);
        LogManager::log('USER_UPDATED', 'user', 'success', [], $userId);
        Session::flash('success', 'User updated.');
        header('Location: /banking-system/public/admin/users'); exit;
    }

    public function toggleUser(array $params = []): void
    {
        $userId = (int)($params['id'] ?? $_POST['user_id'] ?? 0);
        if ($userId === (int)Session::get('user_id')) { Session::flash('error', 'Cannot deactivate own account.'); header('Location: /banking-system/public/admin/users'); exit; }
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id=?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user) { Session::flash('error', 'User not found.'); header('Location: /banking-system/public/admin/users'); exit; }
        $newStatus = $user['is_active'] ? 0 : 1;
        $pdo->prepare("UPDATE users SET is_active=? WHERE id=?")->execute([$newStatus, $userId]);
        LogManager::log($newStatus ? 'ACCOUNT_ACTIVATED' : 'ACCOUNT_DEACTIVATED', 'user', 'success', [], $userId);
        Session::flash('success', 'User status updated.');
        header('Location: /banking-system/public/admin/users'); exit;
    }

    public function deleteUser(array $params = []): void
    {
        CsrfMiddleware::verify();
        $userId = (int)($params['id'] ?? 0);
        if ($userId === (int)Session::get('user_id')) {
            Session::flash('error', 'You cannot delete your own account.');
            header('Location: /banking-system/public/admin/users'); exit;
        }
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id=?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user) { Session::flash('error', 'User not found.'); header('Location: /banking-system/public/admin/users'); exit; }
        // Soft-delete: deactivate instead of hard delete to preserve audit trail
        $pdo->prepare("UPDATE users SET is_active=0, username=CONCAT(username,'_deleted_',id), email=CONCAT('deleted_',id,'@removed.local') WHERE id=?")->execute([$userId]);
        LogManager::log('ACCOUNT_DELETED', 'user', 'success', ['original_username' => $user['username']], $userId);
        Session::flash('success', "User '{$user['username']}' deleted.");
        header('Location: /banking-system/public/admin/users'); exit;
    }

    public function resetUserPassword(array $params = []): void
    {
        CsrfMiddleware::verify();
        $userId = (int)($params['id'] ?? 0);
        $pdo    = Database::getInstance();
        $stmt   = $pdo->prepare("SELECT username, email FROM users WHERE id=? AND is_active=1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user) { Session::flash('error', 'User not found or inactive.'); header('Location: /banking-system/public/admin/users'); exit; }
        // Generate temp password
        $tempPass = 'Reset@' . random_int(10000, 99999);
        $hash     = password_hash($tempPass, PASSWORD_BCRYPT, ['cost' => 12]);
        $pdo->prepare("UPDATE users SET password_hash=?, login_failures=0, locked_until=NULL WHERE id=?")->execute([$hash, $userId]);
        LogManager::log('PASSWORD_RESET_ADMIN', 'user', 'success', ['target_user_id' => $userId]);
        Session::flash('success', "Password for '{$user['username']}' reset to: <strong>{$tempPass}</strong> — share securely.");
        header('Location: /banking-system/public/admin/users'); exit;
    }

    public function freezeAccount(array $params = []): void
    {
        CsrfMiddleware::verify();
        $accountId = (int)($params['id'] ?? 0);
        $reason    = trim($_POST['reason'] ?? 'Administrative freeze');
        $pdo       = Database::getInstance();
        $pdo->prepare("UPDATE accounts SET is_frozen=1, freeze_reason=? WHERE id=?")->execute([$reason, $accountId]);

        $acc = $pdo->prepare("SELECT user_id, account_number FROM accounts WHERE id=?");
        $acc->execute([$accountId]);
        $accRow = $acc->fetch();
        if ($accRow) {
            BroadcastService::toUser((int)$accRow['user_id'], 'account_frozen', [
                'account_number' => $accRow['account_number'], 'reason' => $reason,
            ]);
        }
        LogManager::log('ACCOUNT_FROZEN', 'account', 'success', ['account_id' => $accountId, 'reason' => $reason]);
        Session::flash('success', 'Account frozen.');
        header('Location: /banking-system/public/admin/users'); exit;
    }

    public function unfreezeAccount(array $params = []): void
    {
        CsrfMiddleware::verify();
        $accountId = (int)($params['id'] ?? 0);
        $pdo = Database::getInstance();
        $pdo->prepare("UPDATE accounts SET is_frozen=0, freeze_reason=NULL WHERE id=?")->execute([$accountId]);
        $acc = $pdo->prepare("SELECT user_id, account_number FROM accounts WHERE id=?");
        $acc->execute([$accountId]);
        $accRow = $acc->fetch();
        if ($accRow) {
            BroadcastService::toUser((int)$accRow['user_id'], 'account_unfrozen', ['account_number' => $accRow['account_number']]);
        }
        LogManager::log('ACCOUNT_UNFROZEN', 'account', 'success', ['account_id' => $accountId]);
        Session::flash('success', 'Account unfrozen.');
        header('Location: /banking-system/public/admin/users'); exit;
    }

    public function reverseTransaction(array $params = []): void
    {
        CsrfMiddleware::verify();
        $txnId  = (int)($params['id'] ?? 0);
        $result = TransactionService::reverseTransaction($txnId, (int)Session::get('user_id'));
        if ($result['success']) Session::flash('success', "Transaction reversed. Ref: {$result['reversal_reference']}");
        else Session::flash('error', $result['error']);
        header('Location: /banking-system/public/admin/dashboard'); exit;
    }

    public function listRoles(array $params = []): void
    {
        $roles       = RbacService::getAllRoles();
        $permissions = RbacService::getAllPermissions();
        $view        = 'roles';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function saveRole(array $params = []): void
    {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if (empty($name)) { Session::flash('error', 'Role name required.'); header('Location: /banking-system/public/admin/roles'); exit; }
        $pdo = Database::getInstance();
        $pdo->prepare("INSERT INTO roles (name,description) VALUES(?,?) ON DUPLICATE KEY UPDATE description=?")->execute([$name, $desc, $desc]);
        Session::flash('success', 'Role saved.');
        header('Location: /banking-system/public/admin/roles'); exit;
    }

    public function editPermissions(array $params = []): void
    {
        $roleId = (int)($params['id'] ?? 0);
        $pdo    = Database::getInstance();
        $role   = $pdo->prepare("SELECT * FROM roles WHERE id=?"); $role->execute([$roleId]);
        $editRole    = $role->fetch();
        $permissions = RbacService::getAllPermissions();
        $assigned    = RbacService::getRolePermissionIds($roleId);
        $view        = 'edit_permissions';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function savePermissions(array $params = []): void
    {
        $roleId  = (int)($params['id'] ?? 0);
        $permIds = array_map('intval', $_POST['permissions'] ?? []);
        RbacService::saveRolePermissions($roleId, $permIds);
        Session::flash('success', 'Permissions updated.');
        header('Location: /banking-system/public/admin/roles'); exit;
    }

    public function auditLog(array $params = []): void
    {
        $pdo    = Database::getInstance();
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = 25;
        $offset = ($page - 1) * $limit;
        $where  = "WHERE 1=1"; $p = [];
        if (!empty($_GET['action']))    { $where .= " AND action LIKE ?";        $p[] = '%'.$_GET['action'].'%'; }
        if (!empty($_GET['username']))  { $where .= " AND username LIKE ?";      $p[] = '%'.$_GET['username'].'%'; }
        if (!empty($_GET['outcome']))   { $where .= " AND outcome = ?";          $p[] = $_GET['outcome']; }
        if (!empty($_GET['from_date'])) { $where .= " AND DATE(created_at) >= ?";$p[] = $_GET['from_date']; }
        if (!empty($_GET['to_date']))   { $where .= " AND DATE(created_at) <= ?";$p[] = $_GET['to_date']; }
        if (!empty($_GET['ip']))        { $where .= " AND source_ip LIKE ?";     $p[] = '%'.$_GET['ip'].'%'; }
        $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs $where"); $cntStmt->execute($p);
        $total = (int)$cntStmt->fetchColumn();
        $stmt  = $pdo->prepare("SELECT * FROM audit_logs $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($p);
        $logs  = $stmt->fetchAll();
        $pages = (int)ceil($total / $limit);
        $view  = 'audit_log';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function exportAudit(array $params = []): void
    {
        $pdo  = Database::getInstance();
        $rows = $pdo->query("SELECT id,user_id,username,action,entity_type,source_ip,outcome,created_at FROM audit_logs ORDER BY id DESC")->fetchAll();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="audit-log-'.date('Ymd-His').'.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','User ID','Username','Action','Entity','IP','Outcome','Timestamp']);
        foreach ($rows as $row) fputcsv($out, array_values($row));
        fclose($out); exit;
    }

    public function integrityCheck(array $params = []): void
    {
        $result = LogManager::verifyChain();
        $view   = 'integrity';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function systemHealth(array $params = []): void
    {
        $pdo   = Database::getInstance();
        $stats = $pdo->query("SELECT
            (SELECT COUNT(*) FROM users WHERE is_active=1) AS active_users,
            (SELECT COUNT(*) FROM accounts WHERE is_active=1) AS active_accounts,
            (SELECT COUNT(*) FROM transactions WHERE status='pending') AS pending_txns,
            (SELECT COUNT(*) FROM audit_logs WHERE outcome='failure' AND created_at >= DATE_SUB(NOW(),INTERVAL 1 HOUR)) AS recent_failures,
            (SELECT COUNT(*) FROM audit_logs) AS total_audit_entries,
            (SELECT COUNT(*) FROM ws_sessions WHERE last_ping >= DATE_SUB(NOW(),INTERVAL 2 MINUTE)) AS ws_connected,
            (SELECT COUNT(*) FROM notifications WHERE is_read=0) AS unread_notifs,
            (SELECT COUNT(*) FROM fraud_flags WHERE review_status='pending') AS fraud_pending
        ")->fetch();
        $view = 'system_health';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function reports(array $params = []): void
    {
        $pdo = Database::getInstance();
        $dailyVolume = $pdo->query("
            SELECT DATE(created_at) AS day, SUM(amount) AS total, COUNT(*) AS count, type
            FROM transactions WHERE created_at >= DATE_SUB(NOW(),INTERVAL 30 DAY) AND status='completed'
            GROUP BY DATE(created_at),type ORDER BY day DESC
        ")->fetchAll();
        $view = 'reports';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function fraudDashboard(array $params = []): void
    {
        $pdo = Database::getInstance();
        $flags = $pdo->query("
            SELECT ff.*, t.reference_number, t.amount, t.created_at AS txn_date,
                   fa.account_number AS from_acc, ta.account_number AS to_acc,
                   uf.full_name AS from_holder, u.full_name AS reviewed_by_name
            FROM fraud_flags ff
            JOIN transactions t ON t.id=ff.transaction_id
            LEFT JOIN accounts fa ON fa.id=t.from_account_id
            LEFT JOIN accounts ta ON ta.id=t.to_account_id
            LEFT JOIN users uf ON uf.id=fa.user_id
            LEFT JOIN users u  ON u.id=ff.reviewed_by
            ORDER BY ff.review_status='pending' DESC, ff.risk_score DESC, ff.created_at DESC
        ")->fetchAll();
        $view = 'fraud_dashboard';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function reviewFraud(array $params = []): void
    {
        CsrfMiddleware::verify();
        $flagId = (int)($params['id'] ?? 0);
        $status = $_POST['review_status'] ?? 'cleared';
        $notes  = trim($_POST['notes'] ?? '');
        $pdo    = Database::getInstance();
        $pdo->prepare("UPDATE fraud_flags SET review_status=?,reviewed_by=?,reviewed_at=NOW(),notes=? WHERE id=?")
            ->execute([$status, Session::get('user_id'), $notes, $flagId]);
        LogManager::log('FRAUD_REVIEWED', 'fraud', 'success', ['flag_id' => $flagId, 'status' => $status]);
        Session::flash('success', 'Fraud flag updated.');
        header('Location: /banking-system/public/admin/fraud'); exit;
    }

    public function liveMonitor(array $params = []): void
    {
        $pdo = Database::getInstance();
        $recentTxns = $pdo->query("
            SELECT t.*, fa.account_number AS from_acc, ta.account_number AS to_acc, u.full_name AS initiator
            FROM transactions t
            LEFT JOIN accounts fa ON fa.id=t.from_account_id
            LEFT JOIN accounts ta ON ta.id=t.to_account_id
            LEFT JOIN users u ON u.id=t.initiated_by
            ORDER BY t.created_at DESC LIMIT 50
        ")->fetchAll();
        $stats = $pdo->query("
            SELECT COUNT(*) AS today_count, COALESCE(SUM(amount),0) AS today_vol
            FROM transactions WHERE DATE(created_at)=CURDATE() AND status='completed'
        ")->fetch();
        $view = 'live_monitor';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function scheduledPayments(array $params = []): void
    {
        $pdo = Database::getInstance();
        $payments = $pdo->query("
            SELECT sp.*, u.full_name, u.username, a1.account_number AS from_acc, a2.account_number AS to_acc
            FROM scheduled_payments sp
            JOIN users u ON u.id=sp.user_id
            JOIN accounts a1 ON a1.id=sp.from_account_id
            JOIN accounts a2 ON a2.id=sp.to_account_id
            ORDER BY sp.next_run_at ASC
        ")->fetchAll();
        $view = 'scheduled_payments';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function broadcastAlert(array $params = []): void
    {
        CsrfMiddleware::verify();
        $message  = trim($_POST['message']  ?? '');
        $severity = $_POST['severity']       ?? 'info';
        if (empty($message)) { Session::flash('error', 'Message required.'); header('Location: /banking-system/public/admin/dashboard'); exit; }
        BroadcastService::toAll('system_alert', ['message' => $message, 'severity' => $severity]);
        LogManager::log('SYSTEM_ALERT_BROADCAST', 'system', 'success', ['message' => $message]);
        Session::flash('success', 'System alert broadcast to all users.');
        header('Location: /banking-system/public/admin/dashboard'); exit;
    }

    /** AJAX: poll ws_outbox for AJAX fallback clients */
    public function wsPoll(array $params = []): void
    {
        header('Content-Type: application/json');
        $userId   = (int)Session::get('user_id');
        $pdo      = Database::getInstance();
        $stmt     = $pdo->prepare("SELECT id, event, data FROM ws_outbox WHERE user_id=? AND sent=0 ORDER BY id ASC LIMIT 20");
        $stmt->execute([$userId]);
        $messages = $stmt->fetchAll();
        if (!empty($messages)) {
            $ids = implode(',', array_column($messages, 'id'));
            $pdo->exec("UPDATE ws_outbox SET sent=1 WHERE id IN ($ids)");
        }
        echo json_encode(['messages' => $messages]);
        exit;
    }
}
