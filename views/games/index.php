<?php $pageTitle = 'Jogos — Modyssey'; ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container">

        <div class="page-header">
            <div>
                <h1>Jogos</h1>
                <p class="text-muted">Todos os jogos disponíveis na plataforma.</p>
            </div>
            <?php if (Auth::can('sympathizer')): ?>
                <div class="page-actions">
                    <a href="<?= BASE_URL ?>/games/create" class="btn btn-primary">+ Adicionar Jogo</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($_GET['created'])): ?>
            <div class="alert alert-success mb-24">
                <span class="alert-icon">&#10003;</span>
                Jogo adicionado com sucesso.
            </div>
        <?php endif; ?>

        <?php if (!empty($games)): ?>
            <div class="grid grid-4">
                <?php foreach ($games as $game): ?>
                    <div class="card">
                        <?php if ($game['image_path']): ?>
                            <img
                                src="<?= htmlspecialchars($game['image_path']) ?>"
                                alt="<?= htmlspecialchars($game['name']) ?>"
                                style="width:100%;aspect-ratio:16/9;object-fit:cover;"
                            >
                        <?php endif; ?>
                        <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                            <a href="<?= BASE_URL ?>/games/<?= $game['id'] ?>" style="font-weight:600;color:var(--text);text-decoration:none;">
                                <?= htmlspecialchars($game['name']) ?>
                            </a>
                            <?php if (Auth::isOwnerOrAdmin((int) $game['added_by'])): ?>
                                <a href="<?= BASE_URL ?>/games/<?= $game['id'] ?>/delete"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Apagar este jogo? Esta acção remove todos os mods associados.')">
                                    Apagar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span style="font-size:3rem;opacity:.15;">&#127918;</span>
                <p>Ainda não existem jogos registados.</p>
                <?php if (Auth::can('sympathizer')): ?>
                    <a href="<?= BASE_URL ?>/games/create" class="btn btn-primary mt-16">Adicionar o primeiro jogo</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
