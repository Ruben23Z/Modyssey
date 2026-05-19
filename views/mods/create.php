<?php $pageTitle = 'Publicar Mod — Modyssey'; ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container" style="max-width:720px;">

        <div class="page-header">
            <div>
                <h1>Publicar Mod</h1>
                <p class="text-muted">Preenche os dados do teu mod.</p>
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
                <form method="POST" action="/mods/store" enctype="multipart/form-data" novalidate
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
                                <option value="public"  <?= (($_POST['visibility'] ?? 'public') === 'public')  ? 'selected' : '' ?>>Público</option>
                                <option value="private" <?= (($_POST['visibility'] ?? '') === 'private') ? 'selected' : '' ?>>Privado</option>
                            </select>
                        </div>
                    </div>

                    <?php if (!empty($categories)): ?>
                        <div class="form-group">
                            <label>Categorias <span class="text-muted text-xs">(máx. 2)</span></label>
                            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:4px;">
                                <?php
                                $selectedCats = array_map('intval', (array)($_POST['category_ids'] ?? []));
                                foreach ($categories as $cat):
                                ?>
                                    <label class="checkbox-label" style="background:var(--bg4);border:1px solid var(--border);border-radius:var(--radius);padding:6px 12px;">
                                        <input
                                            type="checkbox"
                                            name="category_ids[]"
                                            value="<?= $cat['id'] ?>"
                                            <?= in_array((int)$cat['id'], $selectedCats, true) ? 'checked' : '' ?>
                                        >
                                        <?= htmlspecialchars($cat['name']) ?>
                                        <span class="text-xs text-muted"><?= htmlspecialchars($cat['type']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="cover_image">Imagem de Capa *</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="cover_image" name="cover_image" accept="image/*" required>
                        </div>
                        <span class="form-hint">JPEG, PNG ou WebP. Máx. 5 MB.</span>
                    </div>

                    <div class="form-group">
                        <label for="extra_images">Imagens Adicionais <span class="text-muted">(opcional)</span></label>
                        <div class="file-input-wrapper">
                            <input type="file" id="extra_images" name="extra_images[]" accept="image/*" multiple>
                        </div>
                        <span class="form-hint">Podes adicionar várias imagens de demonstração.</span>
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
                        <a href="/mods" class="btn btn-ghost">Cancelar</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
