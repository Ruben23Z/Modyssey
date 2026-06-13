<?php require_once __DIR__ . '/../../core/Lang.php'; $pageTitle = Lang::t('home_page_title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container">

        <section class="hero">
            <h1><?= Lang::t('home_title') ?></h1>
            <p><?= Lang::t('home_subtitle') ?></p>
            <div class="hero-actions">
                <a href="<?= BASE_URL ?>/mods" class="btn btn-primary btn-lg"><?= Lang::t('view_all_mods') ?></a>
                <a href="<?= BASE_URL ?>/games" class="btn btn-ghost btn-lg"><?= Lang::t('explore_games') ?></a>
            </div>
        </section>

        <?php if (!empty($mods)): ?>
            <section class="section">
                <div class="section-header">
                    <h2 class="section-title"><?= Lang::t('recent_mods') ?></h2>
                    <a href="<?= BASE_URL ?>/mods" class="section-link"><?= Lang::t('view_all') ?></a>
                </div>

                <div class="grid grid-3">
                    <?php foreach (array_slice($mods, 0, 6) as $mod): ?>
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
                                    <span><?= Lang::t('published_on') ?> <?= htmlspecialchars($mod['uploader']) ?></span>
                                </div>
                                <div class="mod-card-tags">
                                    <?php if ($mod['visibility'] === 'private'): ?>
                                        <span class="tag tag-private"><?= Lang::t('private') ?></span>
                                    <?php endif; ?>
                                    <span class="badge-downloads">&#8595; <?= number_format($mod['download_count']) ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php else: ?>
            <section class="section">
                <div class="empty-state">
                    <span style="font-size:3rem;opacity:.15;">&#127918;</span>
                    <p><?= Lang::t('no_mods') ?></p>
                    <?php if (Auth::can('user')): ?>
                        <a href="<?= BASE_URL ?>/mods/create" class="btn btn-primary mt-16"><?= Lang::t('be_first') ?></a>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
