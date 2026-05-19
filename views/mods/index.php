<?php $pageTitle = 'Mods — Modyssey'; ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container">

        <div class="page-header">
            <div>
                <h1>Mods</h1>
                <p class="text-muted">Explora e descarrega mods para os teus jogos favoritos.</p>
            </div>
            <?php if (Auth::can('user')): ?>
                <div class="page-actions">
                    <a href="<?= BASE_URL ?>/mods/create" class="btn btn-primary">+ Publicar Mod</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($_GET['created'])): ?>
            <div class="alert alert-success mb-24">
                <span class="alert-icon">&#10003;</span>
                Mod publicado com sucesso.
            </div>
        <?php endif; ?>

        <?php if (!empty($mods)): ?>
            <div class="grid grid-3">
                <?php foreach ($mods as $mod): ?>
                    <a href="<?= BASE_URL ?>/mods/<?= $mod['id'] ?>" class="mod-card" style="text-decoration:none;">
                        <?php if ($mod['cover_image_path']): ?>
                            <img
                                src="<?= htmlspecialchars($mod['cover_image_path']) ?>"
                                alt="<?= htmlspecialchars($mod['title']) ?>"
                                class="mod-card-cover"
                            >
                        <?php else: ?>
                            <div class="mod-card-cover" style="display:flex;align-items:center;justify-content:center;">
                                <span style="font-size:2rem;opacity:.2;">&#127918;</span>
                            </div>
                        <?php endif; ?>

                        <div class="mod-card-body">
                            <div class="mod-card-title"><?= htmlspecialchars($mod['title']) ?></div>
                            <div class="mod-card-meta">
                                <span class="mod-card-game"><?= htmlspecialchars($mod['game_name']) ?></span>
                                <span>&bull;</span>
                                <span>por <?= htmlspecialchars($mod['uploader']) ?></span>
                            </div>
                            <div class="mod-card-tags">
                                <?php if ($mod['visibility'] === 'private'): ?>
                                    <span class="tag tag-private">Privado</span>
                                <?php endif; ?>
                                <span class="badge-downloads">&#8595; <?= number_format($mod['download_count']) ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span style="font-size:3rem;opacity:.15;">&#127918;</span>
                <p>Ainda não existem mods disponíveis.</p>
                <?php if (Auth::can('user')): ?>
                    <a href="<?= BASE_URL ?>/mods/create" class="btn btn-primary mt-16">Publicar o primeiro mod</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
