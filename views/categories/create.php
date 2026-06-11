<?php require_once __DIR__ . '/../../core/Lang.php'; $pageTitle = Lang::t('create_category_page_title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container" style="max-width:540px;">

        <div class="page-header">
            <div>
                <h1><?= Lang::t('create_category_title') ?></h1>
                <p class="text-muted"><?= Lang::t('create_category_subtitle') ?></p>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error mb-24">
                <span class="alert-icon">&#9888;</span>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/categories/store" novalidate
                      style="display:flex;flex-direction:column;gap:20px;">

                    <div class="form-group">
                        <label for="game_id"><?= Lang::t('game_required') ?></label>
                        <select id="game_id" name="game_id" required>
                            <option value=""><?= Lang::t('select_game_for_category') ?></option>
                            <?php foreach ($games as $game): ?>
                                <option value="<?= $game['id'] ?>"
                                    <?= ((int)($_POST['game_id'] ?? 0) === (int)$game['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($game['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="name"><?= Lang::t('category_name_label') ?></label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                            placeholder="<?= Lang::t('category_name_placeholder') ?>"
                            required
                            maxlength="100"
                        >
                    </div>

                    <div class="form-group">
                        <label for="type"><?= Lang::t('category_type_label') ?></label>
                        <input
                            type="text"
                            id="type"
                            name="type"
                            value="<?= htmlspecialchars($_POST['type'] ?? '') ?>"
                            placeholder="<?= Lang::t('category_type_placeholder') ?>"
                            required
                            maxlength="80"
                        >
                        <span class="form-hint"><?= Lang::t('category_type_hint') ?></span>
                    </div>

                    <div style="display:flex;gap:10px;">
                        <button type="submit" class="btn btn-primary"><?= Lang::t('save_category') ?></button>
                        <a href="<?= BASE_URL ?>/categories" class="btn btn-ghost"><?= Lang::t('cancel') ?></a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
