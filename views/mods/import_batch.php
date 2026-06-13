<?php require_once __DIR__ . '/../../core/Lang.php'; $pageTitle = Lang::t('import_batch_page_title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container" style="max-width:720px;">
        <div class="page-header" style="margin-bottom: 32px;">
            <div>
                <h1><?= Lang::t('import_batch_title') ?></h1>
                <p class="text-muted"><?= Lang::t('import_batch_subtitle') ?></p>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger mb-4">
                <strong><?= Lang::t('error') ?>:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body" style="padding: 30px;">
                <form method="POST" action="<?= BASE_URL ?>/mods/import-batch" enctype="multipart/form-data"
                      style="display:flex; flex-direction:column; gap:24px;">

                    <div class="form-group">
                        <label for="batch_file" style="font-weight: 700;"><?= Lang::t('batch_file_label') ?></label>
                        <input type="file" id="batch_file" name="batch_file" accept=".zip" required
                               class="form-control">
                        <span class="form-hint"
                              style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-top: 6px;">
                            <?= Lang::t('batch_file_hint') ?>
                        </span>
                    </div>

                    <div style="background: var(--bg3); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px;">
                        <h5 style="color: var(--accent); font-size: 0.95rem; font-weight: 700; margin-bottom: 8px;">
                            <?= Lang::t('batch_xml_example') ?></h5>
                        <pre style="font-size: 0.8rem; background: #0b0c10; padding: 12px; border-radius: var(--radius); color: #fff; overflow-x: auto; margin: 0; max-height: 250px;"><code>&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;mods&gt;
    &lt;mod&gt;
        &lt;title&gt;Nome do Mod 1&lt;/title&gt;
        &lt;description&gt;Descrição detalhada do mod...&lt;/description&gt;
        &lt;visibility&gt;public&lt;/visibility&gt; &lt;!-- public ou private --&gt;
        &lt;game_id&gt;1&lt;/game_id&gt;
        &lt;cover_image&gt;capa_mod1.jpg&lt;/cover_image&gt; &lt;!-- Deve estar no ZIP --&gt;
        &lt;mod_file&gt;mod1_ficheiros.zip&lt;/mod_file&gt; &lt;!-- Deve estar no ZIP --&gt;
        &lt;video_file&gt;demonstracao.mp4&lt;/video_file&gt; &lt;!-- Opcional --&gt;
        &lt;categories&gt;
            &lt;category_id&gt;1&lt;/category_id&gt;
            &lt;category_id&gt;2&lt;/category_id&gt;
        &lt;/categories&gt;
    &lt;/mod&gt;
&lt;/mods&gt;</code></pre>
                    </div>

                    <div style="display:flex; gap:12px;">
                        <button type="submit" class="btn btn-primary"><?= Lang::t('start_import') ?></button>
                        <a href="<?= BASE_URL ?>/mods" class="btn btn-ghost"><?= Lang::t('cancel') ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
