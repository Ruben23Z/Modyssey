<?php
$pageTitle = Lang::t('sub_page_title') . ' — Modyssey';
require __DIR__ . '/../layout/header.php';
?>

<main>
    <div class="container">

        <div class="page-header">
            <div>
                <h1><?= Lang::t('sub_page_title') ?></h1>
                <p class="text-muted"><?= Lang::t('sub_page_subtitle') ?></p>
            </div>
        </div>

        <!-- Subscribed Games Section -->
        <section style="margin-bottom: 48px;">
            <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:16px;color:var(--text);">
                Jogos Subscritos
            </h2>
            <?php if (!empty($subscribedGames)): ?>
                <div class="grid grid-4">
                    <?php foreach ($subscribedGames as $game): ?>
                        <div class="card" style="position:relative;">
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
                                <form method="POST" action="<?= BASE_URL ?>/subscriptions/toggle">
                                    <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm"
                                            onclick="return confirm('Cancelar subscrição de \'<?= htmlspecialchars(addslashes($game['name'])) ?>\'?')">
                                        <?= Lang::t('sub_unsubscribe') ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <span style="font-size:3rem;opacity:.15;">&#128276;</span>
                    <p><?= Lang::t('sub_no_subs') ?></p>
                    <a href="<?= BASE_URL ?>/games" class="btn btn-primary mt-16">Ver Jogos</a>
                </div>
            <?php endif; ?>
        </section>

        <!-- Notifications Section -->
        <section>
            <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:16px;color:var(--text);">
                <?= Lang::t('sub_notifications_title') ?>
            </h2>
            <?php if (!empty($notifications)): ?>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <?php foreach ($notifications as $notif): ?>
                        <div style="
                            background: var(--bg2);
                            border: 1px solid var(--border);
                            border-radius: var(--radius);
                            padding: 14px 18px;
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 16px;
                            <?= !$notif['is_read'] ? 'border-left: 3px solid var(--accent, #e74c3c);' : '' ?>
                        ">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <span style="font-size:1.3rem;">&#128276;</span>
                                <div>
                                    <p style="margin:0;font-size:0.9rem;color:var(--text);">
                                        <?= htmlspecialchars($notif['message']) ?>
                                    </p>
                                    <span style="font-size:0.75rem;color:var(--text-muted);">
                                        <?= date('d/m/Y H:i', strtotime($notif['created_at'])) ?>
                                    </span>
                                </div>
                            </div>
                            <?php if (!$notif['is_read']): ?>
                                <span class="tag" style="background:var(--accent,#e74c3c);color:#fff;font-size:0.7rem;font-weight:700;white-space:nowrap;">
                                    <?= Lang::t('sub_new') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <span style="font-size:3rem;opacity:.15;">&#128276;</span>
                    <p><?= Lang::t('sub_no_notifications') ?></p>
                </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
