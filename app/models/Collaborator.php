<?php
declare(strict_types=1);

namespace app\models;

class Collaborator extends Model
{
    protected string $table = 'collaborators';

    public const STATUS_ACTIVE  = 1;
    public const STATUS_DELETED = 3;

    /** All non-deleted collaborators ordered by the given column (used by dropdowns). */
    public function all(string $orderBy = 'created_at', string $dir = 'DESC'): array
    {
        $dir  = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
        $stmt = $this->db->query(
            "SELECT * FROM collaborators
             WHERE idstatus != " . self::STATUS_DELETED . "
             ORDER BY {$orderBy} {$dir}"
        );
        return $stmt->fetchAll();
    }

    /** All non-deleted collaborators with computed is_active flag and area name. */
    public function allWithStatus(): array
    {
        $stmt = $this->db->query(
            "SELECT c.*,
                    (c.exit_date IS NULL) AS is_active,
                    a.name               AS area_name
             FROM collaborators c
             LEFT JOIN areas a ON a.id = c.area_id
             WHERE c.idstatus != " . self::STATUS_DELETED . "
             ORDER BY c.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Single non-deleted collaborator with area name and linked user info.
     * Returns false for soft-deleted or missing rows.
     */
    public function findWithArea(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, a.name AS area_name,
                    u.id    AS linked_user_id,
                    u.email AS linked_user_email
             FROM collaborators c
             LEFT JOIN areas a ON a.id = c.area_id
             LEFT JOIN users u ON u.id = c.user_id
             WHERE c.id = ?
               AND c.idstatus != " . self::STATUS_DELETED . "
             LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create a collaborator and its linked system user atomically.
     *
     * The user is created first so its ID is available for the user_id FK.
     * If either INSERT fails the transaction rolls back and the exception is
     * re-thrown for the controller to handle.
     *
     * @param  array  $collabFields  Fields for collaborators table (must include idstatus)
     * @param  string $email         Validated, unique email for the new user
     * @param  string $password      Plain-text password (bcrypt-hashed here, cost 12)
     * @return int    New collaborator ID
     * @throws \Throwable            Re-throws on failure after rollback
     */
    public function createWithUser(array $collabFields, string $email, string $password): int
    {
        $this->db->beginTransaction();

        try {
            $userModel = new User();

            $userId = $userModel->insert([
                'name'     => $collabFields['name'],
                'email'    => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                'role'     => 'user',
                'idstatus' => User::STATUS_ACTIVE,
            ]);

            $collabFields['user_id'] = $userId;
            $collabId = $this->insert($collabFields);

            $this->db->commit();
            return $collabId;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Soft delete: sets idstatus=3 and records deleted_at.
     * Row is kept; supports.collaborator_id FK integrity is preserved.
     */
    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE collaborators
             SET idstatus = ?, deleted_at = NOW()
             WHERE id = ? AND idstatus != ?"
        );
        return $stmt->execute([self::STATUS_DELETED, $id, self::STATUS_DELETED]);
    }

    /** True when the collaborator is referenced by at least one support ticket. */
    public function hasSupports(int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM supports WHERE collaborator_id = ?'
        );
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Count currently active (non-deleted, no exit date) collaborators. */
    public function countActive(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM collaborators
             WHERE exit_date IS NULL AND idstatus != " . self::STATUS_DELETED
        )->fetchColumn();
    }

    /** Returns total, active and inactive counts excluding soft-deleted rows. */
    public function countStats(): array
    {
        $row = $this->db->query(
            "SELECT COUNT(*)                  AS total,
                    SUM(exit_date IS NULL)     AS active,
                    SUM(exit_date IS NOT NULL) AS inactive
             FROM collaborators
             WHERE idstatus != " . self::STATUS_DELETED
        )->fetch();

        return [
            'total'    => (int) $row['total'],
            'active'   => (int) $row['active'],
            'inactive' => (int) $row['inactive'],
        ];
    }
}
