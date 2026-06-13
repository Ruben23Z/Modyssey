<?php
$games = $games ?? [];
$categories = $categories ?? [];
$mods = $mods ?? [];
$subscribedGames = $subscribedGames ?? [];
$selectedCategoryId = $selectedCategoryId ?? null;
require_once __DIR__ . '/../../models/Game.php';
require_once __DIR__ . '/../../models/Category.php';

if (!isset($games)) {
    $games = (new Game())->all();
}
if (!isset($categories)) {
    $categories = (new Category())->all();
}

$pageTitle = Lang::t('create_mod_page_title');
?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container" style="max-width:720px;">

        <div class="page-header">
            <div>
                <h1><?= Lang::t('create_mod_title') ?></h1>
                <p class="text-muted"><?= Lang::t('create_mod_subtitle') ?></p>
            </div>
        </div>

        <div id="js-error-alert" class="alert alert-error mb-24" style="display: none;">
            <span class="alert-icon">&#9888;</span>
            <span class="alert-msg"></span>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error mb-24">
                <span class="alert-icon">&#9888;</span>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/mods/store" enctype="multipart/form-data" novalidate
                      style="display:flex;flex-direction:column;gap:22px;">

                    <div class="form-group">
                        <label for="title"><?= Lang::t('title_label') ?></label>
                        <input
                                type="text"
                                id="title"
                                name="title"
                                value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                placeholder="<?= Lang::t('title_placeholder') ?>"
                                required
                                maxlength="150"
                        >
                    </div>

                    <div class="form-group">
                        <label for="description"><?= Lang::t('description_label') ?></label>
                        <textarea
                                id="description"
                                name="description"
                                placeholder="<?= Lang::t('description_placeholder') ?>"
                                required
                                rows="6"
                        ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="game_id"><?= Lang::t('game_label') ?></label>
                            <select id="game_id" name="game_id" required>
                                <option value=""><?= Lang::t('select_game') ?></option>
                                <?php foreach ($games as $game): ?>
                                    <option value="<?= $game['id'] ?>"
                                            data-extensions="<?= htmlspecialchars($game['allowed_extensions'] ?? 'zip') ?>"
                                            <?= ((int)($_POST['game_id'] ?? 0) === (int)$game['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($game['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="visibility"><?= Lang::t('visibility_label') ?></label>
                            <select id="visibility" name="visibility">
                                <option value="public" <?= (($_POST['visibility'] ?? 'public') === 'public') ? 'selected' : '' ?>>
                                    <?= Lang::t('public') ?>
                                </option>
                                <option value="private" <?= (($_POST['visibility'] ?? '') === 'private') ? 'selected' : '' ?>>
                                    <?= Lang::t('private') ?>
                                </option>
                            </select>
                        </div>
                    </div>

                    <?php
                    $categoriesByGame = [];
                    foreach ($categories as $cat) {
                        $categoriesByGame[(int)$cat['game_id']][] = [
                                'id' => (int)$cat['id'],
                                'name' => $cat['name'],
                                'type' => $cat['type'],
                        ];
                    }
                    ?>
                    <div class="form-group" id="categories-section" style="display: none;">
                        <label><?= Lang::t('categories_label') ?> <span class="text-muted text-xs"><?= Lang::t('select_exactly_2') ?></span></label>
                        <div id="categories-container" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:4px;">
                            <!-- Carregado via Javascript -->
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const categoriesByGame = <?= json_encode($categoriesByGame) ?>;
                            const gameSelect = document.getElementById('game_id');
                            const categoriesSection = document.getElementById('categories-section');
                            const categoriesContainer = document.getElementById('categories-container');
                            const selectedCats = <?= json_encode(array_map('intval', (array)($_POST['category_ids'] ?? []))) ?>;

                            const form = gameSelect.closest('form');
                            const titleInput = document.getElementById('title');
                            const descInput = document.getElementById('description');
                            const coverInput = document.getElementById('cover_image');
                            const extraInput = document.getElementById('extra_images');
                            const videoInput = document.getElementById('demo_video');
                            const fileInput = document.getElementById('mod_file');

                            const jsErrorAlert = document.getElementById('js-error-alert');
                            const jsErrorMsg = jsErrorAlert.querySelector('.alert-msg');

                            function showError(message, inputElement = null) {
                                jsErrorMsg.textContent = message;
                                jsErrorAlert.style.display = 'flex';
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                                if (inputElement) {
                                    inputElement.focus();
                                    inputElement.style.borderColor = 'var(--danger)';
                                    inputElement.style.boxShadow = '0 0 0 3px rgba(224, 85, 85, 0.15)';
                                }
                            }

                            function clearErrors() {
                                jsErrorAlert.style.display = 'none';
                                [titleInput, descInput, gameSelect, coverInput, extraInput, videoInput, fileInput].forEach(inp => {
                                    if (inp) {
                                        inp.style.borderColor = '';
                                        inp.style.boxShadow = '';
                                    }
                                });
                            }

                            function updateCategories() {
                                const gameId = gameSelect.value;
                                categoriesContainer.innerHTML = '';

                                if (!gameId || !categoriesByGame[gameId] || categoriesByGame[gameId].length === 0) {
                                    categoriesSection.style.display = 'none';
                                    return;
                                }

                                categoriesSection.style.display = 'block';
                                categoriesByGame[gameId].forEach(cat => {
                                    const isChecked = selectedCats.includes(cat.id) ? 'checked' : '';
                                    const label = document.createElement('label');
                                    label.className = 'checkbox-label';
                                    label.style.cssText = 'background:var(--bg4);border:1px solid var(--border);border-radius:var(--radius);padding:6px 12px;display:flex;align-items:center;gap:6px;cursor:pointer;';
                                    label.innerHTML = `
                                    <input type="checkbox" name="category_ids[]" value="${cat.id}" ${isChecked}>
                                    <span>${cat.name}</span>
                                    <span class="text-xs text-muted">(${cat.type})</span>
                                `;
                                    categoriesContainer.appendChild(label);
                                });
                            }

                            // Extensões de ficheiro permitidas pelo jogo selecionado
                            function getAllowedExtensions() {
                                const opt = gameSelect.options[gameSelect.selectedIndex];
                                const raw = (opt && opt.dataset.extensions) ? opt.dataset.extensions : 'zip';
                                return raw.split(',').map(e => e.trim().replace(/^\./, '').toLowerCase()).filter(e => e);
                            }

                            function updateModFileAccept() {
                                const exts = getAllowedExtensions();
                                const hint = document.getElementById('mod_file_hint');
                                if (exts.includes('*')) {
                                    fileInput.removeAttribute('accept');
                                    if (hint) hint.textContent = <?= json_encode(Lang::t('mod_file_hint_all')) ?>;
                                } else {
                                    fileInput.setAttribute('accept', exts.map(e => '.' + e).join(','));
                                    if (hint) hint.textContent = <?= json_encode(Lang::t('mod_file_hint_prefix')) ?> + ' ' + exts.map(e => '.' + e).join(', ') + '. ' + <?= json_encode(Lang::t('mod_file_hint_suffix')) ?>;
                                }
                            }

                            gameSelect.addEventListener('change', function () {
                                updateCategories();
                                updateModFileAccept();
                            });
                            updateModFileAccept();

                            categoriesContainer.addEventListener('change', function (e) {
                                if (e.target.type === 'checkbox') {
                                    const checked = categoriesContainer.querySelectorAll('input[type="checkbox"]:checked');
                                    if (checked.length > 2) {
                                        e.target.checked = false;
                                        alert(<?= json_encode(Lang::t('select_exactly_2_error')) ?>);
                                    }
                                }
                            });

                            form.addEventListener('submit', function (e) {
                                clearErrors();

                                // Title check
                                const title = titleInput.value.trim();
                                if (!title) {
                                    e.preventDefault();
                                    showError(<?= json_encode(Lang::t('js_title_required')) ?>, titleInput);
                                    return;
                                }
                                if (title.length < 3 || title.length > 150) {
                                    e.preventDefault();
                                    showError(<?= json_encode(Lang::t('js_title_length')) ?>, titleInput);
                                    return;
                                }

                                // Description check
                                const desc = descInput.value.trim();
                                if (!desc) {
                                    e.preventDefault();
                                    showError(<?= json_encode(Lang::t('js_desc_required')) ?>, descInput);
                                    return;
                                }
                                if (desc.length < 10) {
                                    e.preventDefault();
                                    showError(<?= json_encode(Lang::t('js_desc_length')) ?>, descInput);
                                    return;
                                }

                                // Game selection check
                                if (!gameSelect.value) {
                                    e.preventDefault();
                                    showError(<?= json_encode(Lang::t('js_select_game')) ?>, gameSelect);
                                    return;
                                }

                                // Categories check
                                const checked = categoriesContainer.querySelectorAll('input[type="checkbox"]:checked');
                                if (checked.length !== 2) {
                                    e.preventDefault();
                                    showError(<?= json_encode(Lang::t('js_select_2_categories')) ?>);
                                    return;
                                }

                                // Cover Image validation
                                if (!coverInput.files || coverInput.files.length === 0) {
                                    e.preventDefault();
                                    showError(<?= json_encode(Lang::t('js_cover_required')) ?>, coverInput);
                                    return;
                                }
                                const coverFile = coverInput.files[0];
                                const allowedImgTypes = ['image/jpeg', 'image/png', 'image/webp'];
                                if (!allowedImgTypes.includes(coverFile.type)) {
                                    e.preventDefault();
                                    showError(<?= json_encode(Lang::t('js_cover_type')) ?>, coverInput);
                                    return;
                                }
                                if (coverFile.size > 5 * 1024 * 1024) {
                                    e.preventDefault();
                                    showError(<?= json_encode(Lang::t('js_cover_size')) ?>, coverInput);
                                    return;
                                }

                                // Extra Images validation
                                if (extraInput.files && extraInput.files.length > 0) {
                                    for (let i = 0; i < extraInput.files.length; i++) {
                                        const extraFile = extraInput.files[i];
                                        if (!allowedImgTypes.includes(extraFile.type)) {
                                            e.preventDefault();
                                            showError(<?= json_encode(Lang::t('js_extra_type')) ?>, extraInput);
                                            return;
                                        }
                                        if (extraFile.size > 5 * 1024 * 1024) {
                                            e.preventDefault();
                                            showError(<?= json_encode(Lang::t('js_extra_size_prefix')) ?> + ' "' + extraFile.name + '" ' + <?= json_encode(Lang::t('js_extra_size_suffix')) ?>, extraInput);
                                            return;
                                        }
                                    }
                                }

                                // Demo Video validation
                                if (videoInput.files && videoInput.files.length > 0) {
                                    const videoFile = videoInput.files[0];
                                    const allowedVidTypes = ['video/mp4', 'video/webm', 'video/ogg'];
                                    if (!allowedVidTypes.includes(videoFile.type)) {
                                        e.preventDefault();
                                        showError(<?= json_encode(Lang::t('js_video_type')) ?>, videoInput);
                                        return;
                                    }
                                    if (videoFile.size > 50 * 1024 * 1024) {
                                        e.preventDefault();
                                        showError(<?= json_encode(Lang::t('js_video_size')) ?>, videoInput);
                                        return;
                                    }
                                }

                                // Mod File validation
                                if (!fileInput.files || fileInput.files.length === 0) {
                                    e.preventDefault();
                                    showError(<?= json_encode(Lang::t('js_mod_file_required')) ?>, fileInput);
                                    return;
                                }
                                const modFile = fileInput.files[0];
                                const modExt = modFile.name.split('.').pop().toLowerCase();
                                const allowedExts = getAllowedExtensions();
                                if (!allowedExts.includes('*') && !allowedExts.includes(modExt)) {
                                    e.preventDefault();
                                    showError(<?= json_encode(Lang::t('js_mod_file_format_prefix')) ?> + ' ' + allowedExts.map(e => '.' + e).join(', '), fileInput);
                                    return;
                                }
                                if (modFile.size > 500 * 1024 * 1024) {
                                    e.preventDefault();
                                    showError(<?= json_encode(Lang::t('js_mod_file_size')) ?>, fileInput);
                                    return;
                                }
                            });

                            if (gameSelect.value) {
                                updateCategories();
                            }

                            const coverPreviewContainer = document.getElementById('cover_image_preview_container');
                            const coverPreview = document.getElementById('cover_image_preview');

                            if (coverInput && coverPreviewContainer && coverPreview) {
                                coverInput.addEventListener('change', function () {
                                    const file = this.files[0];
                                    if (file) {
                                        const reader = new FileReader();
                                        reader.onload = function (e) {
                                            coverPreview.src = e.target.result;
                                            coverPreviewContainer.style.display = 'block';
                                        }
                                        reader.readAsDataURL(file);
                                    } else {
                                        coverPreviewContainer.style.display = 'none';
                                        coverPreview.src = '';
                                    }
                                });
                            }
                        });
                    </script>

                    <div class="form-group">
                        <label for="cover_image"><?= Lang::t('cover_image_label') ?></label>
                        <div class="file-input-wrapper">
                            <input type="file" id="cover_image" name="cover_image" accept="image/*" required>
                        </div>
                        <span class="form-hint"><?= Lang::t('image_hint') ?></span>
                        <div id="cover_image_preview_container" style="display: none; margin-top: 10px;">
                            <img id="cover_image_preview" src="" alt="Cover Preview"
                                 style="max-width: 200px; border-radius: var(--radius); border: 1px solid var(--border);">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="extra_images"><?= Lang::t('extra_images_label') ?> <span class="text-muted"><?= Lang::t('optional') ?></span></label>
                        <div class="file-input-wrapper">
                            <input type="file" id="extra_images" name="extra_images[]" accept="image/*" multiple>
                        </div>
                        <span class="form-hint"><?= Lang::t('extra_images_hint') ?></span>
                    </div>

                    <div class="form-group">
                        <label for="demo_video"><?= Lang::t('demo_video_label') ?> <span class="text-muted"><?= Lang::t('optional') ?></span></label>
                        <div class="file-input-wrapper">
                            <input type="file" id="demo_video" name="demo_video"
                                   accept="video/mp4,video/webm,video/ogg">
                        </div>
                        <span class="form-hint"><?= Lang::t('video_hint') ?></span>
                    </div>

                    <div class="form-group">
                        <label for="mod_file"><?= Lang::t('mod_file_label') ?></label>
                        <div class="file-input-wrapper">
                            <input type="file" id="mod_file" name="mod_file" accept=".zip" required>
                        </div>
                        <span class="form-hint" id="mod_file_hint"><?= Lang::t('mod_file_hint_default') ?></span>
                    </div>

                    <div style="display:flex;gap:10px;">
                        <button type="submit" class="btn btn-primary"><?= Lang::t('publish_button') ?></button>
                        <a href="<?= BASE_URL ?>/mods" class="btn btn-ghost"><?= Lang::t('cancel') ?></a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>