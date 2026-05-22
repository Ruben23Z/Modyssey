<?php $pageTitle = 'Administração de Utilizadores — Modyssey'; ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container">
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">Gerir Utilizadores</h2>
            </div>

            <?php if (!empty($users)): ?>
                <table class="table table-striped table-hover" style="width: 100%; margin-top: 1rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                            <th style="padding: 1rem;">ID</th>
                            <th style="padding: 1rem;">Nome de Utilizador</th>
                            <th style="padding: 1rem;">Email</th>
                            <th style="padding: 1rem;">Cargo Atual</th>
                            <th style="padding: 1rem;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 1rem;"><?= htmlspecialchars($user['id']) ?></td>
                                <td style="padding: 1rem;"><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                                <td style="padding: 1rem; color: var(--text-muted);"><?= htmlspecialchars($user['email']) ?></td>
                                <td style="padding: 1rem;">
                                    <span class="role-badge <?= htmlspecialchars($user['role_name']) ?>" data-user-badge="<?= $user['id'] ?>">
                                        <?= htmlspecialchars(ucfirst($user['role_name'])) ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        <select class="form-select role-select" data-user-id="<?= $user['id'] ?>" style="width: auto;">
                                            <option value="1" <?= $user['role_name'] === 'guest' ? 'selected' : '' ?>>Convidado</option>
                                            <option value="2" <?= $user['role_name'] === 'user' ? 'selected' : '' ?>>Utilizador</option>
                                            <option value="3" <?= $user['role_name'] === 'sympathizer' ? 'selected' : '' ?>>Simpatizante</option>
                                            <option value="4" <?= $user['role_name'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>Nenhum utilizador encontrado.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<div id="toast-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000; display: flex; flex-direction: column; gap: 10px;"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const roleSelects = document.querySelectorAll('.role-select');
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

    roleSelects.forEach(select => {
        select.addEventListener('change', () => {
            const userId = select.getAttribute('data-user-id');
            const roleId = select.value;

            select.disabled = true;

            fetch('<?= BASE_URL ?>/api/users/role', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    user_id: userId,
                    role_id: roleId
                })
            })
            .then(res => {
                if (!res.ok) throw new Error('Falha ao atualizar cargo');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('Cargo atualizado com sucesso!', 'success');
                    const badge = document.querySelector(`[data-user-badge="${userId}"]`);
                    if (badge) {
                        badge.className = `role-badge ${data.role}`;
                        badge.textContent = data.label;
                    }
                } else {
                    throw new Error(data.error || 'Erro desconhecido');
                }
            })
            .catch(err => {
                showToast(err.message || 'Erro ao atualizar o cargo do utilizador.', 'error');
            })
            .finally(() => {
                select.disabled = false;
            });
        });
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
