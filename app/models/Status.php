<?php
declare(strict_types=1);

namespace app\models;

class Status extends Model
{
    protected string $table = 'status';

    /**
     * Returns the statuses available for selection in user forms
     * (active + inactive only — 'deleted' is never set via a form).
     *
     * @return array<int, array{idstatus: int, status: string}>
     */
    public function forUsers(): array
    {
        $stmt = $this->db->query(
            "SELECT idstatus, status
             FROM `status`
             WHERE status != 'deleted'
               AND deleted_at IS NULL
             ORDER BY idstatus ASC"
        );
        return $stmt->fetchAll();
    }
}
