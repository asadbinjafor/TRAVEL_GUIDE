<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';

$name = trim((string) env('ADMIN_NAME', 'Site Admin'));
$email = strtolower(trim((string) env('ADMIN_EMAIL')));
$password = (string) env('ADMIN_PASSWORD');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
    fwrite(STDERR, "Set ADMIN_EMAIL and an ADMIN_PASSWORD of at least 12 characters.\n");
    exit(1);
}

$pdo = db();
$pdo->beginTransaction();
try {
    $find = $pdo->prepare('SELECT id FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1 FOR UPDATE');
    $find->execute([$email]);
    $id = $find->fetchColumn();
    if ($id) {
        $update = $pdo->prepare("UPDATE users SET name = ?, password_hash = ?, role = 'admin', is_verified = TRUE WHERE id = ?");
        $update->execute([$name, password_hash($password, PASSWORD_DEFAULT), $id]);
        $message = 'Admin account updated.';
    } else {
        $insert = $pdo->prepare(
            "INSERT INTO users (name, email, password_hash, role, is_verified)
             VALUES (?, ?, ?, 'admin', TRUE)"
        );
        $insert->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        $message = 'Admin account created.';
    }
    $pdo->commit();
    fwrite(STDOUT, $message . "\n");
} catch (Throwable $exception) {
    $pdo->rollBack();
    fwrite(STDERR, "Admin seed failed. Check the database connection and schema.\n");
    exit(1);
}
