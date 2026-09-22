<?php
declare(strict_types=1);

define('ROOT_DIR', dirname(__DIR__));

/** Load a local .env without overriding real process environment variables. */
function loadLocalEnv(string $path): void
{
    if (!is_file($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key === '' || getenv($key) !== false) {
            continue;
        }
        if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) {
            $value = substr($value, 1, -1);
        }
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

function envBool(string $key, bool $default = false): bool
{
    $value = env($key);
    return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOL);
}

loadLocalEnv(ROOT_DIR . '/.env');

$basePath = '/' . trim((string) env('APP_BASE_PATH', ''), '/');
define('BASE_URL', $basePath === '/' ? '' : $basePath);
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_DEBUG', envBool('APP_DEBUG', false));
define('REMEMBER_DAYS', max(1, (int) env('REMEMBER_DAYS', '30')));
define('UPLOAD_MAX_BYTES', max(1024, (int) env('UPLOAD_MAX_BYTES', (string) (2 * 1024 * 1024))));
define('MAX_POST_IMAGES', max(1, (int) env('MAX_POST_IMAGES', '5')));
define('PROFILE_UPLOAD_DIR', ROOT_DIR . '/public/uploads/profiles/');
define('POST_UPLOAD_DIR', ROOT_DIR . '/public/uploads/posts/');

define('COST_LEVEL_MAP', ['low' => 500, 'medium' => 1500, 'high' => 3000]);
define('GENRES', ['beach', 'mountain', 'city', 'historical', 'adventure', 'nature']);

function ensureUploadDirs(): void
{
    if (env('SUPABASE_STORAGE_BUCKET') && env('SUPABASE_URL') && env('SUPABASE_SERVICE_ROLE_KEY')) {
        return;
    }
    foreach ([PROFILE_UPLOAD_DIR, POST_UPLOAD_DIR] as $dir) {
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Upload directory is not writable.');
        }
    }
}
