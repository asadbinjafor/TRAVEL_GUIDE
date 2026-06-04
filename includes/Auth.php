<?php
require_once __DIR__ . '/../models/UserModel.php';

class Auth
{
    public static function user(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        return [
            'id' => (int) $_SESSION['user_id'],
            'name' => $_SESSION['name'] ?? '',
            'role' => $_SESSION['role'] ?? '',
            'is_verified' => (int) ($_SESSION['is_verified'] ?? 0),
        ];
    }

    public static function login(array $user): void
    {
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['is_verified'] = (int) $user['is_verified'];
    }

    public static function logout(): void
    {
        $uid = $_SESSION['user_id'] ?? null;
        if ($uid) {
            (new UserModel())->clearRememberToken((int) $uid);
        }
        self::clearRememberCookie();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function setRemember(int $userId, string $token): void
    {
        setcookie('remember_me', $userId . ':' . $token, [
            'expires' => time() + (86400 * REMEMBER_DAYS),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }

    public static function clearRememberCookie(): void
    {
        setcookie('remember_me', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }

    public static function tryRememberLogin(): void
    {
        if (!empty($_SESSION['user_id']) || empty($_COOKIE['remember_me'])) {
            return;
        }
        $parts = explode(':', $_COOKIE['remember_me'], 2);
        if (count($parts) !== 2) {
            return;
        }
        $user = (new UserModel())->findByRememberToken((int) $parts[0], $parts[1]);
        if ($user) {
            self::login($user);
        }
    }

    public static function requireLogin(): void
    {
        if (!self::user()) {
            redirect('/login');
        }
    }

    public static function requireVerified(): void
    {
        self::requireLogin();
        if (!self::user()['is_verified']) {
            redirect('/?pending=1');
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        if (!in_array(self::user()['role'], $roles, true)) {
            http_response_code(403);
            die('Access denied.');
        }
    }

    public static function requireAdmin(): void
    {
        self::requireRole('admin');
    }

    public static function requireScout(): void
    {
        self::requireRole('scout');
        self::requireVerified();
    }

    public static function requireGeneralUser(): void
    {
        self::requireRole('user');
        self::requireVerified();
    }

    public static function isVerifiedGeneralUser(): bool
    {
        $u = self::user();
        return $u && $u['role'] === 'user' && $u['is_verified'];
    }
}
