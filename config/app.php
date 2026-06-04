<?php
define('ROOT_DIR', dirname(__DIR__));
define('BASE_URL', '/project1');

// XAMPP MySQL — change port if your my.ini uses another (e.g. 3307)
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'travel_guide');
define('DB_USER', 'root');
define('DB_PASS', '');
define('REMEMBER_DAYS', 30);
define('REMEMBER_SECRET', 'travel_guide_remember_secret_change_in_production');
define('UPLOAD_MAX_BYTES', 2 * 1024 * 1024);
define('PROFILE_UPLOAD_DIR', ROOT_DIR . '/public/uploads/profiles/');
define('POST_UPLOAD_DIR', ROOT_DIR . '/public/uploads/posts/');
define('PROFILE_UPLOAD_WEB', BASE_URL . '/public/uploads/profiles/');
define('POST_UPLOAD_WEB', BASE_URL . '/public/uploads/posts/');

define('COST_LEVEL_MAP', [
    'low' => 500,
    'medium' => 1500,
    'high' => 3000,
]);

define('GENRES', ['beach', 'mountain', 'city', 'historical', 'adventure', 'nature']);

function ensureUploadDirs(): void
{
    foreach ([PROFILE_UPLOAD_DIR, POST_UPLOAD_DIR] as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
