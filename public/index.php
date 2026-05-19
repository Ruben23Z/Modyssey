<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';

Auth::start();

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
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
    ],
    'POST' => [
        '/login'             => ['AuthController',    'login'],
        '/register'          => ['AuthController',    'register'],
        '/mods/store'        => ['ModController',     'store'],
        '/games/store'       => ['GameController',    'store'],
        '/categories/store'  => ['CategoryController','store'],
    ],
];

$matched = false;

foreach ($routes[$method] ?? [] as $route => $handler) {
    [$controller, $action] = $handler;

    if ($uri === $route) {
        $matched = true;
        $file    = __DIR__ . '/../controllers/' . $controller . '.php';

        if (!file_exists($file)) {
            http_response_code(500);
            echo 'Controller not found: ' . htmlspecialchars($controller);
            exit;
        }

        require_once $file;
        $instance = new $controller();
        $instance->$action();
        exit;
    }
}

if (!$matched) {
    http_response_code(404);
    echo '404 Not Found';
}
