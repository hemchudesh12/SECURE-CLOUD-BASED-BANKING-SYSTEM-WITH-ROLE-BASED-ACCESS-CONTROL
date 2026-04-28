<?php
/**
 * LoanController — customer loan applications & admin approval.
 */
class LoanController
{
    private function getAccount(): ?array
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT a.* FROM accounts a WHERE a.user_id = ? AND a.is_active = 1"
        );
        $stmt->execute([Session::get('user_id')]);
        return $stmt->fetch() ?: null;
    }

    // ── CUSTOMER: list own loans + apply form ──────────────────
    public function customerIndex(array $params = []): void
    {
        $account = $this->getAccount();
        $userId  = (int)Session::get('user_id');
        $pdo     = Database::getInstance();

        $stmt = $pdo->prepare(
            "SELECT l.*, u.full_name AS approved_by_name
             FROM loans l
             LEFT JOIN users u ON u.id = l.approved_by
             WHERE l.user_id = ?
             ORDER BY l.requested_at DESC"
        );
        $stmt->execute([$userId]);
        $loans = $stmt->fetchAll();

        $view = 'loans';
        include BASE_PATH . '/views/layouts/main.php';
    }

    // ── CUSTOMER: submit loan application ──────────────────────
    public function apply(array $params = []): void
    {
        CsrfMiddleware::verify();
        $account = $this->getAccount();
        if (!$account) {
            Session::flash('error', 'No active account found.');
            header('Location: /banking-system/public/customer/loans');
            exit;
        }

        $amount   = (float)($_POST['amount']            ?? 0);
        $purpose  = trim($_POST['purpose']              ?? '');
        $months   = max(1, (int)($_POST['repayment_months'] ?? 12));
        $userId   = (int)Session::get('user_id');

        if ($amount <= 0) {
            Session::flash('error', 'Please enter a valid loan amount.');
            header('Location: /banking-system/public/customer/loans');
            exit;
        }
        if (empty($purpose)) {
            Session::flash('error', 'Please provide a loan purpose.');
            header('Location: /banking-system/public/customer/loans');
            exit;
        }
        if ($months < 1 || $months > 360) {
            Session::flash('error', 'Repayment period must be between 1 and 360 months.');
            header('Location: /banking-system/public/customer/loans');
            exit;
        }

        $pdo = Database::getInstance();

        // Prevent duplicate pending application
        $dup = $pdo->prepare("SELECT id FROM loans WHERE user_id = ? AND status = 'pending'");
        $dup->execute([$userId]);
        if ($dup->fetch()) {
            Session::flash('error', 'You already have a pending loan application. Please wait for it to be reviewed.');
            header('Location: /banking-system/public/customer/loans');
            exit;
        }

        $pdo->prepare(
            "INSERT INTO loans (user_id, account_id, amount, purpose, repayment_months)
             VALUES (?, ?, ?, ?, ?)"
        )->execute([$userId, $account['id'], $amount, $purpose, $months]);

        LogManager::log('LOAN_APPLIED', 'loan', 'success', ['amount' => $amount, 'purpose' => $purpose], $userId);
        Session::flash('success', 'Your loan request has been submitted and is pending admin approval.');
        header('Location: /banking-system/public/customer/loans');
        exit;
    }

    // ── ADMIN: list all loans with filter ─────────────────────
    public function adminIndex(array $params = []): void
    {
        $pdo    = Database::getInstance();
        $status = $_GET['status'] ?? '';
        $where  = '';
        $bind   = [];
        if (in_array($status, ['pending','approved','rejected'], true)) {
            $where = 'WHERE l.status = ?';
            $bind  = [$status];
        }

        $stmt = $pdo->prepare(
            "SELECT l.*, u.full_name, u.username, u.email,
                    a.account_number,
                    ab.full_name AS approved_by_name
             FROM loans l
             JOIN users u    ON u.id  = l.user_id
             JOIN accounts a ON a.id  = l.account_id
             LEFT JOIN users ab ON ab.id = l.approved_by
             $where
             ORDER BY l.status='pending' DESC, l.requested_at DESC"
        );
        $stmt->execute($bind);
        $loans = $stmt->fetchAll();

        $view = 'loans';
        include BASE_PATH . '/views/layouts/main.php';
    }

    // ── ADMIN: approve ─────────────────────────────────────────
    public function approve(array $params = []): void
    {
        CsrfMiddleware::verify();
        $loanId  = (int)($params['id'] ?? 0);
        $adminId = (int)Session::get('user_id');
        $pdo     = Database::getInstance();

        $stmt = $pdo->prepare("SELECT * FROM loans WHERE id = ? AND status = 'pending' FOR UPDATE");

        $pdo->beginTransaction();
        try {
            $stmt->execute([$loanId]);
            $loan = $stmt->fetch();
            if (!$loan) throw new \RuntimeException('Loan not found or already processed.');

            // Disburse amount to account
            $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?")
                ->execute([$loan['amount'], $loan['account_id']]);

            // Record transaction
            $ref = 'LOAN-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $pdo->prepare(
                "INSERT INTO transactions
                 (reference_number, to_account_id, amount, type, status, initiated_by, description)
                 VALUES (?,?,?,'loan_disbursement','completed',?,?)"
            )->execute([
                $ref, $loan['account_id'], $loan['amount'],
                $adminId, 'Loan disbursement — approved by admin #' . $adminId,
            ]);

            // Update loan status
            $pdo->prepare(
                "UPDATE loans SET status='approved', approved_at=NOW(), approved_by=? WHERE id=?"
            )->execute([$adminId, $loanId]);

            $pdo->commit();

            // Notify user via broadcast
            BroadcastService::toUser((int)$loan['user_id'], 'notification', [
                'title'   => 'Loan Approved! 🎉',
                'message' => '₹' . number_format($loan['amount'], 2) . ' has been credited to your account.',
                'type'    => 'success',
            ]);
            BroadcastService::toUser((int)$loan['user_id'], 'balance_updated', [
                'change' => +$loan['amount'],
            ]);

            LogManager::log('LOAN_APPROVED', 'loan', 'success', ['loan_id' => $loanId, 'amount' => $loan['amount']], $adminId);
            Session::flash('success', 'Loan #' . $loanId . ' approved and ₹' . number_format($loan['amount'], 2) . ' disbursed.');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            Session::flash('error', 'Error: ' . $e->getMessage());
        }

        header('Location: /banking-system/public/admin/loans');
        exit;
    }

    // ── ADMIN: reject ──────────────────────────────────────────
    public function reject(array $params = []): void
    {
        CsrfMiddleware::verify();
        $loanId  = (int)($params['id'] ?? 0);
        $adminId = (int)Session::get('user_id');
        $note    = trim($_POST['rejection_note'] ?? 'Application rejected by admin.');
        $pdo     = Database::getInstance();

        $stmt = $pdo->prepare("SELECT * FROM loans WHERE id = ? AND status = 'pending'");
        $stmt->execute([$loanId]);
        $loan = $stmt->fetch();

        if (!$loan) {
            Session::flash('error', 'Loan not found or already processed.');
            header('Location: /banking-system/public/admin/loans');
            exit;
        }

        $pdo->prepare(
            "UPDATE loans SET status='rejected', approved_at=NOW(), approved_by=?, rejection_note=? WHERE id=?"
        )->execute([$adminId, $note, $loanId]);

        BroadcastService::toUser((int)$loan['user_id'], 'notification', [
            'title'   => 'Loan Application Rejected',
            'message' => 'Your loan request of ₹' . number_format($loan['amount'], 2) . ' was not approved.',
            'type'    => 'error',
        ]);

        LogManager::log('LOAN_REJECTED', 'loan', 'failure', ['loan_id' => $loanId], $adminId);
        Session::flash('success', 'Loan #' . $loanId . ' has been rejected.');
        header('Location: /banking-system/public/admin/loans');
        exit;
    }
}
