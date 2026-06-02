<?php

$xmlFile = __DIR__ . '/configuracoes/.htconfig.xml';
if (file_exists($xmlFile)) {
    $xml = simplexml_load_file($xmlFile);
    if ($xml) {
        define('DB_HOST', (string)$xml->DataBase->host);
        define('DB_NAME', (string)$xml->DataBase->db);
        define('DB_USER', (string)$xml->DataBase->username);
        define('DB_PASS', (string)$xml->DataBase->password);
    }
}

if (!defined('DB_HOST')) {
    define('DB_HOST',    'localhost');
    define('DB_NAME',    'modyssey');
    define('DB_USER',    'root');
    define('DB_PASS',    '');
}

define('DB_CHARSET', 'utf8mb4');

if (!defined('BASE_URL')) {
    define('BASE_URL', '/Modyssey/public');
}