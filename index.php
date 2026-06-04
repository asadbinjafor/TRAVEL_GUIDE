<?php
require_once __DIR__ . '/includes/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$base = rtrim(BASE_URL, '/');
if ($base !== '' && strncmp($uri, $base, strlen($base)) === 0) {
    $uri = substr($uri, strlen($base)) ?: '/';
}
$uri = '/' . trim($uri, '/');
if ($uri !== '/') {
    $uri = rtrim($uri, '/') ?: '/';
}

if (isset($_GET['route'])) {
    $uri = '/' . trim((string) $_GET['route'], '/');
    if ($uri !== '/') {
        $uri = rtrim($uri, '/') ?: '/';
    }
} elseif ($uri === '/index.php') {
    $uri = '/';
}

$routes = require ROOT_DIR . '/config/routes.php';

$handler = null;
if ($method === 'GET' && isset($routes['GET_API'][$uri])) {
    $handler = $routes['GET_API'][$uri];
} elseif (isset($routes[$method][$uri])) {
    $handler = $routes[$method][$uri];
}

if (!$handler) {
    http_response_code(404);
    view('errors/404', ['title' => 'Not Found']);
    exit;
}

[$class, $action] = $handler;
$controller = new $class();
$controller->$action();
