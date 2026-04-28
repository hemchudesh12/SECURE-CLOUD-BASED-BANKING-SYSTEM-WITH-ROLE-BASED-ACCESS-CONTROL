<?php
/**
 * CustomerController — complete customer portal.
 */
class CustomerController
{
    private function getAccount(): ?array
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT a.*, u.username, u.email, u.full_name, u.phone
             FROM accounts a
             JOIN users u ON u.id = a.user_id
             WHERE a.user_id = ? AND a.is_active = 1"
        );
        $stmt->execute([Session::get('user_id')]);
        return $stmt->fetch() ?: null;
    }

    public function dashboard(array $params = []): void
    {
        $account = $this->getAccount();
        $recentTxns = [];
        if ($account) {
            $history    = TransactionService::getHistory((int)$account['id'], [], 1);
            $recentTxns = array_slice($history['rows'], 0, 8);
        }
        $unreadNotifs = NotificationService::unreadCount((int)Session::get('user_id'));
        LogManager::log('CUSTOMER_DASHBOARD_VIEW', 'account', 'success', [], $account['id'] ?? null);
        $view = 'dashboard';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function showTransfer(array $params = []): void
    {
        $account      = $this->getAccount();
        $userId       = (int)Session::get('user_id');
        $pdo          = Database::getInstance();
        $beneficiaries = $pdo->prepare(
            "SELECT * FROM beneficiaries WHERE user_id = ? AND is_active = 1 ORDER BY nickname ASC"
        );
        $beneficiaries->execute([$userId]);
        $savedBeneficiaries = $beneficiaries->fetchAll();

        $view = 'transfer';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function processTransfer(array $params = []): void
    {
        CsrfMiddleware::verify();
        $account = $this->getAccount();
        if (!$account) {
            Session::flash('error', 'Account not found.');
            header('Location: /banking-system/public/customer/transfer');
            exit;
        }
        if ($account['is_frozen']) {
            Session::flash('error', 'Your account is frozen. Please contact support.');
            header('Location: /banking-system/public/customer/transfer');
            exit;
        }

        $toAccountNumber = trim($_POST['to_account'] ?? '');
        $amount          = (float)($_POST['amount']   ?? 0);
        $description     = trim($_POST['description'] ?? '');
        $note            = trim($_POST['note']        ?? '');

        if (empty($toAccountNumber) || $amount <= 0) {
            Session::flash('error', 'Please provide a valid destination account and amount.');
            header('Location: /banking-system/public/customer/transfer');
            exit;
        }

        $result = TransactionService::transfer((int)$account['id'], $toAccountNumber, $amount, $description ?: $note);

        if ($result['success']) {
            $msg = ($result['needs_approval'] ?? false)
                ? "Transfer of ₹" . number_format($amount, 2) . " is pending approval. Ref: {$result['reference']}"
                : "Transfer of ₹" . number_format($amount, 2) . " completed successfully. Ref: {$result['reference']}";
            if (!empty($result['flagged'])) {
                $msg .= ' ⚠ This transaction has been flagged for review.';
            }
            Session::flash('success', $msg);
        } else {
            Session::flash('error', $result['error']);
        }

        header('Location: /banking-system/public/customer/transfer');
        exit;
    }

    public function history(array $params = []): void
    {
        $account = $this->getAccount();
        $filters = [
            'type'       => $_GET['type']       ?? '',
            'from_date'  => $_GET['from_date']  ?? '',
            'to_date'    => $_GET['to_date']    ?? '',
            'min_amount' => $_GET['min_amount'] ?? '',
            'max_amount' => $_GET['max_amount'] ?? '',
        ];
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $history = $account
            ? TransactionService::getHistory((int)$account['id'], $filters, $page)
            : ['rows' => [], 'total' => 0, 'pages' => 0, 'page' => 1];

        LogManager::log('HISTORY_VIEW', 'account', 'success', [], $account['id'] ?? null);
        $view = 'history';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function exportStatement(array $params = []): void
    {
        $account = $this->getAccount();
        if (!$account) exit;

        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT t.reference_number, t.type, t.amount, t.status, t.description, t.created_at,
                    fa.account_number AS from_account, ta.account_number AS to_account
             FROM transactions t
             LEFT JOIN accounts fa ON fa.id = t.from_account_id
             LEFT JOIN accounts ta ON ta.id = t.to_account_id
             WHERE t.from_account_id = ? OR t.to_account_id = ?
             ORDER BY t.created_at DESC"
        );
        $stmt->execute([$account['id'], $account['id']]);
        $rows = $stmt->fetchAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="statement-' . $account['account_number'] . '-' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Reference', 'Type', 'Amount (INR)', 'Status', 'From Account', 'To Account', 'Description', 'Date']);
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['reference_number'], $row['type'],
                number_format((float)$row['amount'], 2),
                $row['status'], $row['from_account'] ?? '', $row['to_account'] ?? '',
                $row['description'], $row['created_at'],
            ]);
        }
        fclose($out);
        LogManager::log('STATEMENT_EXPORT', 'account', 'success', [], $account['id']);
        exit;
    }

    public function showProfile(array $params = []): void
    {
        $account = $this->getAccount();
        $view    = 'profile';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function updateProfile(array $params = []): void
    {
        CsrfMiddleware::verify();
        $userId   = Session::get('user_id');
        $fullName = trim($_POST['full_name'] ?? '');
        $phone    = trim($_POST['phone']     ?? '');
        $email    = trim($_POST['email']     ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Valid email is required.');
            header('Location: /banking-system/public/customer/profile');
            exit;
        }

        $pdo = Database::getInstance();
        $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, email = ? WHERE id = ?")
            ->execute([$fullName, $phone, $email, $userId]);

        Session::set('full_name', $fullName);
        LogManager::log('PROFILE_UPDATE', 'user', 'success', [], $userId);
        Session::flash('success', 'Profile updated successfully.');
        header('Location: /banking-system/public/customer/profile');
        exit;
    }

    public function changePassword(array $params = []): void
    {
        CsrfMiddleware::verify();
        $userId  = Session::get('user_id');
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($new !== $confirm) {
            Session::flash('error', 'New passwords do not match.');
            header('Location: /banking-system/public/customer/profile');
            exit;
        }

        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current, $user['password_hash'])) {
            Session::flash('error', 'Current password is incorrect.');
            header('Location: /banking-system/public/customer/profile');
            exit;
        }

        $err = AuthService::validatePassword($new);
        if ($err) {
            Session::flash('error', $err);
            header('Location: /banking-system/public/customer/profile');
            exit;
        }

        $hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
        $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $userId]);

        LogManager::log('PASSWORD_CHANGED', 'user', 'success', [], $userId);
        Session::flash('success', 'Password changed successfully. Please log in again.');
        Session::destroy();
        header('Location: /banking-system/public/login');
        exit;
    }

    public function analytics(array $params = []): void
    {
        $account = $this->getAccount();
        $view    = 'analytics';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function analyticsData(array $params = []): void
    {
        header('Content-Type: application/json');
        $account = $this->getAccount();
        if (!$account) { echo json_encode(['error' => 'No account']); exit; }

        $pdo = Database::getInstance();
        $aid = (int)$account['id'];

        // Spending by category (type)
        $catStmt = $pdo->prepare(
            "SELECT type, COALESCE(SUM(amount),0) AS total
             FROM transactions
             WHERE from_account_id = ? AND status = 'completed'
               AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY type"
        );
        $catStmt->execute([$aid]);
        $cats = $catStmt->fetchAll();

        $categories = array_column($cats, 'type');
        $amounts    = array_map(fn($r) => (float)$r['total'], $cats);

        // Balance history — last 30 days running balance
        $days = 30;
        $dates    = [];
        $balances = [];
        $currentBal = (float)$account['balance'];

        // Get daily net changes
        $dayStmt = $pdo->prepare(
            "SELECT DATE(created_at) AS day,
                    SUM(CASE WHEN to_account_id = ? THEN amount ELSE 0 END) AS credits,
                    SUM(CASE WHEN from_account_id = ? THEN amount ELSE 0 END) AS debits
             FROM transactions
             WHERE (from_account_id = ? OR to_account_id = ?)
               AND status = 'completed'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(created_at)
             ORDER BY day ASC"
        );
        $dayStmt->execute([$aid, $aid, $aid, $aid, $days]);
        $dayData = $dayStmt->fetchAll(PDO::FETCH_KEY_PAIR + 0);
        $dayMap  = [];
        foreach ($dayStmt->fetchAll() as $r) {
            $dayMap[$r['day']] = (float)$r['credits'] - (float)$r['debits'];
        }

        // Rebuild from current balance backwards
        $runBal = $currentBal;
        $allDays = [];
        for ($i = 0; $i < $days; $i++) {
            $allDays[] = date('Y-m-d', strtotime("-{$i} days"));
        }
        $allDays = array_reverse($allDays);

        // Re-fetch properly
        $dayStmt2 = $pdo->prepare(
            "SELECT DATE(created_at) AS day,
                    SUM(CASE WHEN to_account_id = ? THEN amount ELSE 0 END) AS credits,
                    SUM(CASE WHEN from_account_id = ? THEN amount ELSE 0 END) AS debits
             FROM transactions
             WHERE (from_account_id = ? OR to_account_id = ?)
               AND status = 'completed'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(created_at)
             ORDER BY day ASC"
        );
        $dayStmt2->execute([$aid, $aid, $aid, $aid, $days]);
        $dayRows = $dayStmt2->fetchAll();
        $dayMap2 = [];
        foreach ($dayRows as $r) {
            $dayMap2[$r['day']] = (float)$r['credits'] - (float)$r['debits'];
        }

        $cumulative = 0;
        foreach ($allDays as $d) {
            $cumulative += $dayMap2[$d] ?? 0;
            $dates[]    = date('d M', strtotime($d));
            $balances[] = round($currentBal - $cumulative + ($dayMap2[$d] ?? 0), 2);
        }
        // Approximate: just show current balance adjusted
        $balances = array_fill(0, $days, $currentBal);
        foreach ($allDays as $idx => $d) {
            if (isset($dayMap2[$d])) {
                for ($j = $idx; $j < $days; $j++) {
                    $balances[$j] -= $dayMap2[$d];
                }
            }
        }
        $balances = array_map(fn($b) => max(0, round($b, 2)), $balances);

        echo json_encode([
            'categories' => $categories ?: ['No transactions'],
            'amounts'    => $amounts    ?: [0],
            'dates'      => $dates,
            'balances'   => $balances,
            'current_balance' => $currentBal,
        ]);
        exit;
    }

    public function scheduledPayments(array $params = []): void
    {
        $account = $this->getAccount();
        $userId  = (int)Session::get('user_id');
        $pdo     = Database::getInstance();

        $stmt = $pdo->prepare(
            "SELECT sp.*, a1.account_number AS from_acc, a2.account_number AS to_acc
             FROM scheduled_payments sp
             JOIN accounts a1 ON a1.id = sp.from_account_id
             JOIN accounts a2 ON a2.id = sp.to_account_id
             WHERE sp.user_id = ?
             ORDER BY sp.next_run_at ASC"
        );
        $stmt->execute([$userId]);
        $scheduledList = $stmt->fetchAll();
        $view = 'scheduled';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function addScheduled(array $params = []): void
    {
        CsrfMiddleware::verify();
        $account = $this->getAccount();
        if (!$account) {
            Session::flash('error', 'Account not found.');
            header('Location: /banking-system/public/customer/scheduled');
            exit;
        }

        $toAccNum   = trim($_POST['to_account'] ?? '');
        $amount     = (float)($_POST['amount']  ?? 0);
        $desc       = trim($_POST['description']?? '');
        $frequency  = $_POST['frequency']        ?? 'once';
        $nextRunAt  = $_POST['next_run_at']      ?? '';
        $endDate    = $_POST['end_date']         ?? null;
        $userId     = (int)Session::get('user_id');

        if ($amount <= 0 || empty($toAccNum) || empty($nextRunAt)) {
            Session::flash('error', 'Please fill in all required fields.');
            header('Location: /banking-system/public/customer/scheduled');
            exit;
        }

        $pdo   = Database::getInstance();
        $dstAcc = $pdo->prepare("SELECT id FROM accounts WHERE account_number = ? AND is_active = 1");
        $dstAcc->execute([$toAccNum]);
        $dst = $dstAcc->fetch();

        if (!$dst) {
            Session::flash('error', 'Destination account not found.');
            header('Location: /banking-system/public/customer/scheduled');
            exit;
        }

        $pdo->prepare(
            "INSERT INTO scheduled_payments (user_id, from_account_id, to_account_id, amount, description, frequency, next_run_at, end_date)
             VALUES (?,?,?,?,?,?,?,?)"
        )->execute([$userId, $account['id'], $dst['id'], $amount, $desc, $frequency, $nextRunAt, $endDate ?: null]);

        LogManager::log('SCHEDULED_PAYMENT_CREATED', 'scheduled', 'success', ['amount' => $amount, 'frequency' => $frequency]);
        Session::flash('success', 'Scheduled payment created successfully.');
        header('Location: /banking-system/public/customer/scheduled');
        exit;
    }

    public function cancelScheduled(array $params = []): void
    {
        CsrfMiddleware::verify();
        $id     = (int)($params['id'] ?? 0);
        $userId = (int)Session::get('user_id');

        $pdo = Database::getInstance();
        $pdo->prepare("UPDATE scheduled_payments SET status = 'completed' WHERE id = ? AND user_id = ?")
            ->execute([$id, $userId]);

        LogManager::log('SCHEDULED_PAYMENT_CANCELLED', 'scheduled', 'success', ['id' => $id]);
        Session::flash('success', 'Scheduled payment cancelled.');
        header('Location: /banking-system/public/customer/scheduled');
        exit;
    }

    /** AJAX: get current balance */
    public function apiBalance(array $params = []): void
    {
        header('Content-Type: application/json');
        $account = $this->getAccount();
        if (!$account) { echo json_encode(['error' => 'Not found']); exit; }
        echo json_encode([
            'balance'        => (float)$account['balance'],
            'account_number' => $account['account_number'],
        ]);
        exit;
    }
}
