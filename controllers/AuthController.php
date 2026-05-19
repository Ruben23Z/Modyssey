<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function loginForm(): void
    {
        if (Auth::isLoggedIn()) {
            header('Location: /');
            exit;
        }
        require __DIR__ . '/../views/auth/login.php';
    }

    public function login(): void
    {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!$email || !$password) {
            $error = 'Preenche todos os campos.';
            require __DIR__ . '/../views/auth/login.php';
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Credenciais inválidas.';
            require __DIR__ . '/../views/auth/login.php';
            return;
        }

        Auth::login($user);
        header('Location: /');
        exit;
    }

    public function registerForm(): void
    {
        if (Auth::isLoggedIn()) {
            header('Location: /');
            exit;
        }
        require __DIR__ . '/../views/auth/register.php';
    }

    public function register(): void
    {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm  = trim($_POST['confirm']  ?? '');

        if (!$username || !$email || !$password || !$confirm) {
            $error = 'Preenche todos os campos.';
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email inválido.';
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        if (strlen($password) < 8) {
            $error = 'A password deve ter pelo menos 8 caracteres.';
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        if ($password !== $confirm) {
            $error = 'As passwords não coincidem.';
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        if ($this->userModel->emailExists($email)) {
            $error = 'Este email já está registado.';
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        if ($this->userModel->usernameExists($username)) {
            $error = 'Este nome de utilizador já existe.';
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        $this->userModel->create($username, $email, $password);
        header('Location: /login?registered=1');
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }
}
