<?php $pageTitle = 'Adicionar Categoria — Modyssey'; ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container" style="max-width:540px;">

        <div class="page-header">
            <div>
                <h1>Adicionar Categoria</h1>
                <p class="text-muted">Categorias servem para organizar os mods.</p>
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
                        <label for="game_id">Jogo *</label>
                        <select id="game_id" name="game_id" required>
                            <option value="">Selecciona o jogo correspondente</option>
                            <?php foreach ($games as $game): ?>
                                <option value="<?= $game['id'] ?>"
                                    <?= ((int)($_POST['game_id'] ?? 0) === (int)$game['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($game['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="name">Nome da Categoria *</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                            placeholder="Ex: Armas, Texturas, Sons..."
                            required
                            maxlength="100"
                        >
                    </div>

                    <div class="form-group">
                        <label for="type">Tipo *</label>
                        <input
                            type="text"
                            id="type"
                            name="type"
                            value="<?= htmlspecialchars($_POST['type'] ?? '') ?>"
                            placeholder="Ex: Gameplay, Gráficos, Interface..."
                            required
                            maxlength="80"
                        >
                        <span class="form-hint">Agrupa categorias semelhantes sob o mesmo tipo.</span>
                    </div>

                    <div style="display:flex;gap:10px;">
                        <button type="submit" class="btn btn-primary">Guardar Categoria</button>
                        <a href="<?= BASE_URL ?>/categories" class="btn btn-ghost">Cancelar</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
