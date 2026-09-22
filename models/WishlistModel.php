<?php
class WishlistModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function add(int $userId, int $postId): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO wishlist (user_id, post_id) VALUES (?, ?)
             ON CONFLICT (user_id, post_id) DO NOTHING'
        );
        return $stmt->execute([$userId, $postId]);
    }

    public function remove(int $userId, int $postId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM wishlist WHERE user_id = ? AND post_id = ?');
        return $stmt->execute([$userId, $postId]);
    }

    public function forUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT w.id AS wishlist_id, p.id, p.title, p.country, p.cost_level, p.genre
             FROM wishlist w JOIN posts p ON p.id = w.post_id
             WHERE w.user_id = ? AND p.status = 'approved' ORDER BY w.added_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function has(int $userId, int $postId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM wishlist WHERE user_id = ? AND post_id = ? LIMIT 1');
        $stmt->execute([$userId, $postId]);
        return (bool) $stmt->fetchColumn();
    }
}
