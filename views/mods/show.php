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
                    <span>Jogo: <a href="<?= BASE_URL ?>/games/<?= $mod['game_id'] ?>"
                                   class="text-accent"><?= htmlspecialchars($mod['game_name']) ?></a></span>
                    <span>&bull;</span>
                    <span>Por <strong
                                style="color:var(--text);"><?= htmlspecialchars($mod['uploader']) ?></strong></span>
                    <span>&bull;</span>
                    <span>&#8595; <?= number_format($mod['download_count']) ?> transferências</span>
                    <span id="visibility-badge-container">
                        <?php if ($mod['visibility'] === 'private'): ?>
                            <span class="tag tag-private">Privado</span>
                        <?php else: ?>
                            <span class="tag" style="background: rgba(82, 192, 124, 0.08); border-color: rgba(82, 192, 124, 0.3); color: var(--success);">Público</span>
                        <?php endif; ?>
                    </span>
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
                    <?php if (!empty($mod['video_path'])): ?>
                        <div style="margin-top: 32px; margin-bottom: 24px;">
                            <h3 style="font-size: 1.1rem; margin-bottom: 12px; color: var(--text);">Vídeo de
                                Demonstração</h3>
                            <video controls
                                   style="width: 100%; max-height: 400px; border-radius: var(--radius-lg); border: 1px solid var(--border-soft); background: #000; outline: none;">
                                <source src="<?= htmlspecialchars($mod['video_path']) ?>" type="video/mp4">
                                O teu navegador não suporta a reprodução de vídeo.
                            </video>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <aside>
                <div class="card" style="position:sticky;top:80px;">
                    <div class="card-body" style="display:flex;flex-direction:column;gap:12px;">

                        <a href="<?= BASE_URL ?>/mods/<?= $mod['id'] ?>/download" class="btn btn-primary btn-lg"
                           style="justify-content:center;">
                            &#8595; Descarregar
                        </a>

                        <?php if (Auth::isOwnerOrAdmin((int)$mod['uploaded_by'])): ?>
                            <hr>
                            <div style="display:flex; flex-direction:column; gap:6px;">
                                <label for="visibility-toggle" style="font-weight: 600; font-size: 0.8rem;">Visibilidade do Mod</label>
                                <select id="visibility-toggle" class="form-control" style="font-size:0.85rem; padding: 6px 10px;">
                                    <option value="public" <?= $mod['visibility'] === 'public' ? 'selected' : '' ?>>Público</option>
                                    <option value="private" <?= $mod['visibility'] === 'private' ? 'selected' : '' ?>>Privado</option>
                                </select>
                            </div>
                            <hr>
                            <a href="<?= BASE_URL ?>/mods/<?= $mod['id'] ?>/delete"
                               class="btn btn-danger"
                               style="justify-content:center;"
                               onclick="return confirm('Apagar este mod definitivamente?')">
                                Apagar Mod
                            </a>
                        <?php endif; ?>

                        <hr>

                        <!-- Social Share (Bootstrap Styled) -->
                        <div style="margin-bottom: 18px;">
                            <span style="font-weight: 700; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 8px; letter-spacing: 0.5px;">Partilhar</span>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <a href="#" onclick="shareFacebook(event)" class="btn" style="background-color: #1877f2; color: #fff; justify-content: center; font-size: 0.8rem; padding: 6px 12px; border-radius: var(--radius);">
                                    <i class="bi bi-facebook" style="font-size: 0.95rem;"></i> Facebook
                                </a>
                                <a href="#" onclick="shareTwitter(event)" class="btn" style="background-color: #000; color: #fff; justify-content: center; font-size: 0.8rem; padding: 6px 12px; border: 1px solid var(--border); border-radius: var(--radius);">
                                    <i class="bi bi-twitter-x" style="font-size: 0.95rem;"></i> Twitter / X
                                </a>
                                <a href="#" onclick="shareWhatsApp(event)" class="btn" style="background-color: #25d366; color: #fff; justify-content: center; font-size: 0.8rem; padding: 6px 12px; border-radius: var(--radius);">
                                    <i class="bi bi-whatsapp" style="font-size: 0.95rem;"></i> WhatsApp
                                </a>
                            </div>
                        </div>

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
        .mod-layout {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<?php if (Auth::isOwnerOrAdmin((int)$mod['uploaded_by'])): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const visibilityToggle = document.getElementById('visibility-toggle');
    const badgeContainer = document.getElementById('visibility-badge-container');

    if (visibilityToggle && badgeContainer) {
        visibilityToggle.addEventListener('change', () => {
            const visibility = visibilityToggle.value;
            visibilityToggle.disabled = true;

            fetch('<?= BASE_URL ?>/api/mods/<?= $mod['id'] ?>/visibility', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ visibility: visibility })
            })
            .then(res => {
                if (!res.ok) throw new Error('Falha ao atualizar visibilidade');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    if (data.visibility === 'private') {
                        badgeContainer.innerHTML = '<span class="tag tag-private">Privado</span>';
                    } else {
                        badgeContainer.innerHTML = '<span class="tag" style="background: rgba(82, 192, 124, 0.08); border-color: rgba(82, 192, 124, 0.3); color: var(--success);">Público</span>';
                    }
                } else {
                    throw new Error(data.error || 'Erro desconhecido');
                }
            })
            .catch(err => {
                alert(err.message || 'Erro ao alterar a visibilidade.');
            })
            .finally(() => {
                visibilityToggle.disabled = false;
            });
        });
    }
});
</script>
<?php endif; ?>

<script>
function shareFacebook(e) {
    e.preventDefault();
    const url = encodeURIComponent(window.location.href);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, 'Share', 'width=600,height=400,resizable=yes,scrollbars=yes');
}

function shareTwitter(e) {
    e.preventDefault();
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent("Vê este mod incrível no Modyssey!");
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, 'Share', 'width=600,height=400,resizable=yes,scrollbars=yes');
}

function shareWhatsApp(e) {
    e.preventDefault();
    const text = encodeURIComponent("Vê este mod incrível no Modyssey: " + window.location.href);
    window.open(`https://api.whatsapp.com/send?text=${text}`, 'Share', 'width=600,height=400,resizable=yes,scrollbars=yes');
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
