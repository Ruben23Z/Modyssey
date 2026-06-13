<?php require_once __DIR__ . '/../../core/Lang.php'; $pageTitle = Lang::t('not_found_page_title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container">
        <div class="empty-state" style="padding-top:100px;">
            <span style="font-size:4rem;opacity:.12;">404</span>
            <h1 style="font-size:1.4rem;margin-top:16px;"><?= Lang::t('not_found_title') ?></h1>
            <p><?= Lang::t('not_found_text') ?></p>
            <a href="<?= BASE_URL ?>/" class="btn btn-secondary mt-24"><?= Lang::t('back_home') ?></a>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
