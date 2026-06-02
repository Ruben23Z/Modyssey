<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Redirecionar para o instalador se o ficheiro de configuração não existir
$configFile = __DIR__ . '/../config/configuracoes/.htconfig.xml';
if (!file_exists($configFile)) {
    $requestUri = $_SERVER['REQUEST_URI'];
    if (strpos($requestUri, 'setup.php') === false && strpos($requestUri, 'css/') === false && strpos($requestUri, 'js/') === false) {
        header('Location: /Modyssey/public/setup.php');
        exit;
    }
}

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Lang.php';

Auth::start();
Lang::init();

if (!defined('BASE_URL')) {
    define('BASE_URL', '/Modyssey/public');
}

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$uri = '/' . ltrim(substr($requestUri, strlen($basePath)), '/');
$uri = $uri === '' ? '/' : $uri;
$method = $_SERVER['REQUEST_METHOD'];

$staticRoutes = [
    'GET' => [
        '/' => ['GuestController', 'index'],
        '/login' => ['AuthController', 'loginForm'],
        '/register' => ['AuthController', 'registerForm'],
        '/confirmar' => ['AuthController', 'confirmEmail'],
        '/captcha.php' => ['AuthController', 'captcha'],
        '/logout' => ['AuthController', 'logout'],
        '/mods' => ['ModController', 'index'],
        '/mods/create' => ['ModController', 'createForm'],
        '/mods/import-batch' => ['ModController', 'importBatchForm'],
        '/games' => ['GameController', 'index'],
        '/games/create' => ['GameController', 'createForm'],
        '/categories' => ['CategoryController', 'index'],
        '/categories/create' => ['CategoryController', 'createForm'],
        '/admin/users' => ['UserController', 'index'],
        '/admin/settings' => ['UserController', 'settingsForm'],
        '/api/search' => ['SearchController', 'search'],
        '/subscriptions' => ['SubscriptionController', 'index'],
    ],
    'POST' => [
        '/login' => ['AuthController', 'login'],
        '/register' => ['AuthController', 'register'],
        '/mods/store' => ['ModController', 'store'],
        '/mods/import-batch' => ['ModController', 'importBatch'],
        '/games/store' => ['GameController', 'store'],
        '/categories/store' => ['CategoryController', 'store'],
        '/admin/users/role' => ['UserController', 'updateRole'],
        '/admin/settings' => ['UserController', 'updateSettings'],
        '/api/users/role' => ['UserController', 'updateRoleAjax'],
        '/subscriptions/toggle' => ['SubscriptionController', 'toggle'],
    ],
];

$dynamicRoutes = [
    'GET' => [
        '#^/mods/(\d+)$#' => ['ModController', 'show', ['id']],
        '#^/mods/(\d+)/download$#' => ['ModController', 'download', ['id']],
        '#^/games/(\d+)$#' => ['GameController', 'show', ['id']],
        '#^/games/(\d+)/delete$#' => ['GameController', 'delete', ['id']],
        '#^/categories/(\d+)/delete$#' => ['CategoryController', 'delete', ['id']],
        '#^/mods/(\d+)/delete$#' => ['ModController', 'delete', ['id']],
        '#^/games/(\d+)/download-zip$#' => ['GameController', 'downloadZip', ['id']],
    ],
    'POST' => [
        '#^/api/mods/(\d+)/visibility$#' => ['ModController', 'toggleVisibility', ['id']],
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