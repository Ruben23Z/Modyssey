<?php require_once __DIR__ . '/../../core/Lang.php'; $pageTitle = Lang::t('categories_page_title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container">

        <div class="page-header">
            <div>
                <h1><?= Lang::t('nav_categories') ?></h1>
                <p class="text-muted"><?= Lang::t('categories_subtitle') ?></p>
            </div>
            <div class="page-actions">
                <a href="<?= BASE_URL ?>/categories/create" class="btn btn-primary"><?= Lang::t('add_category') ?></a>
            </div>
        </div>

        <?php if (!empty($_GET['created'])): ?>
            <div class="alert alert-success mb-24">
                <span class="alert-icon">&#10003;</span>
                <?= Lang::t('category_created') ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($categories)): ?>
            <?php
            $grouped = [];
            foreach ($categories as $cat) {
                $gameName = $cat['game_name'] ?? Lang::t('no_game');
                $grouped[$gameName][$cat['type']][] = $cat;
            }
            ?>
            <?php foreach ($grouped as $gameName => $types): ?>
                <div class="section"
                     style="margin-bottom: 2rem; border-bottom: 1px solid var(--border-soft); padding-bottom: 1.5rem;">
                    <div class="section-header" style="margin-bottom: 12px;">
                        <h2 class="section-title"
                            style="font-size: 1.3rem; color: var(--text-primary); border-left: 3px solid var(--primary); padding-left: 8px;"><?= htmlspecialchars($gameName) ?></h2>
                    </div>
                    <?php foreach ($types as $type => $items): ?>
                        <div style="margin-top: 14px; margin-bottom: 14px;">
                            <h3 style="font-size: 0.9rem; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;"><?= htmlspecialchars($type) ?></h3>
                            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                                <?php foreach ($items as $cat): ?>
                                    <div style="display:flex;align-items:center;gap:8px;background:var(--bg3);border:1px solid var(--border-soft);border-radius:var(--radius);padding:8px 14px;">
                                        <span style="font-size:0.875rem;color:var(--text);"><?= htmlspecialchars($cat['name']) ?></span>
                                        <?php if (Auth::isOwnerOrAdmin((int)$cat['added_by'])): ?>
                                            <a href="<?= BASE_URL ?>/categories/<?= $cat['id'] ?>/delete"
                                               class="btn btn-danger btn-sm"
                                               style="padding:2px 8px;font-size:0.72rem;"
                                               onclick="return confirm('<?= Lang::t('delete_category_confirm_prefix') ?> \'<?= htmlspecialchars($cat['name']) ?>\'? <?= Lang::t('delete_category_confirm_suffix') ?>')">
                                                &times;
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <span style="font-size:3rem;opacity:.15;">&#9776;</span>
                <p><?= Lang::t('no_categories') ?></p>
                <a href="<?= BASE_URL ?>/categories/create" class="btn btn-primary mt-16"><?= Lang::t('add_first_category') ?></a>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
