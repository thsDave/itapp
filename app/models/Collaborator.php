<?php
declare(strict_types=1);

namespace app\models;

class Collaborator extends Model
{
    protected string $table = 'collaborators';

    // idstatus constants — mirrors the status seed rows.
    public const STATUS_ACTIVE  = 1;
    public const STATUS_DELETED = 3;

    /** All non-deleted collaborators ordered by the given column (used by dropdowns). */
    public function all(string $orderBy = 'created_at', string $dir = 'DESC'): array
    {
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
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

    /** Single non-deleted collaborator row with area name (for show/edit pages). */
    public function findWithArea(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, a.name AS area_name
             FROM collaborators c
             LEFT JOIN areas a ON a.id = c.area_id
             WHERE c.id = ?
               AND c.idstatus != " . self::STATUS_DELETED . "
             LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Soft delete: sets idstatus=3 (deleted) and records deleted_at timestamp.
     * The row is kept; supports.collaborator_id FK integrity is preserved.
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

    /**
     * True when the collaborator is referenced by at least one support ticket.
     * Kept for informational use (e.g. display warnings).
     */
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
