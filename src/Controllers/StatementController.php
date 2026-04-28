<?php
/**
 * StatementController — PDF and CSV statement downloads.
 */
class StatementController
{
    private function getAccount(): ?array
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM accounts WHERE user_id = ? AND is_active = 1");
        $stmt->execute([Session::get('user_id')]);
        return $stmt->fetch() ?: null;
    }

    public function showStatement(array $params = []): void
    {
        $account = $this->getAccount();
        $view = 'statement';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function downloadPDF(array $params = []): void
    {
        $account   = $this->getAccount();
        if (!$account) {
            Session::flash('error', 'Account not found.');
            header('Location: /banking-system/public/customer/statement');
            exit;
        }

        $fromDate = $_GET['from_date'] ?? date('Y-m-01');
        $toDate   = $_GET['to_date']   ?? date('Y-m-d');

        $path = StatementService::generatePDF((int)$account['id'], $fromDate, $toDate);

        if (!file_exists($path)) {
            Session::flash('error', 'Could not generate statement. Ensure mPDF is installed.');
            header('Location: /banking-system/public/customer/statement');
            exit;
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $mime = ($ext === 'pdf') ? 'application/pdf' : 'text/html';

        LogManager::log('STATEMENT_PDF_DOWNLOAD', 'account', 'success', [
            'account_id' => $account['id'],
            'from' => $fromDate, 'to' => $toDate,
        ]);

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="statement-' . $account['account_number'] . '-' . $fromDate . '-' . $toDate . '.' . $ext . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);

        // Cleanup generated file
        @unlink($path);
        exit;
    }

    public function downloadCSV(array $params = []): void
    {
        $account = $this->getAccount();
        if (!$account) exit;

        $fromDate = $_GET['from_date'] ?? date('Y-m-01');
        $toDate   = $_GET['to_date']   ?? date('Y-m-d');

        $path = StatementService::generateCSV((int)$account['id'], $fromDate, $toDate);

        if (!file_exists($path)) {
            Session::flash('error', 'Could not generate CSV statement.');
            header('Location: /banking-system/public/customer/statement');
            exit;
        }

        LogManager::log('STATEMENT_CSV_DOWNLOAD', 'account', 'success', [
            'account_id' => $account['id'],
            'from' => $fromDate, 'to' => $toDate,
        ]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="statement-' . $account['account_number'] . '-' . $fromDate . '-' . $toDate . '.csv"');
        header('Content-Length: ' . filesize($path));
        readfile($path);

        @unlink($path);
        exit;
    }

    /** Teller/Admin: download statement for any account */
    public function downloadForAccount(array $params = []): void
    {
        $accountId = (int)($params['id'] ?? $_GET['account_id'] ?? 0);
        $format    = $_GET['format'] ?? 'csv';
        $fromDate  = $_GET['from_date'] ?? date('Y-m-01');
        $toDate    = $_GET['to_date']   ?? date('Y-m-d');

        $path = ($format === 'pdf')
            ? StatementService::generatePDF($accountId, $fromDate, $toDate)
            : StatementService::generateCSV($accountId, $fromDate, $toDate);

        $ext  = $format === 'pdf' ? 'pdf' : 'csv';
        $mime = $format === 'pdf' ? 'application/pdf' : 'text/csv';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="statement-account-' . $accountId . '-' . $fromDate . '-' . $toDate . '.' . $ext . '"');
        readfile($path);
        @unlink($path);
        exit;
    }
}
