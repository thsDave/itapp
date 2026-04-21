<?php
declare(strict_types=1);

namespace app\models;

use PDO;

class Support extends Model
{
    protected string $table = 'supports';

    public const LEVELS   = ['low', 'medium', 'high', 'critical'];
    public const STATUSES = ['open', 'in_progress', 'closed'];

    // ── List / filter ─────────────────────────────────────────────────

    public function filter(array $f = []): array
    {
        $where  = [];
        $params = [];

        if (!empty($f['collaborator_id'])) {
            $where[]  = 's.collaborator_id = ?';
            $params[] = (int) $f['collaborator_id'];
        }
        if (!empty($f['level']) && in_array($f['level'], self::LEVELS, true)) {
            $where[]  = 's.attention_level = ?';
            $params[] = $f['level'];
        }
        if (!empty($f['status']) && in_array($f['status'], self::STATUSES, true)) {
            $where[]  = 's.status = ?';
            $params[] = $f['status'];
        }
        if (!empty($f['date_from'])) {
            $where[]  = 'DATE(s.created_at) >= ?';
            $params[] = $f['date_from'];
        }
        if (!empty($f['date_to'])) {
            $where[]  = 'DATE(s.created_at) <= ?';
            $params[] = $f['date_to'];
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT s.*,
                       c.name     AS collaborator_name,
                       c.position AS collaborator_position,
                       u.name     AS attended_by_name
                FROM supports s
                INNER JOIN collaborators c ON c.id = s.collaborator_id AND c.idstatus != 3
                LEFT  JOIN users         u ON u.id = s.user_id         AND u.idstatus != 3
                {$whereSQL}
                ORDER BY FIELD(s.attention_level,'critical','high','medium','low'),
                         s.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findWithRelations(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT s.*,
                    c.name     AS collaborator_name,
                    c.position AS collaborator_position,
                    u.name     AS attended_by_name
             FROM supports s
             INNER JOIN collaborators c ON c.id = s.collaborator_id AND c.idstatus != 3
             LEFT  JOIN users         u ON u.id = s.user_id         AND u.idstatus != 3
             WHERE s.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function byCollaborator(int $collaboratorId, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT s.*, u.name AS attended_by_name
             FROM supports s
             LEFT JOIN users u ON u.id = s.user_id AND u.idstatus != 3
             WHERE s.collaborator_id = ?
             ORDER BY s.created_at DESC
             LIMIT {$limit}"
        );
        $stmt->execute([$collaboratorId]);
        return $stmt->fetchAll();
    }

    // ── Aggregate stats ───────────────────────────────────────────────

    public function countByStatus(): array
    {
        $rows = $this->db->query(
            "SELECT s.status, COUNT(*) AS total
             FROM supports s
             INNER JOIN collaborators c ON c.id = s.collaborator_id AND c.idstatus != 3
             GROUP BY s.status"
        )->fetchAll();

        $map = array_fill_keys(self::STATUSES, 0);
        foreach ($rows as $r) {
            $map[$r['status']] = (int) $r['total'];
        }
        return $map;
    }

    public function countByLevel(): array
    {
        $rows = $this->db->query(
            "SELECT s.attention_level, COUNT(*) AS total
             FROM supports s
             INNER JOIN collaborators c ON c.id = s.collaborator_id AND c.idstatus != 3
             GROUP BY s.attention_level"
        )->fetchAll();

        $map = array_fill_keys(self::LEVELS, 0);
        foreach ($rows as $r) {
            $map[$r['attention_level']] = (int) $r['total'];
        }
        return $map;
    }

    /** Ticket counts per month for the last $months months (raw DB rows). */
    public function perMonth(int $months = 12): array
    {
        $stmt = $this->db->prepare(
            "SELECT DATE_FORMAT(s.created_at, '%Y-%m') AS month,
                    COUNT(*) AS total
             FROM supports s
             INNER JOIN collaborators c ON c.id = s.collaborator_id AND c.idstatus != 3
             WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
             GROUP BY month
             ORDER BY month ASC"
        );
        $stmt->execute([$months]);
        return $stmt->fetchAll();
    }

    /** Top N collaborators by ticket count (all time). */
    public function perCollaboratorTop(int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.name, COUNT(s.id) AS total
             FROM supports s
             INNER JOIN collaborators c ON c.id = s.collaborator_id AND c.idstatus != 3
             GROUP BY s.collaborator_id, c.name
             ORDER BY total DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /** Top N collaborators by ticket count for the current calendar month. */
    public function perCollaboratorTopCurrentMonth(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.name, COUNT(s.id) AS total
             FROM supports s
             INNER JOIN collaborators c ON c.id = s.collaborator_id AND c.idstatus != 3
             WHERE YEAR(s.created_at)  = YEAR(CURDATE())
               AND MONTH(s.created_at) = MONTH(CURDATE())
             GROUP BY s.collaborator_id, c.name
             ORDER BY total DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /** Most urgent open/in_progress tickets for the dashboard table. */
    public function recentOpen(int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            "SELECT s.id, s.title, s.attention_level, s.status, s.created_at,
                    c.name AS collaborator_name
             FROM supports s
             INNER JOIN collaborators c ON c.id = s.collaborator_id AND c.idstatus != 3
             WHERE s.status IN ('open','in_progress')
             ORDER BY FIELD(s.attention_level,'critical','high','medium','low'),
                      s.created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
