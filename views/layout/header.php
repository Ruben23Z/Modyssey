<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Lang.php';

$currentUser = Auth::user();
$currentUri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Load unread notification count for logged-in users
$unreadNotifCount = 0;
if (Auth::isLoggedIn() && Auth::can('user')) {
    require_once __DIR__ . '/../../models/Notification.php';
    $notifModel = new Notification();
    $unreadNotifCount = $notifModel->getUnreadCount((int)$currentUser['id']);
}

function isActive(string $path): string {
    global $currentUri;
    return str_starts_with($currentUri, BASE_URL . $path) ? ' active' : '';
}
?>

<!DOCTYPE html>
<html lang="<?= Lang::getLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Modyssey') ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-..." crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-..." crossorigin="anonymous"></script>
    <!-- Social Share JS -->
    <script src="<?= BASE_URL ?>/js/social-share.js"></script>
</head>
<body>

<header class="site-header">
    <div class="container header-inner">

        <a href="<?= BASE_URL ?>/" class="site-logo">Mod<span>yssey</span></a>

        <nav class="site-nav">
            <a href="<?= BASE_URL ?>/mods" class="<?= isActive('/mods') ?>"><?= Lang::t('nav_mods') ?></a>
            <a href="<?= BASE_URL ?>/games" class="<?= isActive('/games') ?>"><?= Lang::t('nav_games') ?></a>
            <?php if (Auth::can('sympathizer')): ?>
                <a href="<?= BASE_URL ?>/categories" class="<?= isActive('/categories') ?>"><?= Lang::t('nav_categories') ?></a>
            <?php endif; ?>
            <?php if (Auth::isLoggedIn() && Auth::can('user')): ?>
                <a href="<?= BASE_URL ?>/subscriptions" class="<?= isActive('/subscriptions') ?>" style="position:relative;">
                    <?= Lang::t('nav_subscriptions') ?>
                    <?php if ($unreadNotifCount > 0): ?>
                        <span style="position:absolute;top:-6px;right:-14px;background:var(--accent,#e74c3c);color:#fff;border-radius:50%;font-size:0.65rem;font-weight:700;min-width:16px;height:16px;display:inline-flex;align-items:center;justify-content:center;padding:0 3px;line-height:1;">
                            <?= $unreadNotifCount ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
            <?php if (Auth::can('admin')): ?>
                <a href="<?= BASE_URL ?>/admin/users" class="<?= isActive('/admin') ?>"><?= Lang::t('nav_administration') ?></a>
            <?php endif; ?>
        </nav>

        <div class="global-search-container" style="position: relative; max-width: 280px; width: 100%; margin: 0 16px;">
            <input type="text" id="global-search" placeholder="<?= Lang::t('search_placeholder') ?>" style="width: 100%; padding: 6px 14px; border-radius: 20px; border: 1px solid var(--border); background: var(--bg4); color: var(--text); font-size: 0.85rem; outline: none; transition: border-color var(--transition);">
            <div id="global-search-dropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: var(--bg3); border: 1px solid var(--border); border-radius: var(--radius); max-height: 300px; overflow-y: auto; z-index: 1000; box-shadow: var(--shadow); margin-top: 6px; padding: 10px;">
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('global-search');
            const searchDropdown = document.getElementById('global-search-dropdown');
            let debounceTimer;

            if (searchInput && searchDropdown) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    const query = searchInput.value.trim();
                    if (query.length < 2) {
                        searchDropdown.style.display = 'none';
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        fetch(`<?= BASE_URL ?>/api/search?q=${encodeURIComponent(query)}`)
                            .then(res => res.json())
                            .then(data => {
                                searchDropdown.innerHTML = '';
                                if ((!data.games || data.games.length === 0) && (!data.mods || data.mods.length === 0)) {
                                    searchDropdown.innerHTML = '<div style="padding: 8px; color: var(--text-muted); font-size: 0.85rem;">Sem resultados</div>';
                                    searchDropdown.style.display = 'block';
                                    return;
                                }

                                let html = '';
                                if (data.games && data.games.length > 0) {
                                    html += '<div style="font-weight: 700; font-size: 0.75rem; text-transform: uppercase; color: var(--accent); margin-bottom: 6px; padding: 4px 8px;">Jogos</div>';
                                    data.games.forEach(game => {
                                        const cover = game.image_path ? game.image_path : 'https://via.placeholder.com/40x50?text=Capa';
                                        html += `
                                            <a href="<?= BASE_URL ?>/games/${game.id}" class="search-result-item" style="display: flex; align-items: center; gap: 10px; padding: 6px 8px; border-radius: 6px; color: var(--text); text-decoration: none; margin-bottom: 4px; font-size: 0.85rem; transition: background 0.15s ease;">
                                                <img src="${cover}" style="width: 30px; height: 38px; object-fit: cover; border-radius: 4px;">
                                                <span>${game.name}</span>
                                            </a>
                                        `;
                                    });
                                }

                                if (data.mods && data.mods.length > 0) {
                                    html += '<div style="font-weight: 700; font-size: 0.75rem; text-transform: uppercase; color: var(--success); margin-top: 10px; margin-bottom: 6px; padding: 4px 8px;">Mods</div>';
                                    data.mods.forEach(mod => {
                                        const cover = mod.cover_image_path ? mod.cover_image_path : 'https://via.placeholder.com/40x50?text=Capa';
                                        html += `
                                            <a href="<?= BASE_URL ?>/mods/${mod.id}" class="search-result-item" style="display: flex; align-items: center; gap: 10px; padding: 6px 8px; border-radius: 6px; color: var(--text); text-decoration: none; margin-bottom: 4px; font-size: 0.85rem; transition: background 0.15s ease;">
                                                <img src="${cover}" style="width: 30px; height: 38px; object-fit: cover; border-radius: 4px;">
                                                <div style="display: flex; flex-direction: column;">
                                                    <span style="font-weight: 500;">${mod.title}</span>
                                                    <span style="font-size: 0.7rem; color: var(--text-muted);">${mod.game_name} • por ${mod.uploader}</span>
                                                </div>
                                            </a>
                                        `;
                                    });
                                }

                                searchDropdown.innerHTML = html;
                                searchDropdown.style.display = 'block';

                                document.querySelectorAll('.search-result-item').forEach(item => {
                                    item.addEventListener('mouseenter', () => {
                                        item.style.background = 'var(--bg4)';
                                    });
                                    item.addEventListener('mouseleave', () => {
                                        item.style.background = 'transparent';
                                    });
                                });
                            })
                            .catch(err => {
                                console.error(err);
                            });
                    }, 300);
                });

                document.addEventListener('click', (e) => {
                    if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                        searchDropdown.style.display = 'none';
                    }
                });

                searchInput.addEventListener('focus', () => {
                    if (searchInput.value.trim().length >= 2) {
                        searchDropdown.style.display = 'block';
                    }
                });
            }
        });
        </script>

        <div class="header-actions">
            <!-- Language Switcher -->
            <div class="lang-switcher" style="display: flex; gap: 6px; font-size: 0.8rem; font-weight: 700; align-items: center; margin-right: 12px;">
                <?php
                $currentLang = Lang::getLang();
                $uriWithLang = function($l) {
                    $query = $_GET;
                    $query['lang'] = $l;
                    return '?' . http_build_query($query);
                };
                ?>
                <a href="<?= $uriWithLang('pt') ?>" style="color: <?= $currentLang === 'pt' ? 'var(--accent)' : 'var(--text-muted)' ?>; transition: color var(--transition); text-decoration: none;">PT</a>
                <span style="color: var(--border);">|</span>
                <a href="<?= $uriWithLang('en') ?>" style="color: <?= $currentLang === 'en' ? 'var(--accent)' : 'var(--text-muted)' ?>; transition: color var(--transition); text-decoration: none;">EN</a>
            </div>

            <?php if (Auth::isLoggedIn()): ?>
                <?php if (Auth::can('user')): ?>
                    <a href="<?= BASE_URL ?>/mods/create" class="btn btn-primary btn-sm"><?= Lang::t('publish_mod') ?></a>
                <?php endif; ?>
                <div class="header-user">
                    <span class="header-username">
                        <strong><?= htmlspecialchars($currentUser['username']) ?></strong>
                    </span>
                    <span class="role-badge <?= htmlspecialchars($currentUser['role']) ?>">
                        <?= Lang::t('role_' . $currentUser['role']) ?>
                    </span>
                    <a href="<?= BASE_URL ?>/logout" class="btn btn-ghost btn-sm"><?= Lang::t('nav_logout') ?></a>
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login" class="btn btn-ghost btn-sm"><?= Lang::t('nav_login') ?></a>
                <a href="<?= BASE_URL ?>/register" class="btn btn-primary btn-sm"><?= Lang::t('nav_register') ?></a>
            <?php endif; ?>
        </div>

    </div>
</header>
