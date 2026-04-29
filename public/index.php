<?php

define('BASE_PATH', __DIR__);

// ── 1. Load DB config ──────────────────────────────────────
require_once BASE_PATH . '/config/db.php';

// ── 2. Load Core ───────────────────────────────────────────
require_once BASE_PATH . '/src/Core/Database.php';
require_once BASE_PATH . '/src/Core/Session.php';
require_once BASE_PATH . '/src/Core/Router.php';

// ── 3. Load Middleware ────────────────────────────────────
require_once BASE_PATH . '/src/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/src/Middleware/CsrfMiddleware.php';
require_once BASE_PATH . '/src/Middleware/PermissionMiddleware.php';
require_once BASE_PATH . '/src/Middleware/RateLimitMiddleware.php';

// ── 4. Load Services ──────────────────────────────────────
require_once BASE_PATH . '/src/Services/LogManager.php';
require_once BASE_PATH . '/src/Services/RbacService.php';
require_once BASE_PATH . '/src/Services/AuthService.php';
require_once BASE_PATH . '/src/Services/BroadcastService.php';
require_once BASE_PATH . '/src/Services/FraudDetectionService.php';
require_once BASE_PATH . '/src/Services/TransactionService.php';
require_once BASE_PATH . '/src/Services/NotificationService.php';
require_once BASE_PATH . '/src/Services/StatementService.php';
require_once BASE_PATH . '/src/Services/SchedulerService.php';

// ── 5. Load Controllers ───────────────────────────────────
require_once BASE_PATH . '/src/Controllers/AuthController.php';
require_once BASE_PATH . '/src/Controllers/CustomerController.php';
require_once BASE_PATH . '/src/Controllers/LoanController.php';
require_once BASE_PATH . '/src/Controllers/AdminController.php';
require_once BASE_PATH . '/src/Controllers/BeneficiaryController.php';
require_once BASE_PATH . '/src/Controllers/NotificationController.php';
require_once BASE_PATH . '/src/Controllers/SupportController.php';
require_once BASE_PATH . '/src/Controllers/StatementController.php';

// ── 6. Start secure session ────────────────────────────────
Session::start();

// ── 7. Register routes & dispatch ─────────────────────────
$router = new Router();
require_once BASE_PATH . '/config/routes.php';
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
