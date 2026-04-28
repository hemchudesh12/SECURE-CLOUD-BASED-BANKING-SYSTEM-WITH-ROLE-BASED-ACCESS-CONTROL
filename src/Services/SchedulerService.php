<?php
/**
 * SchedulerService — run due scheduled payments.
 * Invoke via: php bin/run-scheduler.php
 */
class SchedulerService
{
    public static function runDue(): void
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT * FROM scheduled_payments
             WHERE status = 'active'
               AND next_run_at <= NOW()
               AND (end_date IS NULL OR end_date >= CURDATE())"
        );
        $stmt->execute();
        $due = $stmt->fetchAll();

        foreach ($due as $payment) {
            $result = TransactionService::transferById(
                (int)$payment['from_account_id'],
                (int)$payment['to_account_id'],
                (float)$payment['amount'],
                (int)$payment['user_id'],
                $payment['description'] ?? 'Scheduled payment'
            );

            $nextRun = match($payment['frequency']) {
                'daily'   => date('Y-m-d H:i:s', strtotime('+1 day')),
                'weekly'  => date('Y-m-d H:i:s', strtotime('+1 week')),
                'monthly' => date('Y-m-d H:i:s', strtotime('+1 month')),
                'once'    => null,
                default   => null,
            };

            if ($payment['frequency'] === 'once' || $nextRun === null) {
                $pdo->prepare(
                    "UPDATE scheduled_payments SET status='completed', last_run_at=NOW() WHERE id=?"
                )->execute([$payment['id']]);
            } else {
                $pdo->prepare(
                    "UPDATE scheduled_payments SET next_run_at=?, last_run_at=NOW() WHERE id=?"
                )->execute([$nextRun, $payment['id']]);
            }

            if ($result['success']) {
                BroadcastService::toUser((int)$payment['user_id'], 'scheduled_executed', [
                    'reference'   => $result['reference'],
                    'amount'      => $payment['amount'],
                    'description' => $payment['description'],
                ]);
                BroadcastService::toUser((int)$payment['user_id'], 'notification', [
                    'title'   => 'Scheduled payment sent',
                    'message' => '₹' . number_format((float)$payment['amount'], 2) . ' — ' . $payment['description'],
                    'type'    => 'info',
                ]);
            } else {
                // Mark as failed if persistent error
                $pdo->prepare(
                    "UPDATE scheduled_payments SET status='failed', last_run_at=NOW() WHERE id=?"
                )->execute([$payment['id']]);
                BroadcastService::toUser((int)$payment['user_id'], 'notification', [
                    'title'   => 'Scheduled payment failed',
                    'message' => 'Could not process payment: ' . ($result['error'] ?? 'Unknown error'),
                    'type'    => 'danger',
                ]);
            }
        }

        echo '[' . date('Y-m-d H:i:s') . '] Scheduler ran. Processed: ' . count($due) . ' payment(s).' . PHP_EOL;
    }
}
