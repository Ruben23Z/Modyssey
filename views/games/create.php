<?php $pageTitle = 'Adicionar Jogo — Modyssey'; ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container" style="max-width:640px;">

        <div class="page-header">
            <div>
                <h1>Adicionar Jogo</h1>
                <p class="text-muted">Preenche os dados do jogo.</p>
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
                <form method="POST" action="<?= BASE_URL ?>/games/store" enctype="multipart/form-data" novalidate
                      style="display:flex;flex-direction:column;gap:20px;">

                    <div class="form-group">
                        <label for="name">Nome do Jogo *</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                            placeholder="Ex: The Elder Scrolls V: Skyrim"
                            required
                            maxlength="150"
                        >
                    </div>

                    <div class="form-group">
                        <label for="image">Imagem do Jogo *</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="image" name="image" accept="image/*" required>
                        </div>
                        <span class="form-hint">JPEG, PNG ou WebP. Máx. 5 MB.</span>
                    </div>

                    <div style="display:flex;gap:10px;">
                        <button type="submit" class="btn btn-primary">Guardar Jogo</button>
                        <a href="<?= BASE_URL ?>/games" class="btn btn-ghost">Cancelar</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
