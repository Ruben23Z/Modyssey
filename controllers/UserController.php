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
        $userId = (int) ($_POST['user_id'] ?? 0);
        $roleId = (int) ($_POST['role_id'] ?? 0);

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
        $userId = (int) ($input['user_id'] ?? 0);
        $roleId = (int) ($input['role_id'] ?? 0);

        if (!$userId || !$roleId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Parâmetros inválidos.']);
            exit;
        }

        $success = $this->userModel->updateRole($userId, $roleId);
        if ($success) {
            $roleName = match($roleId) {
                1 => 'guest',
                2 => 'user',
                3 => 'sympathizer',
                4 => 'admin',
                default => 'guest'
            };
            $label = match($roleId) {
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
}
