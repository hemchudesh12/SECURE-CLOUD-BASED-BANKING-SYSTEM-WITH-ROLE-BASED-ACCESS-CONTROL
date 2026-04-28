<?php
/**
 * StatementService — PDF and CSV statement generation.
 * Requires mPDF via Composer: composer require mpdf/mpdf
 */
class StatementService
{
    /** Generate PDF statement and return file path */
    public static function generatePDF(int $accountId, string $fromDate, string $toDate): string
    {
        $pdo = Database::getInstance();

        $accStmt = $pdo->prepare(
            "SELECT a.*, u.full_name, u.email, u.phone FROM accounts a
             JOIN users u ON u.id = a.user_id WHERE a.id = ?"
        );
        $accStmt->execute([$accountId]);
        $account = $accStmt->fetch();

        $txnStmt = $pdo->prepare(
            "SELECT t.*,
                    a1.account_number AS from_acc,
                    a2.account_number AS to_acc
             FROM transactions t
             LEFT JOIN accounts a1 ON a1.id = t.from_account_id
             LEFT JOIN accounts a2 ON a2.id = t.to_account_id
             WHERE (t.from_account_id = ? OR t.to_account_id = ?)
               AND DATE(t.created_at) BETWEEN ? AND ?
               AND t.status = 'completed'
             ORDER BY t.created_at ASC"
        );
        $txnStmt->execute([$accountId, $accountId, $fromDate, $toDate]);
        $transactions = $txnStmt->fetchAll();

        $html = self::renderHTML($account, $transactions, $fromDate, $toDate);

        $storageDir = BASE_PATH . '/storage/statements/';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        // Check if mPDF is available
        if (!class_exists('Mpdf\Mpdf')) {
            // Fallback: save HTML as file
            $filename = 'statement_' . $account['account_number'] . '_' . $fromDate . '_' . $toDate . '.html';
            $path = $storageDir . $filename;
            file_put_contents($path, $html);
            return $path;
        }

        $mpdf = new \Mpdf\Mpdf([
            'orientation' => 'P',
            'margin_top'  => 20,
            'margin_left' => 15,
            'margin_right'=> 15,
        ]);
        $mpdf->SetTitle('Account Statement — ' . $account['account_number']);
        $mpdf->WriteHTML($html);

        $filename = 'statement_' . $account['account_number'] . '_' . $fromDate . '_' . $toDate . '.pdf';
        $path     = $storageDir . $filename;
        $mpdf->Output($path, 'F');
        return $path;
    }

    /** Generate CSV statement and return file path */
    public static function generateCSV(int $accountId, string $fromDate, string $toDate): string
    {
        $pdo = Database::getInstance();

        $accStmt = $pdo->prepare("SELECT account_number FROM accounts WHERE id = ?");
        $accStmt->execute([$accountId]);
        $account = $accStmt->fetch();

        $txnStmt = $pdo->prepare(
            "SELECT t.created_at, t.reference_number, t.type, t.amount, t.status,
                    a1.account_number AS from_acc, a2.account_number AS to_acc, t.description
             FROM transactions t
             LEFT JOIN accounts a1 ON a1.id = t.from_account_id
             LEFT JOIN accounts a2 ON a2.id = t.to_account_id
             WHERE (t.from_account_id = ? OR t.to_account_id = ?)
               AND DATE(t.created_at) BETWEEN ? AND ?
             ORDER BY t.created_at DESC"
        );
        $txnStmt->execute([$accountId, $accountId, $fromDate, $toDate]);

        $storageDir = BASE_PATH . '/storage/statements/';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $filename = 'statement_' . ($account['account_number'] ?? $accountId) . '_' . time() . '.csv';
        $path     = $storageDir . $filename;
        $fp       = fopen($path, 'w');
        fputcsv($fp, ['Date', 'Reference', 'Type', 'Amount (INR)', 'Status', 'From Account', 'To Account', 'Description']);
        foreach ($txnStmt->fetchAll() as $row) {
            fputcsv($fp, [
                $row['created_at'],
                $row['reference_number'],
                $row['type'],
                number_format((float)$row['amount'], 2),
                $row['status'],
                $row['from_acc'] ?? '',
                $row['to_acc']   ?? '',
                $row['description'] ?? '',
            ]);
        }
        fclose($fp);
        return $path;
    }

