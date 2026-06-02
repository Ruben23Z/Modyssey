<?php
$games                = $games ?? [];
$categories           = $categories ?? [];
$subscribedGames      = $subscribedGames ?? [];
$subscribedCategories = $subscribedCategories ?? [];
?>
<?php $pageTitle = 'Subscrições — Modyssey'; ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container">

        <div class="page-header" style="margin-bottom: 32px;">
            <div>
                <h1>As Minhas Subscrições</h1>
                <p class="text-muted">Subscreve jogos ou categorias específicas para receber notificações por e-mail
                    quando novos mods forem publicados.</p>
            </div>
        </div>

        <div id="toast-container"
             style="position: fixed; bottom: 20px; right: 20px; z-index: 1000; display: flex; flex-direction: column; gap: 10px;"></div>

        <?php if (!empty($games)): ?>
            <div class="grid grid-3"
                 style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
                <?php foreach ($games as $game): ?>
                    <?php
                    $isGameSubbed = in_array((int)$game['id'], array_map('intval', $subscribedGames));
                    ?>
                    <div class="card"
                         style="background: var(--bg2); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; display: flex; flex-direction: column; transition: transform var(--transition), border-color var(--transition); position: relative;">

                        <?php if ($game['image_path']): ?>
                            <div style="position: relative; width: 100%; aspect-ratio: 16/9; overflow: hidden;">
                                <img
                                        src="<?= htmlspecialchars($game['image_path']) ?>"
                                        alt="<?= htmlspecialchars($game['name']) ?>"
                                        style="width: 100%; height: 100%; object-fit: cover;"
                                >
                                <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(11, 12, 17, 0.95), transparent);"></div>
                            </div>
                        <?php endif; ?>

                        <div class="card-body"
                             style="padding: 20px; flex: 1; display: flex; flex-direction: column; gap: 16px; margin-top: <?php echo $game['image_path'] ? '-40px' : '0'; ?>; position: relative; z-index: 2;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                                <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                                    <?= htmlspecialchars($game['name']) ?>
                                </h3>
                                <button
                                        class="btn sub-toggle"
                                        data-type="game"
                                        data-id="<?= $game['id'] ?>"
                                        style="padding: 6px 14px; font-size: 0.85rem; border-radius: 20px; cursor: pointer; transition: all var(--transition); border: 1px solid <?= $isGameSubbed ? 'var(--accent)' : 'var(--border)' ?>; background: <?= $isGameSubbed ? 'var(--accent)' : 'var(--bg4)' ?>; color: #fff;"
                                >
                                    <?= $isGameSubbed ? 'Subscrito' : 'Subscrever' ?>
                                </button>
                            </div>

                            <hr style="border: 0; border-top: 1px solid var(--border); margin: 4px 0;">

                            <div>
                                <h4 style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 10px; letter-spacing: 1px;">
                                    Categorias Disponíveis
                                </h4>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <?php
                                    $gameCats = array_filter($categories, function ($cat) use ($game) {
                                        return (int)$cat['game_id'] === (int)$game['id'];
                                    });
                                    ?>
                                    <?php if (!empty($gameCats)): ?>
                                        <?php foreach ($gameCats as $cat): ?>
                                            <?php
                                            $isCatSubbed = in_array((int)$cat['id'], array_map('intval', $subscribedCategories));
                                            ?>
                                            <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg3); border: 1px solid var(--border); border-radius: var(--radius); padding: 8px 12px; gap: 10px;">
                                                <div style="display: flex; flex-direction: column;">
                                                    <span style="font-size: 0.9rem; font-weight: 600; color: var(--text);"><?= htmlspecialchars($cat['name']) ?></span>
                                                    <span style="font-size: 0.7rem; color: var(--text-muted);"><?= htmlspecialchars($cat['type']) ?></span>
                                                </div>
                                                <button
                                                        class="btn btn-sm sub-toggle"
                                                        data-type="category"
                                                        data-id="<?= $cat['id'] ?>"
                                                        style="padding: 4px 10px; font-size: 0.75rem; border-radius: 12px; cursor: pointer; transition: all var(--transition); border: 1px solid <?= $isCatSubbed ? 'var(--accent)' : 'var(--border)' ?>; background: <?= $isCatSubbed ? 'var(--accent)' : 'var(--bg4)' ?>; color: #fff;"
                                                >
                                                    <?= $isCatSubbed ? 'Subscrito' : 'Subscrever' ?>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p style="font-size: 0.85rem; color: var(--text-muted); font-style: italic; margin: 0;">
                                            Nenhuma categoria adicionada.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span style="font-size:3rem;opacity:.15;">&#127918;</span>
                <p>Nenhum jogo registado para subscrição.</p>
            </div>
        <?php endif; ?>

    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggles = document.querySelectorAll('.sub-toggle');
        const toastContainer = document.getElementById('toast-container');

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.style.background = type === 'success' ? 'var(--success)' : 'var(--danger)';
            toast.style.color = '#fff';
            toast.style.padding = '12px 20px';
            toast.style.borderRadius = 'var(--radius)';
            toast.style.boxShadow = 'var(--shadow)';
            toast.style.fontSize = '0.9rem';
            toast.style.fontWeight = '600';
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            toast.style.transform = 'translateY(20px)';
            toast.innerHTML = message;

            toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            }, 10);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }

        toggles.forEach(btn => {
            btn.addEventListener('click', () => {
                const type = btn.getAttribute('data-type');
                const id = btn.getAttribute('data-id');

                btn.disabled = true;

                fetch('<?= BASE_URL ?>/subscriptions/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({type, id})
                })
                    .then(res => {
                        if (!res.ok) throw new Error('Erro na comunicação com o servidor');
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            if (data.subscribed) {
                                btn.style.background = 'var(--accent)';
                                btn.style.borderColor = 'var(--accent)';
                                btn.textContent = 'Subscrito';
                                showToast(`Subscrição efetuada com sucesso!`, 'success');
                            } else {
                                btn.style.background = 'var(--bg4)';
                                btn.style.borderColor = 'var(--border)';
                                btn.textContent = 'Subscrever';
                                showToast(`Subscrição removida.`, 'success');
                            }
                        } else {
                            throw new Error(data.error || 'Erro ao processar');
                        }
                    })
                    .catch(err => {
                        showToast(err.message || 'Ocorreu um erro.', 'error');
                    })
                    .finally(() => {
                        btn.disabled = false;
                    });
            });
        });
    });
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
