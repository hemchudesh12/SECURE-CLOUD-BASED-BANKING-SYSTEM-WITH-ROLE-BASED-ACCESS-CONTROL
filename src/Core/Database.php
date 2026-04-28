<?php
/**
 * Database — PDO singleton for XAMPP/MySQL
 * Prepared statements ONLY — no string interpolation.
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST
                 . ';port=3306'
                 . ';dbname=' . DB_NAME
                 . ';charset=' . DB_CHARSET;

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]);
            } catch (PDOException $e) {
                // Never expose credentials
                error_log('DB connection failed: ' . $e->getMessage());
                http_response_code(500);
                include BASE_PATH . '/views/errors/500.php';
                exit;
            }
        }
        return self::$instance;
    }
}
