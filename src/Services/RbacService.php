<?php
/**
 * RbacService — loads permission cache into session at login.
 */
class RbacService
{
    /** Load all permissions for a given role into session */
    public static function loadPermissions(int $roleId): array
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT p.action_key
            FROM permissions p
            JOIN role_permissions rp ON rp.permission_id = p.id
            WHERE rp.role_id = ?
        ");
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Get all roles */
    public static function getAllRoles(): array
    {
        $pdo = Database::getInstance();
        return $pdo->query("SELECT * FROM roles ORDER BY id")->fetchAll();
    }

    /** Get all permissions */
    public static function getAllPermissions(): array
    {
        $pdo = Database::getInstance();
        return $pdo->query("SELECT * FROM permissions ORDER BY module, action_key")->fetchAll();
    }

    /** Get permission IDs assigned to a role */
    public static function getRolePermissionIds(int $roleId): array
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Save permissions for a role (replace all) */
    public static function saveRolePermissions(int $roleId, array $permissionIds): void
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $del = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $del->execute([$roleId]);

            if (!empty($permissionIds)) {
                $ins = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                foreach ($permissionIds as $pid) {
                    $ins->execute([$roleId, (int)$pid]);
                }
            }
            $pdo->commit();
            LogManager::log('PERMISSION_CHANGED', 'role', 'success', [
                'role_id'     => $roleId,
                'permissions' => $permissionIds,
            ], $roleId);
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
