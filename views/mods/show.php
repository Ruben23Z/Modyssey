<?php
$mod        = $mod ?? ['id' => 0, 'title' => '', 'description' => '', 'cover_image_path' => '',
                       'file_path' => '', 'video_path' => '', 'visibility' => 'public',
                       'game_id' => 0, 'game_name' => '', 'uploader' => '', 'uploaded_by' => 0,
                       'download_count' => 0, 'created_at' => ''];
$categories = $categories ?? [];
$images     = $images ?? [];
$versions   = $versions ?? [];
?>
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
                        <?php foreach ($images as $i => $img): ?>
                            <img
                                src="<?= htmlspecialchars($img['image_path']) ?>"
                                alt="Imagem adicional"
                                data-lightbox-index="<?= $i ?>"
                                style="height:90px;border-radius:var(--radius);border:1px solid var(--border);object-fit:cover;cursor:pointer;"
                                onclick="openLightbox(<?= $i ?>)"
                            >
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <h1 style="font-size:1.6rem;font-weight:700;letter-spacing:-.5px;margin-bottom:8px;">
                    <?= htmlspecialchars($mod['title']) ?>
                </h1>

                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:24px;font-size:.875rem;color:var(--text-muted);">
                    <span><?= Lang::t('game_colon') ?> <a href="<?= BASE_URL ?>/games/<?= $mod['game_id'] ?>"
                                   class="text-accent"><?= htmlspecialchars($mod['game_name']) ?></a></span>
                    <span>&bull;</span>
                    <span><?= Lang::t('by_cap') ?> <strong
                                style="color:var(--text);"><?= htmlspecialchars($mod['uploader']) ?></strong></span>
                    <span>&bull;</span>
                    <span>&#8595; <?= number_format($mod['download_count']) ?> <?= Lang::t('downloads_word') ?></span>
                    <span id="visibility-badge-container">
                        <?php if ($mod['visibility'] === 'private'): ?>
                            <span class="tag tag-private"><?= Lang::t('private') ?></span>
                        <?php else: ?>
                            <span class="tag"
                                  style="background: rgba(82, 192, 124, 0.08); border-color: rgba(82, 192, 124, 0.3); color: var(--success);"><?= Lang::t('public') ?></span>
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
                            <h3 style="font-size: 1.1rem; margin-bottom: 12px; color: var(--text);"><?= Lang::t('video_demo_title') ?></h3>
                            <video controls
                                   style="width: 100%; max-height: 400px; border-radius: var(--radius-lg); border: 1px solid var(--border-soft); background: #000; outline: none;">
                                <source src="<?= htmlspecialchars($mod['video_path']) ?>" type="video/mp4">
                                <?= Lang::t('video_not_supported') ?>
                            </video>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Changelog de versões -->
                <div style="margin-top:40px;">
                    <h2 style="font-size:1.15rem;font-weight:700;margin-bottom:16px;"><?= Lang::t('version_history') ?></h2>

                    <?php if (empty($versions)): ?>
                        <p style="color:var(--text-muted);font-size:.875rem;"><?= Lang::t('no_versions') ?></p>
                    <?php else: ?>
                        <?php foreach ($versions as $i => $v): ?>
                            <div style="border:1px solid var(--border);border-radius:var(--radius);padding:16px;margin-bottom:12px;background:var(--bg3);">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                    <span style="font-weight:700;color:var(--accent);">v<?= htmlspecialchars($v['version']) ?></span>
                                    <span style="font-size:.75rem;color:var(--text-muted);"><?= date('d/m/Y', strtotime($v['created_at'])) ?></span>
                                </div>
                                <p style="font-size:.875rem;line-height:1.6;white-space:pre-line;margin:0;"><?= nl2br(htmlspecialchars($v['changelog'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (Auth::isOwnerOrAdmin((int)$mod['uploaded_by'])): ?>
                        <details style="margin-top:20px;">
                            <summary style="cursor:pointer;font-weight:600;font-size:.9rem;color:var(--accent);margin-bottom:12px;"><?= Lang::t('add_new_version') ?></summary>
                            <form method="POST" action="<?= BASE_URL ?>/mods/<?= $mod['id'] ?>/version" enctype="multipart/form-data" style="margin-top:12px;display:flex;flex-direction:column;gap:12px;">
                                <div>
                                    <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;"><?= Lang::t('version_number_label') ?></label>
                                    <input type="text" name="version" required placeholder="1.1" class="form-control" style="font-size:.875rem;">
                                </div>
                                <div>
                                    <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;"><?= Lang::t('version_notes_label') ?></label>
                                    <textarea name="changelog" required rows="4" class="form-control" style="font-size:.875rem;" placeholder="<?= Lang::t('version_notes_placeholder') ?>"></textarea>
                                </div>
                                <?php
                                $gameExts = array_filter(array_map(fn($e) => strtolower(trim($e, " .")), explode(',', $mod['game_allowed_extensions'] ?? 'zip')));
                                $gameExts = $gameExts ?: ['zip'];
                                $allowAllExts = in_array('*', $gameExts, true);
                                $acceptAttr = $allowAllExts ? '' : 'accept="' . htmlspecialchars(implode(',', array_map(fn($e) => '.' . $e, $gameExts))) . '"';
                                $extsLabel = $allowAllExts ? Lang::t('all_formats') : implode(', ', array_map(fn($e) => '.' . $e, $gameExts));
                                ?>
                                <div>
                                    <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;"><?= Lang::t('mod_file_word') ?> (<?= htmlspecialchars($extsLabel) ?>)</label>
                                    <input type="file" name="version_file" <?= $acceptAttr ?> required class="form-control" style="font-size:.875rem;">
                                </div>
                                <button type="submit" class="btn btn-primary" style="align-self:flex-start;"><?= Lang::t('publish_version') ?></button>
                            </form>
                        </details>
                    <?php endif; ?>
                </div>
            </div>

            <aside>
                <div class="card" style="position:sticky;top:80px;">
                    <div class="card-body" style="display:flex;flex-direction:column;gap:12px;">

                        <a href="<?= BASE_URL ?>/mods/<?= $mod['id'] ?>/download" class="btn btn-primary btn-lg"
                           style="justify-content:center;">
                            &#8595; <?= Lang::t('download') ?>
                        </a>

                        <?php if (Auth::isOwnerOrAdmin((int)$mod['uploaded_by'])): ?>
                            <hr>
                            <div style="display:flex; flex-direction:column; gap:6px;">
                                <label for="visibility-toggle" style="font-weight: 600; font-size: 0.8rem;"><?= Lang::t('mod_visibility_label') ?></label>
                                <select id="visibility-toggle" class="form-control"
                                        style="font-size:0.85rem; padding: 6px 10px;">
                                    <option value="public" <?= $mod['visibility'] === 'public' ? 'selected' : '' ?>>
                                        <?= Lang::t('public') ?>
                                    </option>
                                    <option value="private" <?= $mod['visibility'] === 'private' ? 'selected' : '' ?>>
                                        <?= Lang::t('private') ?>
                                    </option>
                                </select>
                            </div>
                            <hr>
                            <a href="<?= BASE_URL ?>/mods/<?= $mod['id'] ?>/delete"
                               class="btn btn-danger"
                               style="justify-content:center;"
                               onclick="return confirm(<?= htmlspecialchars(json_encode(Lang::t('delete_mod_confirm')), ENT_QUOTES) ?>)">
                                <?= Lang::t('delete_mod') ?>
                            </a>
                        <?php endif; ?>

                        <hr>

                        <!-- Social Share (Bootstrap Styled) -->
                        <div style="margin-bottom: 18px;">
                            <span style="font-weight: 700; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 8px; letter-spacing: 1px;"><?= Lang::t('share') ?></span>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <a href="#" data-share="twitter" class="btn"
                                   style="background-color: #000; color: #fff; justify-content: center; font-size: 0.8rem; padding: 6px 12px; border: 1px solid var(--border); border-radius: var(--radius);">
                                    <i class="bi bi-twitter-x" style="font-size: 0.95rem;"></i> Twitter / X
                                </a>
                                <a href="#" data-share="reddit" class="btn"
                                   style="background-color: #ff4500; color: #fff; justify-content: center; font-size: 0.8rem; padding: 6px 12px; border-radius: var(--radius);">
                                    <i class="bi bi-reddit" style="font-size: 0.95rem;"></i> Reddit
                                </a>
                                <a href="#" data-share="whatsapp" class="btn"
                                   style="background-color: #25d366; color: #fff; justify-content: center; font-size: 0.8rem; padding: 6px 12px; border-radius: var(--radius);">
                                    <i class="bi bi-whatsapp" style="font-size: 0.95rem;"></i> WhatsApp
                                </a>
                            </div>
                        </div>

                        <hr>

                        <div style="font-size:.8rem;color:var(--text-muted);">
                            <div style="margin-bottom:6px;">
                                <span><?= Lang::t('published_on') ?></span><br>
                                <strong style="color:var(--text);"><?= date('d/m/Y', strtotime($mod['created_at'])) ?></strong>
                            </div>
                            <div>
                                <span><?= Lang::t('downloads') ?></span><br>
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
                        body: JSON.stringify({visibility: visibility})
                    })
                        .then(res => {
                            if (!res.ok) throw new Error(<?= json_encode(Lang::t('visibility_update_failed')) ?>);
                            return res.json();
                        })
                        .then(data => {
                            if (data.success) {
                                if (data.visibility === 'private') {
                                    badgeContainer.innerHTML = '<span class="tag tag-private"><?= Lang::t('private') ?></span>';
                                } else {
                                    badgeContainer.innerHTML = '<span class="tag" style="background: rgba(82, 192, 124, 0.08); border-color: rgba(82, 192, 124, 0.3); color: var(--success);"><?= Lang::t('public') ?></span>';
                                }
                            } else {
                                throw new Error(data.error || <?= json_encode(Lang::t('unknown_error')) ?>);
                            }
                        })
                        .catch(err => {
                            alert(err.message || <?= json_encode(Lang::t('visibility_change_error')) ?>);
                        })
                        .finally(() => {
                            visibilityToggle.disabled = false;
                        });
                });
            }
        });
    </script>
