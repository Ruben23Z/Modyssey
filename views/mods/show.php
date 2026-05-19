<?php $pageTitle = htmlspecialchars($mod['title']) . ' — Modyssey'; ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container" style="max-width:900px;">

        <div style="display:grid;grid-template-columns:1fr 300px;gap:32px;align-items:start;" class="mod-layout">

            <div>
                <?php if ($mod['cover_image_path']): ?>
                    <img
                        src="<?= htmlspecialchars($mod['cover_image_path']) ?>"
                        alt="<?= htmlspecialchars($mod['title']) ?>"
                        style="width:100%;border-radius:var(--radius-lg);border:1px solid var(--border-soft);margin-bottom:24px;"
                    >
                <?php endif; ?>

                <?php if (!empty($images)): ?>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px;">
                        <?php foreach ($images as $img): ?>
                            <img
                                src="<?= htmlspecialchars($img['image_path']) ?>"
                                alt="Imagem adicional"
                                style="height:90px;border-radius:var(--radius);border:1px solid var(--border);object-fit:cover;cursor:pointer;"
                                onclick="document.querySelector('.mod-main-img').src=this.src"
                            >
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <h1 style="font-size:1.6rem;font-weight:700;letter-spacing:-.5px;margin-bottom:8px;">
                    <?= htmlspecialchars($mod['title']) ?>
                </h1>

                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:24px;font-size:.875rem;color:var(--text-muted);">
                    <span>Jogo: <a href="/games/<?= $mod['game_id'] ?>" class="text-accent"><?= htmlspecialchars($mod['game_name']) ?></a></span>
                    <span>&bull;</span>
                    <span>Por <strong style="color:var(--text);"><?= htmlspecialchars($mod['uploader']) ?></strong></span>
                    <span>&bull;</span>
                    <span>&#8595; <?= number_format($mod['download_count']) ?> transferências</span>
                    <?php if ($mod['visibility'] === 'private'): ?>
                        <span class="tag tag-private">Privado</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($categories)): ?>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:20px;">
                        <?php foreach ($categories as $cat): ?>
                            <span class="tag"><?= htmlspecialchars($cat['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div style="color:var(--text);line-height:1.75;white-space:pre-line;">
                    <?= nl2br(htmlspecialchars($mod['description'])) ?>
                </div>
            </div>

            <aside>
                <div class="card" style="position:sticky;top:80px;">
                    <div class="card-body" style="display:flex;flex-direction:column;gap:12px;">

                        <a href="/mods/<?= $mod['id'] ?>/download" class="btn btn-primary btn-lg" style="justify-content:center;">
                            &#8595; Descarregar
                        </a>

                        <?php if (Auth::isOwnerOrAdmin((int) $mod['uploaded_by'])): ?>
                            <hr>
                            <a href="/mods/<?= $mod['id'] ?>/delete"
                               class="btn btn-danger"
                               style="justify-content:center;"
                               onclick="return confirm('Apagar este mod definitivamente?')">
                                Apagar Mod
                            </a>
                        <?php endif; ?>

                        <hr>

                        <div style="font-size:.8rem;color:var(--text-muted);">
                            <div style="margin-bottom:6px;">
                                <span>Publicado em</span><br>
                                <strong style="color:var(--text);"><?= date('d/m/Y', strtotime($mod['created_at'])) ?></strong>
                            </div>
                            <div>
                                <span>Transferências</span><br>
                                <strong style="color:var(--text);"><?= number_format($mod['download_count']) ?></strong>
                            </div>
                        </div>

                    </div>
                </div>
            </aside>

        </div>

    </div>
</main>

<style>
@media (max-width: 700px) {
    .mod-layout { grid-template-columns: 1fr !important; }
}
</style>

<?php require __DIR__ . '/../layout/footer.php'; ?>
