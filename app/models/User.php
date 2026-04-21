<?php
declare(strict_types=1);

namespace app\models;

class User extends Model
{
    protected string $table = 'users';

    // idstatus constants — mirrors the status seed rows.
    public const STATUS_ACTIVE   = 1;
    public const STATUS_INACTIVE = 2;
    public const STATUS_DELETED  = 3;

    /**
     * All non-deleted users with their status label joined.
     * Overrides the base all() to exclude soft-deleted rows.
     */
    public function all(string $orderBy = 'created_at', string $dir = 'DESC'): array
    {
        $stmt = $this->db->query(
            "SELECT u.*, s.status AS status_name
             FROM users u
             JOIN `status` s ON s.idstatus = u.idstatus
             WHERE u.idstatus != " . self::STATUS_DELETED . "
             ORDER BY u.{$orderBy} {$dir}"
        );
        return $stmt->fetchAll();
    }

    /**
     * Find a single non-deleted user with status label.
     * Returns false for deleted users (treated as non-existent).
     */
    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT u.*, s.status AS status_name
             FROM users u
             JOIN `status` s ON s.idstatus = u.idstatus
             WHERE u.id = ?
               AND u.idstatus != " . self::STATUS_DELETED . "
             LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT u.*, s.status AS status_name
             FROM users u
             JOIN `status` s ON s.idstatus = u.idstatus
             WHERE u.email = ?
             LIMIT 1"
        );
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Used by AuthMiddleware to verify the session user is still active.
     */
    public function findActive(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT u.*, s.status AS status_name
             FROM users u
             JOIN `status` s ON s.idstatus = u.idstatus
             WHERE u.id      = ?
               AND u.idstatus = " . self::STATUS_ACTIVE . "
             LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare(
                'SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1'
            );
            $stmt->execute([$email, $excludeId]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT id FROM users WHERE email = ? LIMIT 1'
            );
            $stmt->execute([$email]);
        }
        return (bool) $stmt->fetch();
    }

    /**
     * Toggles between active (1) and inactive (2).
     * Never touches a deleted (3) row.
     */
    public function toggleStatus(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users
             SET idstatus = IF(idstatus = ?, ?, ?)
             WHERE id = ? AND idstatus != ?"
        );
        return $stmt->execute([
            self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_ACTIVE,
            $id,
            self::STATUS_DELETED,
        ]);
    }

    /**
     * Soft delete: sets idstatus=3 and records deleted_at timestamp.
     * Row is kept; supports.user_id FK integrity is preserved.
     */
    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET idstatus = ?, deleted_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([self::STATUS_DELETED, $id]);
    }

    public function countByRole(): array
    {
        $stmt = $this->db->query(
            "SELECT role, COUNT(*) AS total
             FROM users
             WHERE idstatus != " . self::STATUS_DELETED . "
             GROUP BY role"
        );
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['role']] = (int) $row['total'];
        }
        return $result;
    }
}
