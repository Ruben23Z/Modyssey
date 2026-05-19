<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';

Auth::start();

if (!defined('BASE_URL')) {
    define('BASE_URL', '/Modyssey/public');
}

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$uri        = '/' . ltrim(substr($requestUri, strlen($basePath)), '/');
$uri        = $uri === '' ? '/' : $uri;
$method     = $_SERVER['REQUEST_METHOD'];

$staticRoutes = [
    'GET' => [
        '/'                  => ['GuestController',   'index'],
        '/login'             => ['AuthController',    'loginForm'],
        '/register'          => ['AuthController',    'registerForm'],
        '/logout'            => ['AuthController',    'logout'],
        '/mods'              => ['ModController',     'index'],
        '/mods/create'       => ['ModController',     'createForm'],
        '/games'             => ['GameController',    'index'],
        '/games/create'      => ['GameController',    'createForm'],
        '/categories'        => ['CategoryController','index'],
        '/categories/create' => ['CategoryController','createForm'],
        '/admin/users'       => ['UserController',    'index'],
    ],
    'POST' => [
        '/login'             => ['AuthController',    'login'],
        '/register'          => ['AuthController',    'register'],
        '/mods/store'        => ['ModController',     'store'],
        '/games/store'       => ['GameController',    'store'],
        '/categories/store'  => ['CategoryController','store'],
        '/admin/users/role'  => ['UserController',    'updateRole'],
    ],
];

$dynamicRoutes = [
    'GET' => [
        '#^/mods/(\d+)$#'              => ['ModController',      'show',     ['id']],
        '#^/mods/(\d+)/download$#'     => ['ModController',      'download', ['id']],
        '#^/games/(\d+)$#'             => ['GameController',     'show',     ['id']],
        '#^/games/(\d+)/delete$#'      => ['GameController',     'delete',   ['id']],
        '#^/categories/(\d+)/delete$#' => ['CategoryController', 'delete',   ['id']],
        '#^/mods/(\d+)/delete$#'       => ['ModController',      'delete',   ['id']],
    ],
];

function dispatch(string $controller, string $action): void
{
    $file = __DIR__ . '/../controllers/' . $controller . '.php';

    if (!file_exists($file)) {
        http_response_code(500);
        echo 'Controller não encontrado: ' . htmlspecialchars($controller);
        exit;
    }

    require_once $file;
    $instance = new $controller();
    $instance->$action();
}

foreach ($staticRoutes[$method] ?? [] as $route => [$controller, $action]) {
    if ($uri === $route) {
        dispatch($controller, $action);
        exit;
    }
}

foreach ($dynamicRoutes[$method] ?? [] as $pattern => [$controller, $action, $paramNames]) {
    if (preg_match($pattern, $uri, $matches)) {
        array_shift($matches);
        foreach ($paramNames as $index => $name) {
            $_GET[$name] = $matches[$index];
        }
        dispatch($controller, $action);
        exit;
    }
}

http_response_code(404);
require __DIR__ . '/../views/errors/404.php';