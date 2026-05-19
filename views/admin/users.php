<?php $pageTitle = 'Administração de Utilizadores — Modyssey'; ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container">
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">Gerir Utilizadores</h2>
            </div>

            <?php if (!empty($users)): ?>
                <table class="table" style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
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
                                    <span class="role-badge <?= htmlspecialchars($user['role_name']) ?>">
                                        <?= htmlspecialchars(ucfirst($user['role_name'])) ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    <form method="POST" action="<?= BASE_URL ?>/admin/users/role" style="display: flex; gap: 0.5rem; align-items: center;">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <select name="role_id" class="form-control" style="width: auto; padding: 0.25rem; font-size: 0.9rem;">
                                            <option value="1" <?= $user['role_name'] === 'guest' ? 'selected' : '' ?>>Convidado</option>
                                            <option value="2" <?= $user['role_name'] === 'user' ? 'selected' : '' ?>>Utilizador</option>
                                            <option value="3" <?= $user['role_name'] === 'sympathizer' ? 'selected' : '' ?>>Simpatizante</option>
                                            <option value="4" <?= $user['role_name'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                                    </form>
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

<?php require __DIR__ . '/../layout/footer.php'; ?>
