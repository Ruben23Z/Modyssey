<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../Lib/HtmlMimeMail.php';
require_once __DIR__ . '/../Lib/lib.php';
require_once __DIR__ . '/../Lib/lib-mail-v2.php';

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
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        require __DIR__ . '/../views/auth/login.php';
    }

    public function login(): void
    {

        $email = trim($_POST['email'] ?? '');
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

        if ($user['active'] == 0) {
            $error = 'Por favor, confirma o teu registo através do e-mail enviado.';
            require __DIR__ . '/../views/auth/login.php';
            return;
        }


        Auth::login($user);
        header('Location: ' . BASE_URL . '/');
        exit;
    }

    public function registerForm(): void
    {
        if (Auth::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        require __DIR__ . '/../views/auth/register.php';
    }

    public function register(): void
    {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm = trim($_POST['confirm'] ?? '');
        $captcha = trim($_POST['captcha'] ?? '');

        if (!$username || !$email || !$password || !$confirm || !$captcha) {
            $error = 'Preenche todos os campos.';
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        if (strtolower($captcha) !== strtolower($_SESSION['captcha'] ?? '')) {
            $error = 'O código Captcha está incorreto.';
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


        $token = md5(uniqid(rand(), true));
        $this->userModel->createWithToken($username, $email, $password, $token);
        
        // Construção do link de ativação dinâmico
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https' : 'http';
        $serverName = $_SERVER['SERVER_NAME'];
        $serverPort = $_SERVER['SERVER_PORT'];
        $portPart = '';
        if (($protocol === 'http' && $serverPort != 80) || ($protocol === 'https' && $serverPort != 443)) {
            $portPart = ":$serverPort";
        }
        $link = "$protocol://$serverName$portPart" . BASE_URL . "/confirmar?token=$token";

        $emailConfigFile = __DIR__ . '/../config/configuracoes/.htconfigEmail.xml';
        if (!file_exists($emailConfigFile)) {
            throw new Exception("Ficheiro de configuração de e-mail não encontrado.");
        }

        $xml = simplexml_load_file($emailConfigFile);
        if ($xml === false) {
            throw new Exception("Erro ao ler o ficheiro de configuração de e-mail.");
        }

        $smtpServer = (string)$xml->Account->Server;
        $useSSL = strtoupper((string)$xml->Account->SSL) === 'TRUE' ? 1 : 0;
        $port = (int)$xml->Account->Port;
        $loginName = (string)$xml->Account->LoginName;
        $passwordEmail = (string)$xml->Account->Password;
        $fromEmail = (string)$xml->Account->Email;
        $displayName = (string)$xml->Account->DisplayName;

        /* campos do emial*/
        $subject = "Ativacao da Conta - SMI";
        $msgHtml = "<h2>Bem-vindo, $username!</h2><p>Clique no link para ativar a conta:</p><a href='$link'>$link</a>";
        $mail = new HtmlMimeMail();
        $mail->add_html($msgHtml, strip_tags($msgHtml));
        $mail->build_message();

        /*  Envio efectivo do correio eletrónico utilizando as credenciais SMTP lidas do ficheiro XML.*/
        $enviou = @$mail->send($smtpServer, $useSSL, $port, $loginName, $passwordEmail, $username, $email, $displayName, $fromEmail, $subject);

        if (!$enviou) {
            throw new Exception("Falha ao enviar e-mail. Verifique as credenciais SMTP e a palavra-passe de aplicação do Gmail.");
        }

        $_SESSION['message'] = "Registo efetuado! Verifique o e-mail: $email ou use o link de ativação: <a href='$link' style='color: yellow;'>$link</a>";
        $_SESSION['toastClass'] = "bg-success";

        header('Location: ' . BASE_URL . '/login?registered=1');
        exit;
    }


    public function confirmEmail()
    {

        $token = trim($_GET['token'] ?? '');

        if (!$token) {
            $_SESSION['message'] = 'Token de ativação em falta.';
            $_SESSION['toastClass'] = 'bg-danger';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }


        if ($this->userModel->activateByToken($token)) {

            $_SESSION['message'] = 'Conta ativada com sucesso! Já podes iniciar sessão.';
            $_SESSION['toastClass'] = 'bg-success';
        } else {
            $_SESSION['message'] = 'Token inválido, expirado ou conta já ativada.';
            $_SESSION['toastClass'] = 'bg-danger';
        }
        header('Location: ' . BASE_URL . '/login');
        exit;

    }

    public function captcha(): void
    {
        require __DIR__ . '/../captcha/captcha.php';
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }


}
