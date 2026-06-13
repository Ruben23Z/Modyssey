<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/User.php';

class UserController
{
    private User $userModel;

    public function __construct()
    {
        Auth::require('admin');
        $this->userModel = new User();
    }

    public function index(): void
    {
        $users = $this->userModel->all();
        require __DIR__ . '/../views/admin/users.php';
    }

    public function updateRole(): void
    {
        $userId = (int)($_POST['user_id'] ?? 0);
        $roleId = (int)($_POST['role_id'] ?? 0);

        if (!$userId || !$roleId) {
            header('Location: ' . BASE_URL . '/admin/users');
            exit;
        }

        $this->userModel->updateRole($userId, $roleId);
        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }

    public function updateRoleAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $userId = (int)($input['user_id'] ?? 0);
        $roleId = (int)($input['role_id'] ?? 0);

        if (!$userId || !$roleId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Parâmetros inválidos.']);
            exit;
        }

        $success = $this->userModel->updateRole($userId, $roleId);
        if ($success) {
            $roleName = match ($roleId) {
                1 => 'guest',
                2 => 'user',
                3 => 'sympathizer',
                4 => 'admin',
                default => 'guest'
            };
            $label = match ($roleId) {
                1 => 'Convidado',
                2 => 'Utilizador',
                3 => 'Simpatizante',
                4 => 'Admin',
                default => 'Convidado'
            };
            echo json_encode([
                'success' => true,
                'role' => $roleName,
                'label' => $label
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Falha ao atualizar o cargo.']);
        }
        exit;
    }

    public function settingsForm(): void
    {
        $dbFile = __DIR__ . '/../config/configuracoes/.htconfig.xml';
        $emailFile = __DIR__ . '/../config/configuracoes/.htconfigEmail.xml';

        $db = null;
        if (file_exists($dbFile)) {
            $dbXml = simplexml_load_file($dbFile);
            if ($dbXml) {
                $db = $dbXml->DataBase;
            }
        }

        $email = null;
        if (file_exists($emailFile)) {
            $emailXml = simplexml_load_file($emailFile);
            if ($emailXml) {
                $email = $emailXml->Account;
            }
        }

        require __DIR__ . '/../views/admin/settings.php';
    }

    public function updateSettings(): void
    {
        $dbHost = trim($_POST['db_host'] ?? '');
        $dbPort = trim($_POST['db_port'] ?? '3306');
        $dbName = trim($_POST['db_name'] ?? '');
        $dbUser = trim($_POST['db_user'] ?? '');
        $dbPass = trim($_POST['db_pass'] ?? '');

        $smtpServer = trim($_POST['smtp_server'] ?? '');
        $smtpPort = trim($_POST['smtp_port'] ?? '465');
        $smtpSSL = ($_POST['smtp_ssl'] ?? 'true') === 'true' ? 'TRUE' : 'FALSE';
        $smtpUser = trim($_POST['smtp_user'] ?? '');
        $smtpPass = trim($_POST['smtp_pass'] ?? '');

        $error = null;

        if (!$dbHost || !$dbPort || !$dbName || !$dbUser || !$smtpServer || !$smtpPort || !$smtpUser) {
            $error = 'Preenche todos os campos obrigatórios.';
        } elseif (!is_numeric($dbPort) || (int)$dbPort < 1 || (int)$dbPort > 65535) {
            $error = 'A porta da base de dados deve ser um número entre 1 e 65535.';
        } elseif (!is_numeric($smtpPort) || (int)$smtpPort < 1 || (int)$smtpPort > 65535) {
            $error = 'A porta SMTP deve ser um número entre 1 e 65535.';
        } elseif (!filter_var($smtpUser, FILTER_VALIDATE_EMAIL)) {
            $error = 'O utilizador SMTP deve ser um e-mail válido.';
        }

        if ($error !== null) {
            $db = (object)[
                'host' => $dbHost,
                'port' => $dbPort,
                'db' => $dbName,
                'username' => $dbUser,
                'password' => $dbPass
            ];
            $email = (object)[
                'Server' => $smtpServer,
                'Port' => $smtpPort,
                'SSL' => $smtpSSL === 'TRUE' ? 'TRUE' : 'FALSE',
                'LoginName' => $smtpUser,
                'Password' => $smtpPass
            ];
            require __DIR__ . '/../views/admin/settings.php';
            return;
        }

        $configDir = __DIR__ . '/../config/configuracoes';

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

        $_SESSION['message'] = "Configurações atualizadas com sucesso!";
        $_SESSION['toastClass'] = "bg-success";

        header('Location: ' . BASE_URL . '/admin/settings');
        exit;
    }
}
