<?php $pageTitle = htmlspecialchars($game['name']) . ' — Modyssey'; ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container">

        <div class="page-header">
            <div style="display:flex;align-items:center;gap:20px;">
                <?php if ($game['image_path']): ?>
                    <img
                        src="<?= htmlspecialchars($game['image_path']) ?>"
                        alt="<?= htmlspecialchars($game['name']) ?>"
                        style="width:72px;height:72px;object-fit:cover;border-radius:var(--radius);border:1px solid var(--border);"
                    >
                <?php endif; ?>
                <div>
                    <h1><?= htmlspecialchars($game['name']) ?></h1>
                    <p class="text-muted"><?= count($mods) ?> mods disponíveis</p>
                </div>
            </div>
            <?php if (Auth::isOwnerOrAdmin((int) $game['added_by'])): ?>
                <div class="page-actions">
                    <a href="<?= BASE_URL ?>/games/<?= $game['id'] ?>/delete"
                       class="btn btn-danger"
                       onclick="return confirm('Apagar este jogo? Esta acção remove todos os mods associados.')">
                        Apagar Jogo
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($categories)): ?>
            <div style="display:flex;gap:10px;align-items:center;margin-bottom:24px;flex-wrap:wrap;">
                <span style="font-weight:600;font-size:0.9rem;color:var(--text-muted);">Filtrar por Categoria:</span>
                <a href="<?= BASE_URL ?>/games/<?= $game['id'] ?>" 
                   class="tag"
                   style="text-decoration:none;<?= $selectedCategoryId === 0 ? 'background:var(--primary);color:#fff;' : '' ?>">
                   Todas
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= BASE_URL ?>/games/<?= $game['id'] ?>?category_id=<?= $cat['id'] ?>" 
                       class="tag"
                       style="text-decoration:none;<?= $selectedCategoryId === (int)$cat['id'] ? 'background:var(--primary);color:#fff;' : '' ?>">
                       <?= htmlspecialchars($cat['name']) ?>
                       <span style="font-size:0.75rem;opacity:0.75;">(<?= htmlspecialchars($cat['type']) ?>)</span>
                    </a>
                <?php endforeach; ?>
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
                            <div class="mod-card-cover"></div>
                        <?php endif; ?>
                        <div class="mod-card-body">
                            <div class="mod-card-title"><?= htmlspecialchars($mod['title']) ?></div>
                            <div class="mod-card-meta">por <?= htmlspecialchars($mod['uploader']) ?></div>
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
                <p>Ainda não existem mods para este jogo.</p>
                <?php if (Auth::can('user')): ?>
                    <a href="<?= BASE_URL ?>/mods/create" class="btn btn-primary mt-16">Publicar mod</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
