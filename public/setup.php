<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$configFile = __DIR__ . '/../config/configuracoes/.htconfig.xml';
if (file_exists($configFile)) {
    header('Location: /Modyssey/public/');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbPort = trim($_POST['db_port'] ?? '3306');
    $dbName = trim($_POST['db_name'] ?? 'modyssey');
    $dbUser = trim($_POST['db_user'] ?? 'root');
    $dbPass = trim($_POST['db_pass'] ?? '');

    $adminUser = trim($_POST['admin_user'] ?? 'admin');
    $adminEmail = trim($_POST['admin_email'] ?? 'admin@modyssey.local');
    $adminPass = trim($_POST['admin_pass'] ?? '');

    $smtpServer = trim($_POST['smtp_server'] ?? 'smtp.gmail.com');
    $smtpPort = (int)($_POST['smtp_port'] ?? 465);
    $smtpSSL = ($_POST['smtp_ssl'] ?? 'true') === 'true' ? 'TRUE' : 'FALSE';
    $smtpUser = trim($_POST['smtp_user'] ?? 'smitrabalhopratico@gmail.com');
    $smtpPass = trim($_POST['smtp_pass'] ?? '');

    if (!$dbHost || !$dbName || !$dbUser || !$adminUser || !$adminEmail || !$adminPass) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        try {
            // Tentar conectar ao MySQL
            $dsn = "mysql:host=$dbHost;port=$dbPort;charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Criar a Base de Dados
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $pdo->exec("USE `$dbName`;");

            // Executar o Schema SQL
            $schemaFile = __DIR__ . '/../modyssey_schema (1).sql';
            if (!file_exists($schemaFile)) {
                throw new Exception("Ficheiro modyssey_schema (1).sql não encontrado.");
            }

            $sql = file_get_contents($schemaFile);
            // Limpar instruções de CREATE DATABASE/USE do schema para usar o nome introduzido
            $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS \w+;/i', '', $sql);
            $sql = preg_replace('/USE \w+;/i', '', $sql);

            $pdo->exec($sql);

            // Inserir Utilizador Administrador
            $hashedPass = password_hash($adminPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO user (username, email, password, IDRole, active) VALUES (?, ?, ?, 4, 1)");
            $stmt->execute([$adminUser, $adminEmail, $hashedPass]);

            // Escrever .htconfig.xml
            $configDir = __DIR__ . '/../config/configuracoes';
            if (!is_dir($configDir)) {
                mkdir($configDir, 0755, true);
            }

            $dbXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Config>
  <DataBase>
    <host>{$dbHost}</host>
    <port>{$dbPort}</port>
    <db>{$dbName}</db>
    <username>{$dbUser}</username>
    <password>{$dbPass}</password>
  </DataBase>
</Config>
XML;
            file_put_contents($configDir . '/.htconfig.xml', trim($dbXml));

            //Escrever .htconfigEmail.xml
            $emailXml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE ConfigEmail SYSTEM ".htconfigEmail.dtd">
<ConfigEmail xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation=".htconfigEmail.xsd">
    <Account>
        <Server>{$smtpServer}</Server>
        <SSL>{$smtpSSL}</SSL>
        <Port>{$smtpPort}</Port>
        <Timeout>30</Timeout>
        <LoginName>{$smtpUser}</LoginName>
        <Password>{$smtpPass}</Password>
        <Email>{$smtpUser}</Email>
        <DisplayName>SMI - Trabalho Pratico</DisplayName>
    </Account>
</ConfigEmail>
XML;
            file_put_contents($configDir . '/.htconfigEmail.xml', trim($emailXml));

            $success = 'O CMS Modyssey foi instalado com sucesso!';
        } catch (Exception $e) {
            $error = 'Falha na Instalação: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador Modyssey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg1: #0b0c10;
            --bg2: #1f2833;
            --accent: #66fcf1;
            --accent-hover: #45f3e5;
            --text: #c5c6c7;
            --border: #45a29e;
        }

        body {
            background-color: var(--bg1);
            color: var(--text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .setup-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            max-width: 680px;
            width: 100%;
            padding: 40px;
        }

        .setup-title {
            color: #fff;
            font-weight: 700;
            text-align: center;
            margin-bottom: 8px;
        }

        .setup-title span {
            color: var(--accent);
        }

        .form-label {
            color: #fff;
            font-weight: 600;
        }

        .form-control {
            background: #151a21;
            border: 1px solid #2d3846;
            color: #fff;
        }

        .form-control:focus {
            background: #151a21;
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(102, 252, 241, 0.25);
            color: #fff;
        }

        .btn-primary {
            background-color: var(--accent);
            border: none;
            color: var(--bg1);
            font-weight: 700;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
            color: var(--bg1);
            transform: translateY(-2px);
        }

        .section-header {
            border-bottom: 1px solid #2d3846;
            padding-bottom: 8px;
            margin-bottom: 20px;
            color: var(--accent);
            font-weight: 700;
            margin-top: 24px;
        }
    </style>
</head>
<body>

<div class="setup-card">
    <h1 class="setup-title">Mod<span>yssey</span></h1>
    <h4 class="text-center text-muted mb-4">Assistente de Instalação do CMS</h4>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <strong>Erro:</strong> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success text-center" role="alert">
            <h4 class="alert-heading">Sucesso!</h4>
            <p><?= htmlspecialchars($success) ?></p>
            <hr>
            <a href="/Modyssey/public/login" class="btn btn-primary w-100">Ir para o Login</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <!-- Database Configuration -->
            <div class="section-header">1. Ligação à Base de Dados</div>
            <div class="row g-3">
                <div class="col-md-8">
                    <label for="db_host" class="form-label">Servidor Base de Dados (Host)</label>
                    <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                </div>
                <div class="col-md-4">
                    <label for="db_port" class="form-label">Porta</label>
                    <input type="text" class="form-control" id="db_port" name="db_port" value="3306" required>
                </div>
                <div class="col-md-6">
                    <label for="db_name" class="form-label">Nome da Base de Dados</label>
                    <input type="text" class="form-control" id="db_name" name="db_name" value="modyssey" required>
                </div>
                <div class="col-md-6">
                    <label for="db_user" class="form-label">Utilizador da BD</label>
                    <input type="text" class="form-control" id="db_user" name="db_user" value="root" required>
                </div>
                <div class="col-12">
                    <label for="db_pass" class="form-label">Palavra-passe da BD</label>
                    <input type="password" class="form-control" id="db_pass" name="db_pass" value="">
                </div>
            </div>

            <!-- Email Configuration -->
            <div class="section-header">2. Configuração de Notificações por Correio Eletrónico (SMTP)</div>
            <div class="row g-3">
                <div class="col-md-8">
                    <label for="smtp_server" class="form-label">Servidor SMTP</label>
                    <input type="text" class="form-control" id="smtp_server" name="smtp_server" value="smtp.gmail.com"
                           required>
                </div>
                <div class="col-md-4">
                    <label for="smtp_port" class="form-label">Porta SMTP</label>
                    <input type="number" class="form-control" id="smtp_port" name="smtp_port" value="465" required>
                </div>
                <div class="col-md-6">
                    <label for="smtp_ssl" class="form-label">Usar SSL / TLS</label>
                    <select class="form-select form-control" id="smtp_ssl" name="smtp_ssl">
                        <option value="true">Sim (SSL/TLS)</option>
                        <option value="false">Não</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="smtp_user" class="form-label">Utilizador SMTP (E-mail)</label>
                    <input type="email" class="form-control" id="smtp_user" name="smtp_user"
                           value="smitrabalhopratico@gmail.com" required>
                </div>
                <div class="col-12">
                    <label for="smtp_pass" class="form-label">Palavra-passe do E-mail (ou App Password)</label>
                    <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" value="" required>
                </div>
            </div>

            <!-- Administrator Setup -->
            <div class="section-header">3. Conta de Administrador Principal</div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="admin_user" class="form-label">Nome de Utilizador Admin</label>
                    <input type="text" class="form-control" id="admin_user" name="admin_user" value="admin" required>
                </div>
                <div class="col-md-6">
                    <label for="admin_email" class="form-label">E-mail do Admin</label>
                    <input type="email" class="form-control" id="admin_email" name="admin_email"
                           value="admin@modyssey.local" required>
                </div>
                <div class="col-12">
                    <label for="admin_pass" class="form-label">Palavra-passe do Admin</label>
                    <input type="password" class="form-control" id="admin_pass" name="admin_pass"
                           placeholder="Mínimo 8 caracteres" required minlength="8">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2">Instalar Modyssey</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
