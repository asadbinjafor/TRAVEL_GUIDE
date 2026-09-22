<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once __DIR__ . '/Storage.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/Auth.php';

set_exception_handler(function (Throwable $exception): void {
    if (APP_DEBUG) {
        throw $exception;
    }
    error_log($exception::class . ': application request failed');
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>Service unavailable</title><body><h1>Service temporarily unavailable</h1><p>Please try again shortly.</p></body></html>';
});

Security::initSession();
Security::headers();
ensureUploadDirs();
Auth::tryRememberLogin();

spl_autoload_register(function (string $class): void {
    foreach ([ROOT_DIR . '/controllers/' . $class . '.php', ROOT_DIR . '/models/' . $class . '.php'] as $path) {
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});

function view(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $authUser = Auth::user();
    $viewFile = ROOT_DIR . '/views/' . $name . '.php';
    if (!is_file($viewFile)) {
        throw new RuntimeException('View not found.');
    }
    require ROOT_DIR . '/views/layouts/header.php';
    require $viewFile;
    require ROOT_DIR . '/views/layouts/footer.php';
}

function url(string $path = '/', array $query = []): string
{
    $path = '/' . trim($path, '/');
    $base = rtrim(BASE_URL, '/') . '/index.php';
    $target = $path === '/' ? $base : $base . '?route=' . rawurlencode($path);
    if ($query !== []) {
        $target .= (str_contains($target, '?') ? '&' : '?') . http_build_query($query);
    }
    return $target;
}

function asset(string $path): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function uploadUrl(?string $reference, string $type): ?string
{
    if ($reference === null || $reference === '') {
        return null;
    }
    if (filter_var($reference, FILTER_VALIDATE_URL)) {
        return $reference;
    }
    $folder = $type === 'profile' ? 'profiles' : 'posts';
    return asset('public/uploads/' . $folder . '/' . rawurlencode(basename($reference)));
}

function redirect(string $path, array $query = []): never
{
    header('Location: ' . url($path, $query), true, 303);
    exit;
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return null;
    }
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function baseCostFromLevel(string $level): float
{
    return (float) (COST_LEVEL_MAP[$level] ?? 1000);
}