<?php endif; ?>


<?php if (!empty($images)): ?>
<!-- Lightbox -->
<div id="lightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9999;align-items:center;justify-content:center;">
    <button onclick="closeLightbox()" style="position:absolute;top:20px;right:28px;background:none;border:none;color:#fff;font-size:2rem;cursor:pointer;line-height:1;">&times;</button>
    <button onclick="prevImage()" style="position:absolute;left:20px;background:none;border:none;color:#fff;font-size:2.5rem;cursor:pointer;user-select:none;">&#8249;</button>
    <img id="lightbox-img" src="" alt="" style="max-width:90vw;max-height:85vh;border-radius:8px;object-fit:contain;">
    <button onclick="nextImage()" style="position:absolute;right:20px;background:none;border:none;color:#fff;font-size:2.5rem;cursor:pointer;user-select:none;">&#8250;</button>
    <div id="lightbox-counter" style="position:absolute;bottom:20px;color:#fff;font-size:.85rem;opacity:.7;"></div>
</div>

<script>
const lightboxImages = <?= json_encode(array_column($images, 'image_path')) ?>;
let currentIndex = 0;

function openLightbox(index) {
    currentIndex = index;
    showImage();
    document.getElementById('lightbox').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.body.style.overflow = '';
}

function showImage() {
    document.getElementById('lightbox-img').src = lightboxImages[currentIndex];
    document.getElementById('lightbox-counter').textContent = (currentIndex + 1) + ' / ' + lightboxImages.length;
}

function prevImage() {
    currentIndex = (currentIndex - 1 + lightboxImages.length) % lightboxImages.length;
    showImage();
}

function nextImage() {
    currentIndex = (currentIndex + 1) % lightboxImages.length;
    showImage();
}

document.addEventListener('keydown', (e) => {
    const lightbox = document.getElementById('lightbox');
    if (!lightbox || lightbox.style.display !== 'flex') return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') prevImage();
    if (e.key === 'ArrowRight') nextImage();
});

document.getElementById('lightbox').addEventListener('click', (e) => {
    if (e.target.id === 'lightbox') closeLightbox();
});
</script>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
