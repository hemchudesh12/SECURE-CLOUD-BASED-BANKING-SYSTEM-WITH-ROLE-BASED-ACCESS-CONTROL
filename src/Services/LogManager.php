<?php
/**
 * LogManager — centralized audit logger with cryptographic chaining.
 * Every log entry stores a SHA-256 hash chained to the previous entry.
 */
class LogManager
{
    public static function log(
        string $action,
        string $entityType = '',
        string $outcome    = 'success',
        array  $metadata   = [],
        ?int   $entityId   = null
    ): void {
        try {
            $pdo = Database::getInstance();

            // Get previous entry hash for chaining
            $prev = $pdo->query(
                "SELECT entry_hash FROM audit_logs ORDER BY id DESC LIMIT 1"
            )->fetch();
            $previousHash = $prev ? $prev['entry_hash'] : hash('sha256', 'GENESIS_BLOCK');

            $userId    = Session::get('user_id');
            $username  = Session::get('username', 'guest');
            $sessionId = session_id() ?: '';
            $ip        = self::getClientIp();
            $ua        = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
            $now       = date('Y-m-d H:i:s') . '.' . sprintf('%03d', (int)(microtime(true) * 1000) % 1000);

            // Build entry content for hashing
            $entryContent = implode('|', [
                $userId ?? 0, $username, $action, $entityType,
                $outcome, $ip, $now, $previousHash,
            ]);
            $entryHash = hash('sha256', $entryContent);

            $stmt = $pdo->prepare("
                INSERT INTO audit_logs
                    (user_id, username, session_id, action, entity_type, entity_id,
                     source_ip, user_agent, outcome, metadata, previous_hash, entry_hash, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId, $username, $sessionId, $action, $entityType, $entityId,
                $ip, $ua, $outcome,
                !empty($metadata) ? json_encode($metadata) : null,
                $previousHash, $entryHash, $now,
            ]);
        } catch (Throwable $e) {
            // Audit log must never crash the application
            error_log('LogManager failed: ' . $e->getMessage());
        }
    }

    private static function getClientIp(): string
    {
        // Check for forwarded IP (behind proxy/Azure Front Door)
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    /**
     * Verify integrity of the audit log chain.
     * Returns array with 'valid' bool and 'broken_at' entry id if invalid.
     */
    public static function verifyChain(): array
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->query("SELECT * FROM audit_logs ORDER BY id ASC");
        $rows = $stmt->fetchAll();

        $previousHash = hash('sha256', 'GENESIS_BLOCK');
        foreach ($rows as $row) {
            $entryContent = implode('|', [
                $row['user_id'] ?? 0,
                $row['username'],
                $row['action'],
                $row['entity_type'],
                $row['outcome'],
                $row['source_ip'],
                $row['created_at'],
                $previousHash,
            ]);
            $expectedHash = hash('sha256', $entryContent);

            if ($row['entry_hash'] !== $expectedHash) {
                return ['valid' => false, 'broken_at' => $row['id']];
            }
            $previousHash = $row['entry_hash'];
        }
        return ['valid' => true, 'broken_at' => null];
    }
}
