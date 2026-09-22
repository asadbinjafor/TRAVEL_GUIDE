<?php
declare(strict_types=1);

/** @return array{dsn:string,user:string,password:string} */
function databaseConfig(): array
{
    $databaseUrl = env('DATABASE_URL');
    if ($databaseUrl !== null) {
        $parts = parse_url($databaseUrl);
        if ($parts === false || empty($parts['host']) || empty($parts['path'])) {
            throw new RuntimeException('DATABASE_URL is invalid.');
        }
        $query = [];
        parse_str($parts['query'] ?? '', $query);
        $sslmode = (string) ($query['sslmode'] ?? env('DB_SSLMODE', 'require'));
        return [
            'dsn' => sprintf(
                'pgsql:host=%s;port=%d;dbname=%s;sslmode=%s',
                $parts['host'],
                (int) ($parts['port'] ?? 5432),
                ltrim($parts['path'], '/'),
                preg_replace('/[^a-z-]/', '', $sslmode) ?: 'require'
            ),
            'user' => rawurldecode($parts['user'] ?? ''),
            'password' => rawurldecode($parts['pass'] ?? ''),
        ];
    }

    $host = env('DB_HOST');
    $name = env('DB_NAME');
    $user = env('DB_USER');
    $password = env('DB_PASSWORD');
    if ($host === null || $name === null || $user === null || $password === null) {
        throw new RuntimeException('Database environment variables are incomplete.');
    }
    $sslmode = preg_replace('/[^a-z-]/', '', (string) env('DB_SSLMODE', 'require')) ?: 'require';
    return [
        'dsn' => sprintf('pgsql:host=%s;port=%d;dbname=%s;sslmode=%s', $host, (int) env('DB_PORT', '5432'), $name, $sslmode),
        'user' => $user,
        'password' => $password,
    ];
}

function createDatabaseConnection(): PDO
{
    if (!extension_loaded('pdo_pgsql')) {
        throw new RuntimeException('The pdo_pgsql PHP extension is required.');
    }
    $config = databaseConfig();
    return new PDO($config['dsn'], $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ]);
}

function db(): PDO
{
    static $pdo = null;
    if (!$pdo instanceof PDO) {
        $pdo = createDatabaseConnection();
    }
    return $pdo;
}
