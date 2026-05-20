<?php $pageTitle = 'Categorias — Modyssey'; ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container">

        <div class="page-header">
            <div>
                <h1>Categorias</h1>
                <p class="text-muted">Categorias usadas para classificar os mods.</p>
            </div>
            <div class="page-actions">
                <a href="<?= BASE_URL ?>/categories/create" class="btn btn-primary">+ Adicionar Categoria</a>
            </div>
        </div>

        <?php if (!empty($_GET['created'])): ?>
            <div class="alert alert-success mb-24">
                <span class="alert-icon">&#10003;</span>
                Categoria adicionada com sucesso.
            </div>
        <?php endif; ?>

        <?php if (!empty($categories)): ?>
            <?php
            $grouped = [];
            foreach ($categories as $cat) {
                $gameName = $cat['game_name'] ?? 'Sem Jogo';
                $grouped[$gameName][$cat['type']][] = $cat;
            }
            ?>
            <?php foreach ($grouped as $gameName => $types): ?>
                <div class="section"
                     style="margin-bottom: 2rem; border-bottom: 1px solid var(--border-soft); padding-bottom: 1.5rem;">
                    <div class="section-header" style="margin-bottom: 12px;">
                        <h2 class="section-title"
                            style="font-size: 1.3rem; color: var(--text-primary); border-left: 3px solid var(--primary); padding-left: 8px;"><?= htmlspecialchars($gameName) ?></h2>
                    </div>
                    <?php foreach ($types as $type => $items): ?>
                        <div style="margin-top: 14px; margin-bottom: 14px;">
                            <h3 style="font-size: 0.9rem; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;"><?= htmlspecialchars($type) ?></h3>
                            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                                <?php foreach ($items as $cat): ?>
                                    <div style="display:flex;align-items:center;gap:8px;background:var(--bg3);border:1px solid var(--border-soft);border-radius:var(--radius);padding:8px 14px;">
                                        <span style="font-size:0.875rem;color:var(--text);"><?= htmlspecialchars($cat['name']) ?></span>
                                        <?php if (Auth::isOwnerOrAdmin((int)$cat['added_by'])): ?>
                                            <a href="<?= BASE_URL ?>/categories/<?= $cat['id'] ?>/delete"
                                               class="btn btn-danger btn-sm"
                                               style="padding:2px 8px;font-size:0.72rem;"
                                               onclick="return confirm('Apagar a categoria \'<?= htmlspecialchars($cat['name']) ?>\'? Os mods associados serão desvinculados.')">
                                                &times;
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <span style="font-size:3rem;opacity:.15;">&#9776;</span>
                <p>Ainda não existem categorias.</p>
                <a href="<?= BASE_URL ?>/categories/create" class="btn btn-primary mt-16">Adicionar a primeira
                    categoria</a>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
