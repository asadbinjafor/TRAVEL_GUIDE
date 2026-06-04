<?php
class CommentModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function forPost(int $postId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, u.name AS user_name FROM comments c
             JOIN users u ON u.id = c.user_id
             WHERE c.post_id = ? ORDER BY c.created_at DESC"
        );
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    public function create(int $postId, int $userId, string $content): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)'
        );
        $stmt->execute([$postId, $userId, $content]);
        return (int) $this->db->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM comments WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM comments WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function deleteOwned(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM comments WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $userId]);
    }

    public function allForAdmin(): array
    {
        return $this->db->query(
            "SELECT c.*, u.name AS user_name, p.title AS post_title FROM comments c
             JOIN users u ON u.id = c.user_id
             JOIN posts p ON p.id = c.post_id
             ORDER BY c.created_at DESC"
        )->fetchAll();
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM comments')->fetchColumn();
    }
}
