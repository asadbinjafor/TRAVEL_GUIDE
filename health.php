<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');

try {
    $statement = createDatabaseConnection()->query('SELECT 1');
    if ((int) $statement->fetchColumn() !== 1) {
        throw new RuntimeException('Database readiness check failed.');
    }
    http_response_code(200);
    echo json_encode(['status' => 'ok'], JSON_UNESCAPED_SLASHES);
} catch (Throwable $exception) {
    error_log('Health check failed: ' . $exception::class);
    http_response_code(503);
    echo json_encode(['status' => 'error'], JSON_UNESCAPED_SLASHES);
}
