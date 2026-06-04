<?php
require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/Auth.php';

Security::initSession();
Security::headers();
ensureUploadDirs();
Auth::tryRememberLogin();

spl_autoload_register(function (string $class): void {
    foreach ([
        ROOT_DIR . '/controllers/' . $class . '.php',
        ROOT_DIR . '/models/' . $class . '.php',
    ] as $path) {
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});

function view(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    // Always set after extract so view data cannot overwrite session nav user.
    $authUser = Auth::user();
    $viewFile = ROOT_DIR . '/views/' . $name . '.php';
    if (!is_file($viewFile)) {
        http_response_code(500);
        die('View not found: ' . Security::e($name));
    }
    require ROOT_DIR . '/views/layouts/header.php';
    require $viewFile;
    require ROOT_DIR . '/views/layouts/footer.php';
}

/** Page URL without .htaccess (uses index.php?route=) */
function url(string $path = '/', array $query = []): string
{
    $path = '/' . trim($path, '/');
    $base = rtrim(BASE_URL, '/') . '/index.php';
    if ($path === '/') {
        $u = $base;
    } else {
        $u = $base . '?route=' . rawurlencode($path);
    }
    if ($query !== []) {
        $u .= (str_contains($u, '?') ? '&' : '?') . http_build_query($query);
    }
    return $u;
}

/** Static files (css, js, uploads) */
function asset(string $path): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
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
