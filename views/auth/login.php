<?php $pageTitle = 'Iniciar Sessão — Modyssey'; ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="auth-page">
        <div class="auth-card">

            <div class="auth-logo">
                <div class="auth-logo-text">Mod<span>yssey</span></div>
                <div class="auth-tagline">A tua biblioteca de mods</div>
            </div>

            <h1 class="auth-title">Iniciar Sessão</h1>

            <?php if (!empty($_GET['registered'])): ?>
                <div class="alert alert-success mb-16">
                    <span class="alert-icon">&#10003;</span>
                    Conta criada com sucesso. Ative a sua conta com o link enviado ao e-mail.
                </div>
            <?php endif; ?>

            <div id="js-error-alert" class="alert alert-error mb-16" style="display: none;">
                <span class="alert-icon">&#9888;</span>
                <span class="alert-msg"></span>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error mb-16">
                    <span class="alert-icon">&#9888;</span>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="<?= BASE_URL ?>/login" novalidate>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        placeholder="o.teu@email.com"
                        required
                        autocomplete="email"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width:100%; justify-content:center;">
                    Entrar
                </button>

            </form>

            <div class="auth-footer">
                Ainda não tens conta?
                <a href="<?= BASE_URL ?>/register">Regista-te</a>
            </div>

        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.auth-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const jsErrorAlert = document.getElementById('js-error-alert');
    const jsErrorMsg = jsErrorAlert.querySelector('.alert-msg');

    function showError(message, inputElement = null) {
        jsErrorMsg.textContent = message;
        jsErrorAlert.style.display = 'flex';
        if (inputElement) {
            inputElement.focus();
            inputElement.style.borderColor = 'var(--danger)';
            inputElement.style.boxShadow = '0 0 0 3px rgba(224, 85, 85, 0.15)';
        }
    }

    function clearErrors() {
        jsErrorAlert.style.display = 'none';
        emailInput.style.borderColor = '';
        emailInput.style.boxShadow = '';
        passwordInput.style.borderColor = '';
        passwordInput.style.boxShadow = '';
    }

    form.addEventListener('submit', function(e) {
        clearErrors();

        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();

        // 1. Preencher todos os campos
        if (!email || !password) {
            e.preventDefault();
            showError('Preenche todos os campos.', !email ? emailInput : passwordInput);
            return;
        }

        // 2. Email inválido
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            showError('Email inválido.', emailInput);
            return;
        }
    });

    [emailInput, passwordInput].forEach(input => {
        input.addEventListener('input', function() {
            this.style.borderColor = '';
            this.style.boxShadow = '';
        });
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
