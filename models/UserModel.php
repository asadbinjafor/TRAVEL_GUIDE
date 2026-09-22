<?php
class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $stmt->execute([trim($email)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password_hash, role, is_verified, profile_picture)
             VALUES (?, ?, ?, ?, ?, ?) RETURNING id'
        );
        $stmt->bindValue(1, $data['name']);
        $stmt->bindValue(2, strtolower(trim($data['email'])));
        $stmt->bindValue(3, $data['password_hash']);
        $stmt->bindValue(4, $data['role']);
        $stmt->bindValue(5, !empty($data['is_verified']), PDO::PARAM_BOOL);
        $stmt->bindValue(6, $data['profile_picture'] ?? null);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function updateProfile(int $id, string $name, string $email, ?string $picture): bool
    {
        if ($picture !== null) {
            $stmt = $this->db->prepare('UPDATE users SET name = ?, email = ?, profile_picture = ? WHERE id = ?');
            return $stmt->execute([$name, $email, $picture, $id]);
        }
        $stmt = $this->db->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        return $stmt->execute([$name, $email, $id]);
    }

    public function updatePassword(int $id, string $hash): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        return $stmt->execute([$hash, $id]);
    }

    public function setRememberToken(int $id, string $plainToken): void
    {
        $hash = hash('sha256', $plainToken);
        $stmt = $this->db->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
        $stmt->execute([$hash, $id]);
    }

    public function clearRememberToken(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE users SET remember_token = NULL WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function findByRememberToken(int $id, string $plainToken): ?array
    {
        $hash = hash('sha256', $plainToken);
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE id = ? AND remember_token = ? LIMIT 1'
        );
        $stmt->execute([$id, $hash]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function allForManagement(): array
    {
        return $this->db->query(
            "SELECT id, name, email, role, is_verified, created_at
             FROM users ORDER BY is_verified ASC, role ASC, created_at DESC"
        )->fetchAll();
    }

    public function hasVerifiedAdmin(): bool
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_verified = TRUE"
        );
        return (int) $stmt->fetchColumn() > 0;
    }

    public function countVerifiedAdmins(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_verified = TRUE"
        );
        return (int) $stmt->fetchColumn();
    }

    public function countByRole(): array
    {
        $rows = $this->db->query(
            "SELECT role, COUNT(*) AS cnt FROM users GROUP BY role"
        )->fetchAll();
        $out = ['admin' => 0, 'scout' => 0, 'user' => 0];
        foreach ($rows as $r) {
            $out[$r['role']] = (int) $r['cnt'];
        }
        return $out;
    }

    public function setVerified(int $id, int $verified): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET is_verified = ? WHERE id = ?');
        $stmt->bindValue(1, $verified === 1, PDO::PARAM_BOOL);
        $stmt->bindValue(2, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteUser(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ? AND role != ?');
        return $stmt->execute([$id, 'admin']);
    }
}
