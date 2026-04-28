<?php
/**
 * bin/run-scheduler.php — run via Windows Task Scheduler every minute.
 * Command: C:\xampp\php\php.exe C:\xampp\htdocs\banking-system\bin\run-scheduler.php
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/src/Core/Database.php';
require_once BASE_PATH . '/src/Core/Session.php';
require_once BASE_PATH . '/src/Services/LogManager.php';
require_once BASE_PATH . '/src/Services/BroadcastService.php';
require_once BASE_PATH . '/src/Services/FraudDetectionService.php';
require_once BASE_PATH . '/src/Services/TransactionService.php';
require_once BASE_PATH . '/src/Services/SchedulerService.php';

// CLI session stub
if (!session_id()) {
    session_start();
}
if (empty($_SESSION['user_id'])) {
    // Use system user
    $_SESSION['user_id']  = 1;
    $_SESSION['role_id']  = 1;
    $_SESSION['username'] = 'system';
    $_SESSION['role']     = 'administrator';
}

SchedulerService::runDue();
