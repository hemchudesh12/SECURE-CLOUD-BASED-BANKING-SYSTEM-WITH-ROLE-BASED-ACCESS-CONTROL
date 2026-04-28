<?php
/**
 * FraudDetectionService — rule-based fraud scoring.
 * Called before every transfer; flags suspicious transactions.
 */
class FraudDetectionService
{
    public static function check(int $accountId, float $amount, int $userId): array
    {
        $pdo   = Database::getInstance();
        $score = 0;
        $rules = [];

        // Rule 1: Unusually large single amount (> ₹40,000)
        if ($amount > 40000) {
            $score += 30;
            $rules[] = 'large_amount';
        }

        // Rule 2: More than 5 transactions in the last hour
        $recent = $pdo->prepare(
            "SELECT COUNT(*) AS cnt FROM transactions
             WHERE from_account_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        );
        $recent->execute([$accountId]);
        if ((int)$recent->fetch()['cnt'] >= 5) {
            $score += 35;
            $rules[] = 'high_frequency';
        }

        // Rule 3: Transfer during unusual hours (midnight – 5 am)
        $hour = (int)date('H');
        if ($hour >= 0 && $hour < 5) {
            $score += 15;
            $rules[] = 'unusual_hours';
        }

        // Rule 4: Amount exactly equal to balance (drain attempt)
        $balStmt = $pdo->prepare("SELECT balance FROM accounts WHERE id = ?");
        $balStmt->execute([$accountId]);
        $bal = (float)($balStmt->fetch()['balance'] ?? 0);
        if ($bal > 0 && abs($bal - $amount) < 0.01) {
            $score += 20;
            $rules[] = 'full_balance_drain';
        }

        // Rule 5: Multiple failed attempts in last 30 min
        $failStmt = $pdo->prepare(
            "SELECT COUNT(*) AS cnt FROM transactions
             WHERE from_account_id = ? AND status = 'failed' AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)"
        );
        $failStmt->execute([$accountId]);
        if ((int)$failStmt->fetch()['cnt'] >= 2) {
            $score += 25;
            $rules[] = 'repeated_failures';
        }

        return [
            'flagged' => $score >= 40,
            'score'   => min($score, 100),
            'rule'    => implode(',', $rules),
            'reason'  => empty($rules) ? '' : 'Rules triggered: ' . implode(', ', $rules),
        ];
    }
}
