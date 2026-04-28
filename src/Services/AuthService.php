<?php
/**
 * AuthService — business logic for authentication.
 */
class AuthService
{
    /** Attempt login. Returns user array on success, null on failure. */
    public static function attemptLogin(string $username, string $password): ?array
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT u.*, r.name AS role_name
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE u.username = ?
            LIMIT 1
        ");
        $stmt->execute([trim($username)]);
        $user = $stmt->fetch();

        if (!$user) {
            LogManager::log('LOGIN_FAILURE', 'user', 'failure', ['username' => $username]);
            return null;
        }

        // Check if account is locked
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            LogManager::log('LOGIN_LOCKED', 'user', 'failure', ['user_id' => $user['id']]);
            return null;
        }

        // Check active status
        if (!$user['is_active']) {
            LogManager::log('LOGIN_INACTIVE', 'user', 'failure', ['user_id' => $user['id']]);
            return null;
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            self::incrementFailures($user['id']);
            LogManager::log('LOGIN_FAILURE', 'user', 'failure', ['user_id' => $user['id']]);
            return null;
        }

        // Success — reset failures, update last login
        self::resetFailures($user['id']);
        return $user;
    }

    private static function incrementFailures(int $userId): void
    {
        $cfg         = require BASE_PATH . '/config/app.php';
        $maxAttempts = $cfg['security']['max_login_attempts'] ?? 5;
        $lockMins    = $cfg['security']['lockout_minutes']    ?? 15;

        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare("
            UPDATE users
            SET login_failures = login_failures + 1,
                locked_until = IF(login_failures + 1 >= ?,
                    DATE_ADD(NOW(), INTERVAL ? MINUTE), locked_until)
            WHERE id = ?
        ");
        $stmt->execute([$maxAttempts, $lockMins, $userId]);
    }

    private static function resetFailures(int $userId): void
    {
        $pdo  = Database::getInstance();
        $ip   = $_SERVER['REMOTE_ADDR'] ?? '';
        $stmt = $pdo->prepare("
            UPDATE users
            SET login_failures = 0,
                locked_until   = NULL,
                last_login_at  = NOW(),
                last_login_ip  = ?
            WHERE id = ?
        ");
        $stmt->execute([$ip, $userId]);
    }

    /** Register a new customer account */
    public static function register(string $username, string $email, string $password, string $fullName): array
    {
        $pdo = Database::getInstance();

        // Check uniqueness
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $check->execute([trim($username), trim($email)]);
        if ($check->fetch()) {
            return ['success' => false, 'error' => 'Username or email already exists.'];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        // role_id 3 = customer
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, full_name, role_id, is_active, email_verified)
            VALUES (?, ?, ?, ?, 3, 1, 1)
        ");
        $stmt->execute([trim($username), trim($email), $hash, trim($fullName)]);
        $newId = (int)$pdo->lastInsertId();

        // Create bank account automatically
        $accNumber = 'ACC-' . date('Y') . str_pad($newId, 6, '0', STR_PAD_LEFT);
        $accStmt   = $pdo->prepare("
            INSERT INTO accounts (user_id, account_number, account_type, balance, currency)
            VALUES (?, ?, 'savings', 0.00, 'INR')
        ");
        $accStmt->execute([$newId, $accNumber]);

        LogManager::log('REGISTER', 'user', 'success', ['username' => $username], $newId);
        return ['success' => true, 'user_id' => $newId];
    }

    /** Validate password strength */
    public static function validatePassword(string $password): ?string
    {
        if (strlen($password) < 8)                          return 'Password must be at least 8 characters.';
        if (!preg_match('/[A-Z]/', $password))              return 'Password must contain an uppercase letter.';
        if (!preg_match('/[0-9]/', $password))              return 'Password must contain a number.';
        if (!preg_match('/[^A-Za-z0-9]/', $password))      return 'Password must contain a special character.';
        return null;
    }
}
