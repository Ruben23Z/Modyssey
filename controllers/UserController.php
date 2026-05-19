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
            header('Location: /admin/users');
            exit;
        }

        $this->userModel->updateRole($userId, $roleId);
        header('Location: /admin/users');
        exit;
    }
}
