<?php $pageTitle = 'Criar Conta — Modyssey'; ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="auth-page">
        <div class="auth-card">

            <div class="auth-logo">
                <div class="auth-logo-text">Mod<span>yssey</span></div>
                <div class="auth-tagline">A tua biblioteca de mods</div>
            </div>

            <h1 class="auth-title">Criar Conta</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error mb-16">
                    <span class="alert-icon">&#9888;</span>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="<?= BASE_URL ?>/register" novalidate>

                <div class="form-group">
                    <label for="username">Nome de Utilizador</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        placeholder="o_teu_nome"
                        required
                        autocomplete="username"
                        maxlength="40"
                    >
                </div>

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
                        autocomplete="new-password"
                    >
                    <span class="form-hint">Mínimo de 8 caracteres.</span>
                </div>

                <div class="form-group">
                    <label for="confirm">Confirmar Password</label>
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
                            placeholder="Insere o código"
                            required
                            style="flex: 1;"
                            autocomplete="off"
                        >
                        <img 
                            src="<?= BASE_URL ?>/captcha.php" 
                            alt="Captcha" 
                            style="border-radius: var(--radius); border: 1px solid var(--border); height: 42px; width: 120px; cursor: pointer; transition: border-color var(--transition);"
                            onclick="this.src='<?= BASE_URL ?>/captcha.php?r=' + Math.random();"
                            title="Clique para recarregar"
                        >
                    </div>
                    <span class="form-hint">Clique na imagem para recarregar.</span>
                </div>



                <button type="submit" class="btn btn-primary btn-lg" style="width:100%; justify-content:center;">
                    Criar Conta
                </button>

            </form>

            <div class="auth-footer">
                Já tens conta?
                <a href="<?= BASE_URL ?>/login">Inicia sessão</a>
            </div>

        </div>
    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
