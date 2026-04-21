<?php
declare(strict_types=1);

namespace app\models;

class Role extends Model
{
    protected string $table = 'roles';

    public const PROTECTED_ROLE = 'admin';

    // ── Core CRUD ─────────────────────────────────────────────────────

    /**
     * All roles with user count (excluding soft-deleted users).
     */
    public function all(string $orderBy = 'idrole', string $dir = 'ASC'): array
    {
        $stmt = $this->db->query(
            "SELECT r.*,
                    COUNT(DISTINCT u.id) AS user_count
             FROM roles r
             LEFT JOIN users u
               ON u.role = r.role_name
              AND u.idstatus != 3
             GROUP BY r.idrole
             ORDER BY r.{$orderBy} {$dir}"
        );
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM roles WHERE idrole = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByName(string $name): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM roles WHERE role_name = ? LIMIT 1"
        );
        $stmt->execute([$name]);
        return $stmt->fetch();
    }

    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare(
                "SELECT idrole FROM roles WHERE role_name = ? AND idrole != ? LIMIT 1"
            );
            $stmt->execute([$name, $excludeId]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT idrole FROM roles WHERE role_name = ? LIMIT 1"
            );
            $stmt->execute([$name]);
        }
        return (bool) $stmt->fetch();
    }

    public function insert(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO roles (role_name, description) VALUES (?, ?)"
        );
        $stmt->execute([
            $data['role_name'],
            $data['description'] !== '' ? $data['description'] : null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a role. The admin role_name itself is immutable at DB level.
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE roles
             SET role_name = ?, description = ?, updated_at = NOW()
             WHERE idrole = ? AND role_name != ?"
        );
        return $stmt->execute([
            $data['role_name'],
            $data['description'] !== '' ? $data['description'] : null,
            $id,
            self::PROTECTED_ROLE,
        ]);
    }

    /**
     * Hard delete. Admin role is protected at DB level by the WHERE clause.
     * Returns false if the role does not exist or is admin.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM roles WHERE idrole = ? AND role_name != ?"
        );
        return $stmt->execute([$id, self::PROTECTED_ROLE]);
    }

    // ── Helpers ───────────────────────────────────────────────────────

    /**
     * Count of non-deleted users assigned to this role.
     */
    public function userCount(int $id): int
    {
        $role = $this->find($id);
        if (!$role) {
            return 0;
        }
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM users WHERE role = ? AND idstatus != 3"
        );
        $stmt->execute([$role['role_name']]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * All role names as a flat array — used by UserController for validation.
     */
    public function getRoleNames(): array
    {
        $stmt = $this->db->query("SELECT role_name FROM roles ORDER BY idrole");
        return array_column($stmt->fetchAll(), 'role_name');
    }

    // ── Module access ─────────────────────────────────────────────────

    /**
     * All configurable modules with a flag indicating whether this role has access.
     * Non-configurable (admin-only) modules are excluded from the list.
     */
    public function getConfigurableModulesWithAccess(int $idrole): array
    {
        $stmt = $this->db->prepare(
            "SELECT m.*,
                    IF(rma.idrole IS NOT NULL, 1, 0) AS has_access
             FROM modules m
             LEFT JOIN role_module_access rma
               ON rma.idmodule = m.idmodule AND rma.idrole = ?
             WHERE m.is_configurable = 1
             ORDER BY m.sort_order"
        );
        $stmt->execute([$idrole]);
        return $stmt->fetchAll();
    }

    /**
     * All modules (configurable + admin-only), used for display on admin's edit form.
     */
    public function getAllModulesWithAccess(int $idrole): array
    {
        $stmt = $this->db->prepare(
            "SELECT m.*,
                    IF(rma.idrole IS NOT NULL, 1, 0) AS has_access
             FROM modules m
             LEFT JOIN role_module_access rma
               ON rma.idmodule = m.idmodule AND rma.idrole = ?
             ORDER BY m.sort_order"
        );
        $stmt->execute([$idrole]);
        return $stmt->fetchAll();
    }

    /**
     * Replace module access for a role.
     * Only configurable modules are inserted — non-configurable IDs are silently ignored.
     */
    public function syncModuleAccess(int $idrole, array $moduleIds): void
    {
        $this->db->prepare(
            "DELETE FROM role_module_access WHERE idrole = ?"
        )->execute([$idrole]);

        if (empty($moduleIds)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($moduleIds), '?'));
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO role_module_access (idrole, idmodule)
             SELECT ?, idmodule FROM modules
             WHERE idmodule IN ({$placeholders}) AND is_configurable = 1"
        );
        $stmt->execute([$idrole, ...array_map('intval', $moduleIds)]);
    }
}