    /** Render the HTML for the PDF */
    private static function renderHTML(array $account, array $transactions, string $from, string $to): string
    {
        $rows    = '';
        $balance = (float)$account['balance'];
        // Compute opening balance by subtracting all txns in period
        $netChange = 0;
        foreach ($transactions as $t) {
            if ($t['from_account_id'] == $account['id']) {
                $netChange -= (float)$t['amount'];
            } else {
                $netChange += (float)$t['amount'];
            }
        }
        $openingBalance = $balance - $netChange;
        $runningBalance = $openingBalance;

        foreach ($transactions as $t) {
            $isDebit = $t['from_account_id'] == $account['id'];
            $dr = $isDebit  ? '&#8377;' . number_format((float)$t['amount'], 2) : '-';
            $cr = !$isDebit ? '&#8377;' . number_format((float)$t['amount'], 2) : '-';
            $runningBalance += $isDebit ? -(float)$t['amount'] : +(float)$t['amount'];
            $rows .= "<tr>
                <td>" . htmlspecialchars($t['created_at']) . "</td>
                <td>" . htmlspecialchars($t['reference_number']) . "</td>
                <td>" . htmlspecialchars($t['description'] ?? $t['type']) . "</td>
                <td style='color:#dc3545;text-align:right'>$dr</td>
                <td style='color:#198754;text-align:right'>$cr</td>
                <td style='text-align:right'>&#8377;" . number_format($runningBalance, 2) . "</td>
            </tr>";
        }

        $genDate = date('d M Y H:i:s');
        return "
        <style>
            body { font-family: Arial, sans-serif; color: #1a2744; font-size: 12px; }
            h1   { color: #c9a84c; margin-bottom: 4px; }
            .header-info { margin-bottom: 16px; }
            .header-info p { margin: 2px 0; }
            table { width:100%; border-collapse:collapse; }
            th    { background:#1a2744; color:#c9a84c; padding:8px 6px; text-align:left; }
            td    { border-bottom:1px solid #eee; padding:6px; }
            tr:nth-child(even) { background:#f9f9f9; }
            .footer { margin-top: 20px; font-size: 10px; color: #888; text-align: center; }
        </style>
        <h1>&#127981; SecureBank — Account Statement</h1>
        <div class='header-info'>
            <p><strong>Account Number:</strong> {$account['account_number']} &nbsp;|&nbsp; <strong>Type:</strong> " . ucfirst($account['account_type']) . "</p>
            <p><strong>Account Holder:</strong> " . htmlspecialchars($account['full_name']) . " &nbsp;|&nbsp; <strong>Email:</strong> " . htmlspecialchars($account['email']) . "</p>
            <p><strong>Statement Period:</strong> $from to $to</p>
            <p><strong>Generated On:</strong> $genDate</p>
            <p><strong>Opening Balance:</strong> &#8377;" . number_format($openingBalance, 2) . " &nbsp;|&nbsp; <strong>Closing Balance:</strong> &#8377;" . number_format($runningBalance, 2) . "</p>
        </div>
        <table>
            <tr>
                <th>Date &amp; Time</th>
                <th>Reference</th>
                <th>Description</th>
                <th style='text-align:right'>Debit</th>
                <th style='text-align:right'>Credit</th>
                <th style='text-align:right'>Balance</th>
            </tr>
            $rows
        </table>
        <div class='footer'>This is a system-generated statement. SecureBank &copy; " . date('Y') . ". All transactions are audited.</div>";
    }
}
