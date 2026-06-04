<?php
class PostModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function approvedLatest(int $limit = 6): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.name AS scout_name FROM posts p
             JOIN users u ON u.id = p.scout_id
             WHERE p.status = 'approved' ORDER BY p.created_at DESC LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function approvedAll(): array
    {
        return $this->db->query(
            "SELECT p.*, u.name AS scout_name FROM posts p
             JOIN users u ON u.id = p.scout_id
             WHERE p.status = 'approved' ORDER BY p.created_at DESC"
        )->fetchAll();
    }

    public function findApproved(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.name AS scout_name FROM posts p
             JOIN users u ON u.id = p.scout_id
             WHERE p.id = ? AND p.status = 'approved' LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function byScout(int $scoutId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM posts WHERE scout_id = ? AND status = 'approved' ORDER BY created_at DESC"
        );
        $stmt->execute([$scoutId]);
        return $stmt->fetchAll();
    }

    public function search(string $q): array
    {
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            "SELECT p.*, u.name AS scout_name FROM posts p
             JOIN users u ON u.id = p.scout_id
             WHERE p.status = 'approved' AND (p.title LIKE ? OR p.country LIKE ?)
             ORDER BY p.title ASC"
        );
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    public function filter(?string $country, ?string $genre, ?string $cost): array
    {
        $sql = "SELECT p.*, u.name AS scout_name FROM posts p
                JOIN users u ON u.id = p.scout_id WHERE p.status = 'approved'";
        $params = [];
        if ($country !== null && $country !== '') {
            $sql .= ' AND p.country = ?';
            $params[] = $country;
        }
        if ($genre !== null && $genre !== '') {
            $sql .= ' AND p.genre = ?';
            $params[] = $genre;
        }
        if ($cost !== null && $cost !== '') {
            $sql .= ' AND p.cost_level = ?';
            $params[] = $cost;
        }
        $sql .= ' ORDER BY p.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countries(): array
    {
        return $this->db->query(
            "SELECT DISTINCT country FROM posts WHERE status = 'approved' ORDER BY country"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    public function createFromData(int $scoutId, array $d, string $status = 'approved'): int
    {
        $images = isset($d['image_paths']) ? json_encode($d['image_paths']) : null;
        $stmt = $this->db->prepare(
            'INSERT INTO posts (scout_id, title, short_history, country, genre, cost_level, travel_medium_info, image_paths, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $scoutId,
            $d['title'],
            $d['short_history'],
            $d['country'],
            $d['genre'],
            $d['cost_level'],
            $d['travel_medium_info'],
            $images,
            $status,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool
    {
        $images = isset($d['image_paths']) ? json_encode($d['image_paths']) : ($d['image_paths_json'] ?? null);
        $stmt = $this->db->prepare(
            'UPDATE posts SET title = ?, short_history = ?, country = ?, genre = ?, cost_level = ?,
             travel_medium_info = ?, image_paths = COALESCE(?, image_paths), updated_at = NOW() WHERE id = ?'
        );
        return $stmt->execute([
            $d['title'],
            $d['short_history'],
            $d['country'],
            $d['genre'],
            $d['cost_level'],
            $d['travel_medium_info'],
            $images,
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM posts WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function allForAdmin(): array
    {
        return $this->db->query(
            "SELECT p.*, u.name AS scout_name FROM posts p
             JOIN users u ON u.id = p.scout_id ORDER BY p.created_at DESC"
        )->fetchAll();
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM posts')->fetchColumn();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM posts WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
