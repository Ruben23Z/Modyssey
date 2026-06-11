<?php
// Ensure variables are defined to avoid undefined variable notices
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

$pageTitle = 'Publicar Mod — Modyssey'; 
?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container" style="max-width:720px;">

        <div class="page-header">
            <div>
                <h1>Publicar Mod</h1>
                <p class="text-muted">Preenche os dados do teu mod.</p>
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
                        <label for="title">Título *</label>
                        <input
                                type="text"
                                id="title"
                                name="title"
                                value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                placeholder="Nome do teu mod"
                                required
                                maxlength="150"
                        >
                    </div>

                    <div class="form-group">
                        <label for="description">Descrição *</label>
                        <textarea
                                id="description"
                                name="description"
                                placeholder="Descreve o teu mod, o que faz, como instalar..."
                                required
                                rows="6"
                        ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="game_id">Jogo *</label>
                            <select id="game_id" name="game_id" required>
                                <option value="">Selecciona um jogo</option>
                                <?php foreach ($games as $game): ?>
                                    <option value="<?= $game['id'] ?>"
                                            <?= ((int)($_POST['game_id'] ?? 0) === (int)$game['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($game['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="visibility">Visibilidade</label>
                            <select id="visibility" name="visibility">
                                <option value="public" <?= (($_POST['visibility'] ?? 'public') === 'public') ? 'selected' : '' ?>>
                                    Público
                                </option>
                                <option value="private" <?= (($_POST['visibility'] ?? '') === 'private') ? 'selected' : '' ?>>
                                    Privado
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
                        <label>Categorias <span class="text-muted text-xs">(selecciona exatamente 2)</span></label>
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

                            gameSelect.addEventListener('change', updateCategories);

                            categoriesContainer.addEventListener('change', function (e) {
                                if (e.target.type === 'checkbox') {
                                    const checked = categoriesContainer.querySelectorAll('input[type="checkbox"]:checked');
                                    if (checked.length > 2) {
                                        e.target.checked = false;
                                        alert('Deves selecionar exatamente 2 categorias.');
                                    }
                                }
                            });

                            form.addEventListener('submit', function (e) {
                                clearErrors();

                                // Title check
                                const title = titleInput.value.trim();
                                if (!title) {
                                    e.preventDefault();
                                    showError('O título é obrigatório.', titleInput);
                                    return;
                                }
                                if (title.length < 3 || title.length > 150) {
                                    e.preventDefault();
                                    showError('O título do mod deve ter entre 3 e 150 caracteres.', titleInput);
                                    return;
                                }

                                // Description check
                                const desc = descInput.value.trim();
                                if (!desc) {
                                    e.preventDefault();
                                    showError('A descrição é obrigatória.', descInput);
                                    return;
                                }
                                if (desc.length < 10) {
                                    e.preventDefault();
                                    showError('A descrição do mod deve ter pelo menos 10 caracteres.', descInput);
                                    return;
                                }

                                // Game selection check
                                if (!gameSelect.value) {
                                    e.preventDefault();
                                    showError('Seleciona um jogo da lista.', gameSelect);
                                    return;
                                }

                                // Categories check
                                const checked = categoriesContainer.querySelectorAll('input[type="checkbox"]:checked');
                                if (checked.length !== 2) {
                                    e.preventDefault();
                                    showError('Tens de selecionar exatamente 2 categorias.');
                                    return;
                                }

                                // Cover Image validation
                                if (!coverInput.files || coverInput.files.length === 0) {
                                    e.preventDefault();
                                    showError('A imagem de capa é obrigatória.', coverInput);
                                    return;
                                }
                                const coverFile = coverInput.files[0];
                                const allowedImgTypes = ['image/jpeg', 'image/png', 'image/webp'];
                                if (!allowedImgTypes.includes(coverFile.type)) {
                                    e.preventDefault();
                                    showError('A imagem de capa deve ser do tipo JPEG, PNG ou WebP.', coverInput);
                                    return;
                                }
                                if (coverFile.size > 5 * 1024 * 1024) {
                                    e.preventDefault();
                                    showError('A imagem de capa é demasiado grande. O máximo é 5 MB.', coverInput);
                                    return;
                                }

                                // Extra Images validation
                                if (extraInput.files && extraInput.files.length > 0) {
                                    for (let i = 0; i < extraInput.files.length; i++) {
                                        const extraFile = extraInput.files[i];
                                        if (!allowedImgTypes.includes(extraFile.type)) {
                                            e.preventDefault();
                                            showError('As imagens adicionais devem ser do tipo JPEG, PNG ou WebP.', extraInput);
                                            return;
                                        }
                                        if (extraFile.size > 5 * 1024 * 1024) {
                                            e.preventDefault();
                                            showError('A imagem adicional "' + extraFile.name + '" excede o limite de 5 MB.', extraInput);
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
                                        showError('O vídeo de demonstração deve ser MP4, WebM ou OGG.', videoInput);
                                        return;
                                    }
                                    if (videoFile.size > 50 * 1024 * 1024) {
                                        e.preventDefault();
                                        showError('O vídeo de demonstração excede o limite de 50 MB.', videoInput);
                                        return;
                                    }
                                }

                                // Mod File validation
                                if (!fileInput.files || fileInput.files.length === 0) {
                                    e.preventDefault();
                                    showError('O ficheiro do mod é obrigatório.', fileInput);
                                    return;
                                }
                                const modFile = fileInput.files[0];
                                const modExt = modFile.name.split('.').pop().toLowerCase();
                                if (modExt !== 'zip') {
                                    e.preventDefault();
                                    showError('O ficheiro do mod tem de ser do formato ZIP.', fileInput);
                                    return;
                                }
                                if (modFile.size > 500 * 1024 * 1024) {
                                    e.preventDefault();
                                    showError('O ficheiro do mod excede o tamanho máximo de 500 MB.', fileInput);
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
                        <label for="cover_image">Imagem de Capa *</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="cover_image" name="cover_image" accept="image/*" required>
                        </div>
                        <span class="form-hint">JPEG, PNG ou WebP. Máx. 5 MB.</span>
                        <div id="cover_image_preview_container" style="display: none; margin-top: 10px;">
                            <img id="cover_image_preview" src="" alt="Cover Preview"
                                 style="max-width: 200px; border-radius: var(--radius); border: 1px solid var(--border);">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="extra_images">Imagens Adicionais <span class="text-muted">(opcional)</span></label>
                        <div class="file-input-wrapper">
                            <input type="file" id="extra_images" name="extra_images[]" accept="image/*" multiple>
                        </div>
                        <span class="form-hint">Podes adicionar várias imagens de demonstração.</span>
                    </div>

                    <div class="form-group">
                        <label for="demo_video">Vídeo de Demonstração <span class="text-muted">(opcional)</span></label>
                        <div class="file-input-wrapper">
                            <input type="file" id="demo_video" name="demo_video"
                                   accept="video/mp4,video/webm,video/ogg">
                        </div>
                        <span class="form-hint">Apenas ficheiros MP4, WebM ou OGG. Máx. 50 MB.</span>
                    </div>

                    <div class="form-group">
                        <label for="mod_file">Ficheiro do Mod *</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="mod_file" name="mod_file" accept=".zip" required>
                        </div>
                        <span class="form-hint">Apenas ficheiros ZIP. Máx. 500 MB.</span>
                    </div>

                    <div style="display:flex;gap:10px;">
                        <button type="submit" class="btn btn-primary">Publicar Mod</button>
                        <a href="<?= BASE_URL ?>/mods" class="btn btn-ghost">Cancelar</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
