<?php
/**
 * TransactionService — atomic fund transfers, deposits, withdrawals.
 * Includes: fraud detection, broadcast, daily limit, approval workflow.
 */
class TransactionService
{
    public static function generateReference(string $prefix = 'TXN'): string
    {
        return $prefix . '-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    /**
     * Customer self-service transfer (account number as destination).
     */
    public static function transfer(int $fromAccountId, string $toAccountNumber, float $amount, string $description = ''): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'error' => 'Transfer amount must be greater than zero.'];
        }

        $pdo = Database::getInstance();

        $destStmt = $pdo->prepare("SELECT * FROM accounts WHERE account_number = ? AND is_active = 1");
        $destStmt->execute([trim($toAccountNumber)]);
        $destAccount = $destStmt->fetch();

        if (!$destAccount) {
            return ['success' => false, 'error' => 'Destination account not found or inactive.'];
        }
        if ($destAccount['id'] === $fromAccountId) {
            return ['success' => false, 'error' => 'Cannot transfer to the same account.'];
        }

        return self::transferById($fromAccountId, (int)$destAccount['id'], $amount, (int)Session::get('user_id'), $description);
    }

    /**
     * Internal transfer by account IDs (used by scheduler & teller approval).
     */
    public static function transferById(int $fromAccountId, int $toAccountId, float $amount, int $initiatedBy, string $description = ''): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'error' => 'Amount must be positive.'];
        }

        $pdo = Database::getInstance();

        // Daily limit check
        $limitCheck = self::checkDailyLimit($fromAccountId, $amount);
        if (!$limitCheck['ok']) {
            return ['success' => false, 'error' => $limitCheck['error']];
        }

        // Fraud pre-check
        $fraud = FraudDetectionService::check($fromAccountId, $amount, $initiatedBy);

        // Transfers are now instant (no approval required)
        $needsApproval = false;

        $reference = self::generateReference();
        $pdo->beginTransaction();
        try {
            $srcStmt = $pdo->prepare("SELECT * FROM accounts WHERE id = ? AND is_active = 1 FOR UPDATE");
            $srcStmt->execute([$fromAccountId]);
            $srcAcc = $srcStmt->fetch();
            if (!$srcAcc)                            throw new \RuntimeException('Source account not found or inactive.');
            if ((int)($srcAcc['is_frozen'] ?? 0))    throw new \RuntimeException('Account is frozen. Contact support.');
            if ((float)$srcAcc['balance'] < $amount) throw new \RuntimeException('Insufficient funds.');

            $dstStmt = $pdo->prepare("SELECT * FROM accounts WHERE id = ? AND is_active = 1 FOR UPDATE");
            $dstStmt->execute([$toAccountId]);
            $dstAcc = $dstStmt->fetch();
            if (!$dstAcc) throw new \RuntimeException('Destination account not found or inactive.');

            $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?")->execute([$amount, $fromAccountId]);
            $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?")->execute([$amount, $toAccountId]);

            $isFlagged = $fraud['flagged'] ? 1 : 0;
            $pdo->prepare(
                "INSERT INTO transactions
                 (reference_number, from_account_id, to_account_id, amount, type, status,
                  initiated_by, description, ip_address, is_flagged, flag_reason)
                 VALUES (?,?,?,?,'transfer','completed',?,?,?,?,?)"
            )->execute([
                $reference, $fromAccountId, $toAccountId, $amount,
                $initiatedBy, $description,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $isFlagged, $fraud['reason'] ?? '',
            ]);

            $txnId = (int)$pdo->lastInsertId();

            if ($isFlagged) {
                $pdo->prepare(
                    "INSERT INTO fraud_flags (transaction_id, rule_triggered, risk_score) VALUES (?,?,?)"
                )->execute([$txnId, $fraud['rule'], $fraud['score']]);
            }

            $pdo->commit();

            // ── BROADCAST after commit ─────────────────────
            $newFromBal = (float)$srcAcc['balance'] - $amount;
            $newToBal   = (float)$dstAcc['balance'] + $amount;

            BroadcastService::toUser((int)$srcAcc['user_id'], 'balance_updated', [
                'account_number' => $srcAcc['account_number'],
                'new_balance'    => $newFromBal,
                'change'         => -$amount,
            ]);
            BroadcastService::toUser((int)$srcAcc['user_id'], 'transaction_completed', [
                'reference' => $reference, 'amount' => $amount,
                'type'      => 'debit', 'status' => 'completed',
                'note'      => $description,
            ]);
            BroadcastService::toUser((int)$dstAcc['user_id'], 'balance_updated', [
                'account_number' => $dstAcc['account_number'],
                'new_balance'    => $newToBal,
                'change'         => +$amount,
            ]);
            BroadcastService::toUser((int)$dstAcc['user_id'], 'notification', [
                'title'   => 'Money Received',
                'message' => '₹' . number_format($amount, 2) . ' credited to your account',
                'type'    => 'success',
            ]);
            BroadcastService::toRole('administrator', 'transaction_completed', [
                'reference' => $reference, 'amount' => $amount,
                'type'      => 'transfer',
                'from'      => $srcAcc['account_number'],
                'to'        => $dstAcc['account_number'],
                'flagged'   => $isFlagged,
            ]);

            if ($isFlagged) {
                $fraudData = ['reference' => $reference, 'risk_score' => $fraud['score'], 'rule' => $fraud['rule']];
                BroadcastService::toRole('administrator', 'fraud_flagged', $fraudData);
            }

            LogManager::log('TRANSFER_COMPLETED', 'transaction', 'success', [
                'reference' => $reference, 'amount' => $amount,
                'from' => $fromAccountId, 'to' => $toAccountId,
            ]);

            return ['success' => true, 'reference' => $reference, 'needs_approval' => false, 'flagged' => $isFlagged];

        } catch (\Throwable $e) {
            $pdo->rollBack();
            LogManager::log('TRANSFER_FAILED', 'transaction', 'failure', [
                'error' => $e->getMessage(), 'amount' => $amount, 'from' => $fromAccountId,
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** Teller: deposit cash */
    public static function deposit(int $accountId, float $amount, string $description = ''): array
    {
        if ($amount <= 0) return ['success' => false, 'error' => 'Amount must be positive.'];

        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $accStmt = $pdo->prepare("SELECT * FROM accounts WHERE id = ? AND is_active = 1 FOR UPDATE");
            $accStmt->execute([$accountId]);
            $acc = $accStmt->fetch();
            if (!$acc) throw new \RuntimeException('Account not found or inactive.');

            $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?")->execute([$amount, $accountId]);
            $reference = self::generateReference();
            $pdo->prepare(
                "INSERT INTO transactions (reference_number, to_account_id, amount, type, status, initiated_by, description)
                 VALUES (?,?,?,'deposit','completed',?,?)"
            )->execute([$reference, $accountId, $amount, Session::get('user_id'), $description]);

            $pdo->commit();

            BroadcastService::toUser((int)$acc['user_id'], 'balance_updated', [
                'account_number' => $acc['account_number'],
                'new_balance'    => (float)$acc['balance'] + $amount,
                'change'         => +$amount,
            ]);
            BroadcastService::toUser((int)$acc['user_id'], 'notification', [
                'title'   => 'Deposit Credited',
                'message' => '₹' . number_format($amount, 2) . ' deposited to your account',
                'type'    => 'success',
            ]);

            LogManager::log('DEPOSIT', 'transaction', 'success', ['account_id' => $accountId, 'amount' => $amount, 'reference' => $reference]);
            return ['success' => true, 'reference' => $reference];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** Teller: withdrawal */
    public static function withdrawal(int $accountId, float $amount, string $description = ''): array
    {
        if ($amount <= 0) return ['success' => false, 'error' => 'Amount must be positive.'];

        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $accStmt = $pdo->prepare("SELECT * FROM accounts WHERE id = ? AND is_active = 1 FOR UPDATE");
            $accStmt->execute([$accountId]);
            $acc = $accStmt->fetch();
            if (!$acc) throw new \RuntimeException('Account not found or inactive.');
            if ((float)$acc['balance'] < $amount) throw new \RuntimeException('Insufficient funds.');
            if ($acc['is_frozen']) throw new \RuntimeException('Account is frozen.');

            $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?")->execute([$amount, $accountId]);
            $reference = self::generateReference();
            $pdo->prepare(
                "INSERT INTO transactions (reference_number, from_account_id, amount, type, status, initiated_by, description)
                 VALUES (?,?,?,'withdrawal','completed',?,?)"
            )->execute([$reference, $accountId, $amount, Session::get('user_id'), $description]);

            $pdo->commit();

            BroadcastService::toUser((int)$acc['user_id'], 'balance_updated', [
                'account_number' => $acc['account_number'],
                'new_balance'    => (float)$acc['balance'] - $amount,
                'change'         => -$amount,
            ]);
            BroadcastService::toUser((int)$acc['user_id'], 'notification', [
                'title'   => 'Withdrawal Processed',
                'message' => '₹' . number_format($amount, 2) . ' withdrawn from your account',
                'type'    => 'warning',
            ]);

            LogManager::log('WITHDRAWAL', 'transaction', 'success', ['account_id' => $accountId, 'amount' => $amount, 'reference' => $reference]);
            return ['success' => true, 'reference' => $reference];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** Admin: reverse a completed transfer */
    public static function reverseTransaction(int $transactionId, int $adminId): array
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $txnStmt = $pdo->prepare(
                "SELECT * FROM transactions WHERE id = ? AND status = 'completed' AND type = 'transfer' FOR UPDATE"
            );
            $txnStmt->execute([$transactionId]);
            $t = $txnStmt->fetch();
            if (!$t) throw new \RuntimeException('Transaction not found or not reversible.');

            $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?")->execute([$t['amount'], $t['from_account_id']]);
            $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?")->execute([$t['amount'], $t['to_account_id']]);
            $pdo->prepare("UPDATE transactions SET status = 'reversed' WHERE id = ?")->execute([$transactionId]);

            $ref = self::generateReference('REV');
            $pdo->prepare(
                "INSERT INTO transactions (reference_number, from_account_id, to_account_id, amount, type, status, initiated_by, description)
                 VALUES (?,?,?,?,'reversal','completed',?,?)"
            )->execute([$ref, $t['to_account_id'], $t['from_account_id'], $t['amount'], $adminId, 'Reversal of ' . $t['reference_number']]);

            $pdo->commit();

            BroadcastService::toRole('administrator', 'transaction_completed', [
                'reference' => $ref, 'type' => 'reversal', 'amount' => $t['amount'],
            ]);
            LogManager::log('TRANSACTION_REVERSED', 'transaction', 'success', [
                'original' => $t['reference_number'], 'reversal' => $ref,
            ]);
            return ['success' => true, 'reversal_reference' => $ref];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** Teller/Admin: approve a pending transaction */
    public static function approvePending(int $transactionId, int $approvedBy): array
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND status = 'pending' FOR UPDATE");
            $stmt->execute([$transactionId]);
            $txn = $stmt->fetch();
            if (!$txn) throw new \RuntimeException('Pending transaction not found.');

            $src = $pdo->prepare("SELECT * FROM accounts WHERE id = ? FOR UPDATE");
            $src->execute([$txn['from_account_id']]);
            $srcAcc = $src->fetch();
            if (!$srcAcc || (float)$srcAcc['balance'] < (float)$txn['amount']) {
                throw new \RuntimeException('Insufficient funds in source account.');
            }

            $dst = $pdo->prepare("SELECT * FROM accounts WHERE id = ? FOR UPDATE");
            $dst->execute([$txn['to_account_id']]);
            $dstAcc = $dst->fetch();

            $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?")->execute([$txn['amount'], $txn['from_account_id']]);
            $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?")->execute([$txn['amount'], $txn['to_account_id']]);
            $pdo->prepare(
                "UPDATE transactions SET status = 'completed', approved_by = ? WHERE id = ?"
            )->execute([$approvedBy, $transactionId]);

            $pdo->commit();

            BroadcastService::toUser((int)$srcAcc['user_id'], 'approval_granted', [
                'reference'   => $txn['reference_number'],
                'approved_by' => Session::get('username'),
            ]);
            BroadcastService::toUser((int)$srcAcc['user_id'], 'balance_updated', [
                'account_number' => $srcAcc['account_number'],
                'new_balance'    => (float)$srcAcc['balance'] - (float)$txn['amount'],
                'change'         => -(float)$txn['amount'],
            ]);
            BroadcastService::toUser((int)$dstAcc['user_id'], 'balance_updated', [
                'account_number' => $dstAcc['account_number'],
                'new_balance'    => (float)$dstAcc['balance'] + (float)$txn['amount'],
                'change'         => +(float)$txn['amount'],
            ]);

            LogManager::log('TRANSFER_APPROVED', 'transaction', 'success', [
                'reference' => $txn['reference_number'], 'approved_by' => $approvedBy,
            ]);
            return ['success' => true];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** Teller/Admin: reject a pending transaction */
    public static function rejectPending(int $transactionId, int $rejectedBy, string $reason = ''): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND status = 'pending'");
        $stmt->execute([$transactionId]);
        $txn = $stmt->fetch();
        if (!$txn) return ['success' => false, 'error' => 'Transaction not found.'];

        $pdo->prepare("UPDATE transactions SET status = 'rejected', approved_by = ? WHERE id = ?")
            ->execute([$rejectedBy, $transactionId]);

        // Notify the customer
        $srcAcc = $pdo->prepare("SELECT user_id FROM accounts WHERE id = ?");
        $srcAcc->execute([$txn['from_account_id']]);
        $acc = $srcAcc->fetch();
        if ($acc) {
            BroadcastService::toUser((int)$acc['user_id'], 'approval_rejected', [
                'reference' => $txn['reference_number'],
                'reason'    => $reason ?: 'Rejected by approver',
            ]);
        }

        LogManager::log('TRANSFER_REJECTED', 'transaction', 'success', ['reference' => $txn['reference_number']]);
        return ['success' => true];
    }

    /** Paginated transaction history */
    public static function getHistory(int $accountId, array $filters = [], int $page = 1): array
    {
        $pdo    = Database::getInstance();
        $limit  = 20;
        $offset = ($page - 1) * $limit;
        $where  = "WHERE (t.from_account_id = ? OR t.to_account_id = ?)";
        $params = [$accountId, $accountId];

        if (!empty($filters['type']))      { $where .= " AND t.type = ?";              $params[] = $filters['type']; }
        if (!empty($filters['from_date'])) { $where .= " AND DATE(t.created_at) >= ?"; $params[] = $filters['from_date']; }
        if (!empty($filters['to_date']))   { $where .= " AND DATE(t.created_at) <= ?"; $params[] = $filters['to_date']; }
        if (!empty($filters['min_amount'])){ $where .= " AND t.amount >= ?";            $params[] = (float)$filters['min_amount']; }
        if (!empty($filters['max_amount'])){ $where .= " AND t.amount <= ?";            $params[] = (float)$filters['max_amount']; }

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions t $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT t.*,
                fa.account_number AS from_acc_number,
                ta.account_number AS to_acc_number
            FROM transactions t
            LEFT JOIN accounts fa ON fa.id = t.from_account_id
            LEFT JOIN accounts ta ON ta.id = t.to_account_id
            $where
            ORDER BY t.created_at DESC
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute($params);

        return [
            'rows'  => $stmt->fetchAll(),
            'total' => $total,
            'pages' => (int)ceil($total / $limit),
            'page'  => $page,
        ];
    }

    // ── Private helpers ───────────────────────────────────────

    private static function checkDailyLimit(int $accountId, float $amount): array
    {
        $pdo    = Database::getInstance();
        $roleId = (int)Session::get('role_id', 3);

        $limStmt = $pdo->prepare("SELECT * FROM transaction_limits WHERE role_id = ?");
        $limStmt->execute([$roleId]);
        $limit = $limStmt->fetch();
        if (!$limit) return ['ok' => true];

        if ($amount > (float)$limit['single_limit']) {
            return ['ok' => false, 'error' => 'Amount exceeds single transaction limit of ₹' . number_format($limit['single_limit'], 2)];
        }

        $dayStmt = $pdo->prepare(
            "SELECT COALESCE(SUM(amount),0) AS daily_total FROM transactions
             WHERE from_account_id = ? AND DATE(created_at) = CURDATE()
               AND status IN ('completed','pending') AND type = 'transfer'"
        );
        $dayStmt->execute([$accountId]);
        $dailyTotal = (float)$dayStmt->fetch()['daily_total'];

        if (($dailyTotal + $amount) > (float)$limit['daily_limit']) {
            return ['ok' => false, 'error' => 'Daily transfer limit of ₹' . number_format($limit['daily_limit'], 2) . ' would be exceeded.'];
        }

        return ['ok' => true];
    }

    // requiresApproval method removed as transfers are now instant

    private static function createPendingTransfer(
        int $fromId, int $toId, float $amount, int $initiatedBy, string $description, array $fraud
    ): array {
        $pdo       = Database::getInstance();
        $reference = self::generateReference();
        $pdo->prepare(
            "INSERT INTO transactions (reference_number, from_account_id, to_account_id, amount, type, status, initiated_by, description, is_flagged, flag_reason)
             VALUES (?,?,?,?,'transfer','pending',?,?,?,?)"
        )->execute([
            $reference, $fromId, $toId, $amount, $initiatedBy, $description,
            $fraud['flagged'] ? 1 : 0, $fraud['reason'] ?? '',
        ]);

        BroadcastService::toRole('administrator', 'approval_required', ['reference' => $reference, 'amount' => $amount, 'initiated_by' => $initiatedBy]);

        LogManager::log('TRANSFER_PENDING', 'transaction', 'success', ['reference' => $reference, 'amount' => $amount]);
        return ['success' => true, 'reference' => $reference, 'needs_approval' => true];
    }
}
