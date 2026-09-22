<?php
class PostRequestModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function create(int $scoutId, array $postData, ?int $originalPostId = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO post_requests (scout_id, post_data, original_post_id, status)
             VALUES (?, CAST(? AS jsonb), ?, ?) RETURNING id'
        );
        $stmt->execute([
            $scoutId,
            json_encode($postData, JSON_UNESCAPED_UNICODE),
            $originalPostId,
            'pending',
        ]);
        return (int) $stmt->fetchColumn();
    }

    public function byScout(int $scoutId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM post_requests WHERE scout_id = ? ORDER BY requested_at DESC'
        );
        $stmt->execute([$scoutId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['post_data'] = json_decode($r['post_data'], true);
        }
        return $rows;
    }

    public function find(int $id, int $scoutId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM post_requests WHERE id = ? AND scout_id = ? LIMIT 1'
        );
        $stmt->execute([$id, $scoutId]);
        $row = $stmt->fetch();
        if ($row) {
            $row['post_data'] = json_decode($row['post_data'], true);
        }
        return $row ?: null;
    }

    public function update(int $id, int $scoutId, array $postData): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE post_requests SET post_data = CAST(? AS jsonb) WHERE id = ? AND scout_id = ? AND status = 'pending'"
        );
        $stmt->execute([json_encode($postData, JSON_UNESCAPED_UNICODE), $id, $scoutId]);
        return $stmt->rowCount() === 1;
    }

    public function delete(int $id, int $scoutId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM post_requests WHERE id = ? AND scout_id = ? AND status = 'pending'"
        );
        $stmt->execute([$id, $scoutId]);
        return $stmt->rowCount() === 1;
    }

    public function pendingAll(): array
    {
        $rows = $this->db->query(
            "SELECT pr.*, u.name AS scout_name FROM post_requests pr
             JOIN users u ON u.id = pr.scout_id
             WHERE pr.status = 'pending' ORDER BY pr.requested_at ASC"
        )->fetchAll();
        foreach ($rows as &$r) {
            $r['post_data'] = json_decode($r['post_data'], true);
        }
        return $rows;
    }

    public function findAny(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM post_requests WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $row['post_data'] = json_decode($row['post_data'], true);
        }
        return $row ?: null;
    }

    public function setStatus(int $id, string $status, ?string $reason = null): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE post_requests SET status = ?, reject_reason = ? WHERE id = ?'
        );
        return $stmt->execute([$status, $reason, $id]);
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM post_requests WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function countPending(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM post_requests WHERE status = 'pending'")->fetchColumn();
    }
}
