<?php
class Security
{
    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $proto = strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]));
            return $proto === 'https';
        }
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }

    public static function initSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $secure = self::isHttps();
        ini_set('session.use_strict_mode', '1');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => $secure,
        ]);
        session_start();
        if (empty($_SESSION['_init'])) {
            session_regenerate_id(true);
            $_SESSION['_init'] = 1;
        }
    }

    public static function headers(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header('Cache-Control: private, no-store, max-age=0');
    }

    public static function csrfToken(): string
    {
        self::initSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' .
            self::e(self::csrfToken()) . '">';
    }

    public static function verifyCsrf(?string $token): bool
    {
        self::initSession();
        if (!is_string($token) || $token === '' || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function requireCsrfPost(): void
    {
        if (!self::verifyCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            die('Invalid CSRF token.');
        }
    }

    public static function requireCsrfRequest(): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;
        if (!self::verifyCsrf(is_string($token) ? $token : null)) {
            self::json(['success' => false, 'error' => 'Invalid CSRF token.'], 403);
        }
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function json(array $payload, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }

    public static function validateImageUpload(array $file): ?string
    {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'Upload failed.';
        }
        if (($file['size'] ?? 0) <= 0 || $file['size'] > UPLOAD_MAX_BYTES) {
            return 'Image has an invalid size.';
        }
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return 'Upload source is invalid.';
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return 'Only JPEG, PNG, or WebP images allowed.';
        }
        return null;
    }

    public static function saveUpload(array $file, string $dir, string $prefix, string $storageFolder): ?string
    {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (self::validateImageUpload($file) !== null) {
            return null;
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
        $name = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        if (Storage::isConfigured()) {
            return Storage::upload($file['tmp_name'], $mime, $storageFolder, $name);
        }
        $path = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $name;
        return move_uploaded_file($file['tmp_name'], $path) ? $name : null;
    }
}
