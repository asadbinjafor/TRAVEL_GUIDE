<?php
/**
 * Database connection — XAMPP defaults: host 127.0.0.1, port 3306, user root, no password.
 * Start MySQL from XAMPP Control Panel before using the app.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
    $port = defined('DB_PORT') ? DB_PORT : '3306';
    $name = defined('DB_NAME') ? DB_NAME : 'travel_guide';
    $user = defined('DB_USER') ? DB_USER : 'root';
    $pass = defined('DB_PASS') ? DB_PASS : '';

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        $code = $e->getCode();
        $isRefused = str_contains($e->getMessage(), '2002') || str_contains($e->getMessage(), 'actively refused');
        if ($isRefused) {
            dbConnectionError(
                'MySQL is not running',
                '<p><strong>Fix:</strong> Open <strong>XAMPP Control Panel</strong> → click <strong>Start</strong> next to <strong>MySQL</strong>, then refresh this page.</p>'
            );
        }

        if (str_contains($e->getMessage(), 'Unknown database')) {
            dbConnectionError(
                'Database not found',
                '<p><strong>Fix:</strong> Import <code>database.sql</code> in phpMyAdmin (creates <code>travel_guide</code>).</p>'
            );
        }

        if (str_contains($e->getMessage(), 'could not find driver')) {
            dbConnectionError(
                'PHP MySQL driver missing',
                '<p><strong>Fix:</strong> In <code>php.ini</code> (XAMPP), uncomment:<br>
                 <code>extension=pdo_mysql</code> and <code>extension=mysqli</code>, then restart Apache.</p>'
            );
        }

        dbConnectionError('Database connection failed', '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '</p>');
    }

    return $pdo;
}

function dbConnectionError(string $title, string $body): void
{
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>' . htmlspecialchars($title, ENT_QUOTES) . '</title>'
        . '<style>body{font-family:system-ui,sans-serif;max-width:520px;margin:48px auto;padding:24px;background:#f0f6fa;color:#0f2942}'
        . 'h1{color:#c0392b;font-size:1.4rem}code{background:#fff;padding:2px 6px;border-radius:4px}</style></head><body>'
        . '<h1>' . htmlspecialchars($title, ENT_QUOTES) . '</h1>' . $body . '</body></html>';
    exit;
}
