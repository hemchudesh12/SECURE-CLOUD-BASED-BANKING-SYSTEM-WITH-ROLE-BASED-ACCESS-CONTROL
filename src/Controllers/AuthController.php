<?php
/**
 * AuthController — handles login, logout, register, password reset.
 */
class AuthController
{
    public function showLogin(array $params = []): void
    {
        if (Session::isLoggedIn()) {
            $this->redirectToDashboard();
        }
        include BASE_PATH . '/views/layouts/auth.php';
    }

    public function login(array $params = []): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            Session::flash('error', 'Username and password are required.');
            header('Location: /banking-system/public/login');
            exit;
        }

        $user = AuthService::attemptLogin($username, $password);

        if (!$user) {
            // Check if locked
            $pdo  = Database::getInstance();
            $stmt = $pdo->prepare("SELECT locked_until FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $row = $stmt->fetch();
            if ($row && $row['locked_until'] && strtotime($row['locked_until']) > time()) {
                $until = date('h:i A', strtotime($row['locked_until']));
                Session::flash('error', "Account locked due to too many failed attempts. Try again after $until.");
            } else {
                Session::flash('error', 'Invalid username or password.');
            }
            header('Location: /banking-system/public/login');
            exit;
        }

        // Load permissions
        $permissions = RbacService::loadPermissions((int)$user['role_id']);
        Session::afterLogin($user, $permissions);

        LogManager::log('LOGIN_SUCCESS', 'user', 'success', [
            'role' => $user['role_name'],
        ], $user['id']);

        $this->redirectToDashboard();
    }

    public function showRegister(array $params = []): void
    {
        if (Session::isLoggedIn()) {
            $this->redirectToDashboard();
        }
        $view = 'register';
        include BASE_PATH . '/views/layouts/auth.php';
    }

    public function register(array $params = []): void
    {
        $username  = trim($_POST['username']  ?? '');
        $email     = trim($_POST['email']     ?? '');
        $fullName  = trim($_POST['full_name'] ?? '');
        $password  = $_POST['password']       ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';

        // Validate
        $errors = [];
        if (empty($username))  $errors[] = 'Username is required.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (empty($fullName))  $errors[] = 'Full name is required.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';
        $pwdError = AuthService::validatePassword($password);
        if ($pwdError) $errors[] = $pwdError;

        if ($errors) {
            foreach ($errors as $e) Session::flash('error', $e);
            header('Location: /banking-system/public/register');
            exit;
        }

        $result = AuthService::register($username, $email, $password, $fullName);
        if (!$result['success']) {
            Session::flash('error', $result['error']);
            header('Location: /banking-system/public/register');
            exit;
        }

        Session::flash('success', 'Account created! You can now log in.');
        header('Location: /banking-system/public/login');
        exit;
    }

    public function handleLogout(array $params = []): void
    {
        LogManager::log('LOGOUT', 'user', 'success', ['username' => Session::get('username')]);
        Session::destroy();
        header('Location: /banking-system/public/login?logout=1');
        exit;
    }

    public function showResetRequest(array $params = []): void
    {
        $view = 'reset_request';
        include BASE_PATH . '/views/layouts/auth.php';
    }

    public function handleResetRequest(array $params = []): void
    {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please enter a valid email address.');
            header('Location: /banking-system/public/reset-password');
            exit;
        }

        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token     = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expires   = date('Y-m-d H:i:s', time() + 3600);

            $pdo->prepare("
                INSERT INTO password_reset_tokens (user_id, token_hash, expires_at)
                VALUES (?, ?, ?)
            ")->execute([$user['id'], $tokenHash, $expires]);

            // In production, send email. For local dev, show the link.
            Session::flash('info', 'Reset link (dev mode): /banking-system/public/reset-password/' . $token);
            LogManager::log('PASSWORD_RESET_REQUEST', 'user', 'success', [], $user['id']);
        }

        // Always show same message (prevent email enumeration)
        Session::flash('success', 'If that email exists, a reset link has been sent.');
        header('Location: /banking-system/public/reset-password');
        exit;
    }

    public function showResetForm(array $params = []): void
    {
        $token = $params['token'] ?? '';
        $view  = 'reset_form';
        $valid = $this->validateResetToken($token);
        include BASE_PATH . '/views/layouts/auth.php';
    }

    public function handleResetForm(array $params = []): void
    {
        $token    = $params['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        $userId = $this->validateResetToken($token);
        if (!$userId) {
            Session::flash('error', 'Invalid or expired reset link.');
            header('Location: /banking-system/public/reset-password');
            exit;
        }

        if ($password !== $confirm) {
            Session::flash('error', 'Passwords do not match.');
            header('Location: /banking-system/public/reset-password/' . $token);
            exit;
        }
        $pwdError = AuthService::validatePassword($password);
        if ($pwdError) {
            Session::flash('error', $pwdError);
            header('Location: /banking-system/public/reset-password/' . $token);
            exit;
        }

        $pdo  = Database::getInstance();
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $userId]);
        $pdo->prepare("UPDATE password_reset_tokens SET used_at = NOW() WHERE token_hash = ?")
            ->execute([hash('sha256', $token)]);

        LogManager::log('PASSWORD_RESET_COMPLETE', 'user', 'success', [], $userId);
        Session::flash('success', 'Password updated successfully. Please log in.');
        header('Location: /banking-system/public/login');
        exit;
    }

    private function validateResetToken(string $token): ?int
    {
        if (empty($token)) return null;
        $pdo   = Database::getInstance();
        $stmt  = $pdo->prepare("
            SELECT user_id FROM password_reset_tokens
            WHERE token_hash = ? AND expires_at > NOW() AND used_at IS NULL
        ");
        $stmt->execute([hash('sha256', $token)]);
        $row = $stmt->fetch();
        return $row ? (int)$row['user_id'] : null;
    }

    private function redirectToDashboard(): void
    {
        $role = Session::get('role', 'customer');
        match($role) {
            'administrator' => header('Location: /banking-system/public/admin/dashboard'),
            default         => header('Location: /banking-system/public/customer/dashboard'),
        };
        exit;
    }
}
