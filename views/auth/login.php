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
                    Conta criada com sucesso. Podes iniciar sessão agora.
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error mb-16">
                    <span class="alert-icon">&#9888;</span>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="/login" novalidate>

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
                <a href="/register">Regista-te</a>
            </div>

        </div>
    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
