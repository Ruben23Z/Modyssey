<?php $pageTitle = 'Definições do Sistema — Modyssey'; ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container" style="max-width: 800px;">
        <div class="page-header" style="margin-bottom: 32px;">
            <div>
                <h1>Definições do Sistema</h1>
                <p class="text-muted">Gira as credenciais da base de dados e os parâmetros do servidor de correio eletrónico (SMTP).</p>
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

        <form id="settings-form" method="POST" action="<?= BASE_URL ?>/admin/settings" style="display: flex; flex-direction: column; gap: 24px;" novalidate>
            
            <div class="card">
                <div class="card-header" style="font-weight: 700; color: var(--accent); border-bottom: 1px solid var(--border); padding: 16px 20px; font-size: 1.1rem;">
                    Ligação à Base de Dados
                </div>
                <div class="card-body" style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                    <div class="row">
                        <div class="col-md-9 form-group">
                            <label for="db_host">Servidor (Host)</label>
                            <input type="text" class="form-control" id="db_host" name="db_host" value="<?= htmlspecialchars($db->host ?? 'localhost') ?>" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="db_port">Porta</label>
                            <input type="text" class="form-control" id="db_port" name="db_port" value="<?= htmlspecialchars($db->port ?? '3306') ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="db_name">Nome da Base de Dados</label>
                            <input type="text" class="form-control" id="db_name" name="db_name" value="<?= htmlspecialchars($db->db ?? 'modyssey') ?>" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="db_user">Utilizador</label>
                            <input type="text" class="form-control" id="db_user" name="db_user" value="<?= htmlspecialchars($db->username ?? 'root') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="db_pass">Palavra-passe</label>
                        <input type="password" class="form-control" id="db_pass" name="db_pass" value="<?= htmlspecialchars($db->password ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="font-weight: 700; color: var(--success); border-bottom: 1px solid var(--border); padding: 16px 20px; font-size: 1.1rem;">
                    Servidor de Correio Eletrónico (SMTP)
                </div>
                <div class="card-body" style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label for="smtp_server">Servidor SMTP</label>
                            <input type="text" class="form-control" id="smtp_server" name="smtp_server" value="<?= htmlspecialchars($email->Server ?? 'smtp.gmail.com') ?>" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="smtp_port">Porta SMTP</label>
                            <input type="number" class="form-control" id="smtp_port" name="smtp_port" value="<?= (int)($email->Port ?? 465) ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="smtp_ssl">Usar SSL / TLS</label>
                            <select class="form-select form-control" id="smtp_ssl" name="smtp_ssl">
                                <option value="true" <?= ($email && (string)$email->SSL === 'TRUE') ? 'selected' : '' ?>>Sim (SSL/TLS)</option>
                                <option value="false" <?= ($email && (string)$email->SSL === 'FALSE') ? 'selected' : '' ?>>Não</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="smtp_user">Utilizador SMTP (E-mail)</label>
                            <input type="email" class="form-control" id="smtp_user" name="smtp_user" value="<?= htmlspecialchars($email->LoginName ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="smtp_pass">Palavra-passe do E-mail</label>
                        <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" value="<?= htmlspecialchars($email->Password ?? '') ?>">
                        <span class="form-hint">No caso do Gmail, use uma palavra-passe de aplicação gerada no Google Account.</span>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 10px;">
                <button type="submit" class="btn btn-primary">Guardar Definições</button>
                <a href="<?= BASE_URL ?>/admin/users" class="btn btn-ghost">Voltar para Utilizadores</a>
            </div>

        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('settings-form');
    const dbHost = document.getElementById('db_host');
    const dbPort = document.getElementById('db_port');
    const dbName = document.getElementById('db_name');
    const dbUser = document.getElementById('db_user');
    const smtpServer = document.getElementById('smtp_server');
    const smtpPort = document.getElementById('smtp_port');
    const smtpUser = document.getElementById('smtp_user');

    const jsErrorAlert = document.getElementById('js-error-alert');
    const jsErrorMsg = jsErrorAlert.querySelector('.alert-msg');

    const inputs = [dbHost, dbPort, dbName, dbUser, smtpServer, smtpPort, smtpUser];

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
        inputs.forEach(input => {
            input.style.borderColor = '';
            input.style.boxShadow = '';
        });
    }

    form.addEventListener('submit', function (e) {
        clearErrors();

        // 1. Mandatory fields
        for (let input of inputs) {
            if (!input.value.trim()) {
                e.preventDefault();
                showError('Preenche todos os campos obrigatórios.', input);
                return;
            }
        }

        // 2. Database Port Validation
        const dbPortVal = parseInt(dbPort.value.trim(), 10);
        if (isNaN(dbPortVal) || dbPortVal < 1 || dbPortVal > 65535 || String(dbPortVal) !== dbPort.value.trim()) {
            e.preventDefault();
            showError('A porta da base de dados deve ser um número entre 1 e 65535.', dbPort);
            return;
        }

        // 3. SMTP Port Validation
        const smtpPortVal = parseInt(smtpPort.value.trim(), 10);
        if (isNaN(smtpPortVal) || smtpPortVal < 1 || smtpPortVal > 65535 || String(smtpPortVal) !== smtpPort.value.trim()) {
            e.preventDefault();
            showError('A porta SMTP deve ser um número entre 1 e 65535.', smtpPort);
            return;
        }

        // 4. SMTP User (Email) Validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(smtpUser.value.trim())) {
            e.preventDefault();
            showError('O utilizador SMTP deve ser um e-mail válido.', smtpUser);
            return;
        }
    });

    inputs.forEach(input => {
        input.addEventListener('input', function () {
            this.style.borderColor = '';
            this.style.boxShadow = '';
        });
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
