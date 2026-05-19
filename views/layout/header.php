<?php
require_once __DIR__ . '/../../core/Auth.php';

$currentUser = Auth::user();
$currentUri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function isActive(string $path): string {
    global $currentUri;
    return str_starts_with($currentUri, BASE_URL . $path) ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Modyssey') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-inner">

        <a href="<?= BASE_URL ?>/" class="site-logo">Mod<span>yssey</span></a>

        <nav class="site-nav">
            <a href="<?= BASE_URL ?>/mods" class="<?= isActive('/mods') ?>">Mods</a>
            <a href="<?= BASE_URL ?>/games" class="<?= isActive('/games') ?>">Jogos</a>
            <?php if (Auth::can('sympathizer')): ?>
                <a href="<?= BASE_URL ?>/categories" class="<?= isActive('/categories') ?>">Categorias</a>
            <?php endif; ?>
            <?php if (Auth::can('admin')): ?>
                <a href="<?= BASE_URL ?>/admin/users" class="<?= isActive('/admin') ?>">Administração</a>
            <?php endif; ?>
        </nav>

        <div class="header-actions">
            <?php if (Auth::isLoggedIn()): ?>
                <?php if (Auth::can('user')): ?>
                    <a href="<?= BASE_URL ?>/mods/create" class="btn btn-primary btn-sm">+ Publicar Mod</a>
                <?php endif; ?>
                <div class="header-user">
                    <span class="header-username">
                        <strong><?= htmlspecialchars($currentUser['username']) ?></strong>
                    </span>
                    <span class="role-badge <?= htmlspecialchars($currentUser['role']) ?>">
                        <?= match($currentUser['role']) {
                            'admin'       => 'Admin',
                            'sympathizer' => 'Simpatizante',
                            'user'        => 'Utilizador',
                            default       => 'Convidado',
                        } ?>
                    </span>
                    <a href="<?= BASE_URL ?>/logout" class="btn btn-ghost btn-sm">Sair</a>
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login" class="btn btn-ghost btn-sm">Iniciar Sessão</a>
                <a href="<?= BASE_URL ?>/register" class="btn btn-primary btn-sm">Registar</a>
            <?php endif; ?>
        </div>

    </div>
</header>
