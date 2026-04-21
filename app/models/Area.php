<?php
declare(strict_types=1);

namespace app\models;

class Area extends Model
{
    protected string $table = 'areas';

    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare(
                'SELECT id FROM areas WHERE name = ? AND id != ? LIMIT 1'
            );
            $stmt->execute([$name, $excludeId]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT id FROM areas WHERE name = ? LIMIT 1'
            );
            $stmt->execute([$name]);
        }
        return (bool) $stmt->fetch();
    }

    public function collaboratorCount(int $id): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM collaborators WHERE area_id = ?'
        );
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }
}
