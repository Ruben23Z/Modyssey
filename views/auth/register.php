<?php require_once __DIR__ . '/../../core/Lang.php'; $pageTitle = Lang::t('register_page_title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="auth-page">
        <div class="auth-card">

            <div class="auth-logo">
                <div class="auth-logo-text">Mod<span>yssey</span></div>
                <div class="auth-tagline"><?= Lang::t('auth_tagline') ?></div>
            </div>

            <h1 class="auth-title"><?= Lang::t('register_title') ?></h1>

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

            <form class="auth-form" method="POST" action="<?= BASE_URL ?>/register" novalidate>

                <div class="form-group">
                    <label for="username"><?= Lang::t('username_label') ?></label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        placeholder="<?= Lang::t('username_placeholder') ?>"
                        required
                        autocomplete="username"
                        maxlength="40"
                    >
                </div>

                <div class="form-group">
                    <label for="email"><?= Lang::t('email_label') ?></label>
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
                    <label for="password"><?= Lang::t('password_label') ?></label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        required
                        autocomplete="new-password"
                    >
                    <span class="form-hint"><?= Lang::t('password_hint') ?></span>
                </div>

                <div class="form-group">
                    <label for="confirm"><?= Lang::t('confirm_password_label') ?></label>
                    <input
                        type="password"
                        id="confirm"
                        name="confirm"
                        placeholder="••••••••"
                        required
                        autocomplete="new-password"
                    >
                </div>

                <!--  Desafio CAPTCHA -->
                <div class="form-group">
                    <label for="captcha"><i class="fa fa-lock"></i> Captcha</label>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <input
                            type="text"
                            id="captcha"
                            name="captcha"
                            placeholder="<?= Lang::t('captcha_placeholder') ?>"
                            required
                            style="flex: 1;"
                            autocomplete="off"
                        >
                        <img 
                            src="<?= BASE_URL ?>/captcha.php" 
                            alt="Captcha" 
                            style="border-radius: var(--radius); border: 1px solid var(--border); height: 42px; width: 120px; cursor: pointer; transition: border-color var(--transition);"
                            onclick="this.src='<?= BASE_URL ?>/captcha.php?r=' + Math.random();"
                            title="<?= Lang::t('captcha_reload_title') ?>"
                        >
                    </div>
                    <span class="form-hint"><?= Lang::t('captcha_reload_hint') ?></span>
                </div>



                <button type="submit" class="btn btn-primary btn-lg" style="width:100%; justify-content:center;">
                    <?= Lang::t('register_button') ?>
                </button>

            </form>

            <div class="auth-footer">
                <?= Lang::t('have_account') ?>
                <a href="<?= BASE_URL ?>/login"><?= Lang::t('login_link') ?></a>
            </div>

        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.auth-form');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm');
    const captchaInput = document.getElementById('captcha');
    const jsErrorAlert = document.getElementById('js-error-alert');
    const jsErrorMsg = jsErrorAlert.querySelector('.alert-msg');

    const inputs = [usernameInput, emailInput, passwordInput, confirmInput, captchaInput];

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
        inputs.forEach(input => {
            input.style.borderColor = '';
            input.style.boxShadow = '';
        });
    }

    form.addEventListener('submit', function(e) {
        clearErrors();

        const username = usernameInput.value.trim();
        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();
        const confirm = confirmInput.value.trim();
        const captcha = captchaInput.value.trim();

        // Preencher todos os campos
        if (!username || !email || !password || !confirm || !captcha) {
            e.preventDefault();
            let firstEmpty = null;
            for (let input of inputs) {
                if (!input.value.trim()) {
                    firstEmpty = input;
                    break;
                }
            }
            showError(<?= json_encode(Lang::t('fill_all_fields')) ?>, firstEmpty);
            return;
        }

        // Email inválido
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            showError(<?= json_encode(Lang::t('invalid_email')) ?>, emailInput);
            return;
        }

        // A password deve ter pelo menos 8 caracteres.
        if (password.length < 8) {
            e.preventDefault();
            showError(<?= json_encode(Lang::t('password_min_length')) ?>, passwordInput);
            return;
        }

        // As passwords não coincidem.
        if (password !== confirm) {
            e.preventDefault();
            showError(<?= json_encode(Lang::t('passwords_dont_match')) ?>, confirmInput);
            return;
        }
    });

    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.style.borderColor = '';
            this.style.boxShadow = '';
        });
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
