<?php
class CostEstimateModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function forPost(int $postId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM cost_estimates WHERE post_id = ? LIMIT 1');
        $stmt->execute([$postId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function upsertForPost(int $postId, float $baseCost, string $currency = 'USD'): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO cost_estimates (post_id, base_cost, currency) VALUES (?, ?, ?)
             ON CONFLICT (post_id) DO UPDATE SET
                base_cost = EXCLUDED.base_cost,
                currency = EXCLUDED.currency,
                last_updated = CURRENT_TIMESTAMP'
        );
        $stmt->execute([$postId, $baseCost, $currency]);
    }
}
