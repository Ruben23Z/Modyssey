<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Category.php';

class CategoryController
{
    private Category $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        Auth::require('sympathizer');
        $categories = $this->categoryModel->all();
        require __DIR__ . '/../views/categories/index.php';
    }

    public function createForm(): void
    {
        Auth::require('sympathizer');
        require __DIR__ . '/../views/categories/create.php';
    }

    public function store(): void
    {
        Auth::require('sympathizer');

        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['type'] ?? '');

        if (!$name || !$type) {
            $error      = 'Preenche todos os campos.';
            require __DIR__ . '/../views/categories/create.php';
            return;
        }

        $this->categoryModel->create($name, $type, Auth::id());
        header('Location: /categories?created=1');
        exit;
    }

    public function delete(): void
    {
        Auth::require('sympathizer');

        $id   = (int) ($_GET['id'] ?? 0);
        $user = Auth::user();

        if (!$this->categoryModel->canDelete($id, $user['id'], $user['role'])) {
            http_response_code(403);
            echo 'Acesso negado.';
            return;
        }

        $this->categoryModel->delete($id);
        header('Location: /categories');
        exit;
    }
}
