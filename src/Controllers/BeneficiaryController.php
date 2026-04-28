<?php
/**
 * BeneficiaryController — manage saved payees.
 */
class BeneficiaryController
{
    private function getAccount(): ?array
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM accounts WHERE user_id = ? AND is_active = 1");
        $stmt->execute([Session::get('user_id')]);
        return $stmt->fetch() ?: null;
    }

    public function index(array $params = []): void
    {
        $userId = (int)Session::get('user_id');
        $pdo    = Database::getInstance();
        $stmt   = $pdo->prepare(
            "SELECT b.*, a.id AS resolved_account_id
             FROM beneficiaries b
             LEFT JOIN accounts a ON a.account_number = b.account_number AND a.is_active = 1
             WHERE b.user_id = ? AND b.is_active = 1
             ORDER BY b.nickname ASC"
        );
        $stmt->execute([$userId]);
        $beneficiaries = $stmt->fetchAll();
        $account       = $this->getAccount();

        $view = 'beneficiaries';
        include BASE_PATH . '/views/layouts/main.php';
    }

    public function add(array $params = []): void
    {
        CsrfMiddleware::verify();
        $userId  = (int)Session::get('user_id');
        $nick    = trim($_POST['nickname']       ?? '');
        $accNum  = trim($_POST['account_number'] ?? '');
        $bank    = trim($_POST['bank_name']      ?? 'SecureBank');

        if (empty($nick) || empty($accNum)) {
            Session::flash('error', 'Nickname and account number are required.');
            header('Location: /banking-system/public/customer/beneficiaries');
            exit;
        }

        $pdo = Database::getInstance();

        // Verify destination account exists
        $chk = $pdo->prepare("SELECT id FROM accounts WHERE account_number = ? AND is_active = 1");
        $chk->execute([$accNum]);
        if (!$chk->fetch()) {
            Session::flash('error', 'Account not found or inactive.');
            header('Location: /banking-system/public/customer/beneficiaries');
            exit;
        }

        // Check not own account
        $own = $pdo->prepare("SELECT id FROM accounts WHERE account_number = ? AND user_id = ?");
        $own->execute([$accNum, $userId]);
        if ($own->fetch()) {
            Session::flash('error', 'Cannot add your own account as a beneficiary.');
            header('Location: /banking-system/public/customer/beneficiaries');
            exit;
        }

        try {
            $pdo->prepare(
                "INSERT INTO beneficiaries (user_id, nickname, account_number, bank_name)
                 VALUES (?, ?, ?, ?)"
            )->execute([$userId, $nick, $accNum, $bank]);
            LogManager::log('BENEFICIARY_ADDED', 'beneficiary', 'success', ['account_number' => $accNum]);
            Session::flash('success', "Beneficiary '{$nick}' added successfully.");
        } catch (Throwable $e) {
            Session::flash('error', 'Beneficiary already exists or could not be added.');
        }

        header('Location: /banking-system/public/customer/beneficiaries');
        exit;
    }

    public function remove(array $params = []): void
    {
        CsrfMiddleware::verify();
        $userId = (int)Session::get('user_id');
        $id     = (int)($params['id'] ?? $_POST['id'] ?? 0);

        $pdo = Database::getInstance();
        $pdo->prepare(
            "UPDATE beneficiaries SET is_active = 0 WHERE id = ? AND user_id = ?"
        )->execute([$id, $userId]);

        LogManager::log('BENEFICIARY_REMOVED', 'beneficiary', 'success', ['id' => $id]);
        Session::flash('success', 'Beneficiary removed.');
        header('Location: /banking-system/public/customer/beneficiaries');
        exit;
    }

    /** AJAX: lookup account number for autofill */
    public function lookup(array $params = []): void
    {
        header('Content-Type: application/json');
        $accNum = trim($_GET['account'] ?? '');
        if (empty($accNum)) {
            echo json_encode(['found' => false]);
            exit;
        }

        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT a.account_number, a.account_type, u.full_name
             FROM accounts a JOIN users u ON u.id = a.user_id
             WHERE a.account_number = ? AND a.is_active = 1"
        );
        $stmt->execute([$accNum]);
        $acc = $stmt->fetch();

        if ($acc) {
            echo json_encode([
                'found'        => true,
                'account_type' => $acc['account_type'],
                'holder_name'  => $acc['full_name'],
            ]);
        } else {
            echo json_encode(['found' => false]);
        }
        exit;
    }
}
