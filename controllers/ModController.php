<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Upload.php';
require_once __DIR__ . '/../models/Mod.php';
require_once __DIR__ . '/../models/Game.php';
require_once __DIR__ . '/../models/Category.php';

class ModController
{
    private Mod      $modModel;
    private Game     $gameModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->modModel      = new Mod();
        $this->gameModel     = new Game();
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        $user = Auth::user();
        $mods = $this->modModel->allVisible($user['id'], $user['role']);
        require __DIR__ . '/../views/mods/index.php';
    }

    public function show(): void
    {
        $id  = (int) ($_GET['id'] ?? 0);
        $mod = $this->modModel->findById($id);

        if (!$mod) {
            http_response_code(404);
            echo '404 Mod não encontrado.';
            return;
        }

        $user = Auth::user();

        if (!$this->modModel->isVisible($mod, $user['id'], $user['role'])) {
            http_response_code(403);
            echo 'Acesso negado.';
            return;
        }

        $categories = $this->modModel->getCategories($id);
        $images     = $this->modModel->getImages($id);
        require __DIR__ . '/../views/mods/show.php';
    }

    public function createForm(): void
    {
        Auth::require('user');
        $games      = $this->gameModel->all();
        $categories = $this->categoryModel->all();
        require __DIR__ . '/../views/mods/create.php';
    }

    public function store(): void
    {
        Auth::require('user');

        $title       = trim($_POST['title']       ?? '');
        $description = trim($_POST['description'] ?? '');
        $visibility  = $_POST['visibility'] === 'private' ? 'private' : 'public';
        $gameId      = (int) ($_POST['game_id']   ?? 0);
        $categoryIds = array_map('intval', (array) ($_POST['category_ids'] ?? []));

        if (!$title || !$description || !$gameId) {
            $error      = 'Preenche todos os campos obrigatórios.';
            $games      = $this->gameModel->all();
            $categories = $this->categoryModel->all();
            require __DIR__ . '/../views/mods/create.php';
            return;
        }

        if (count($categoryIds) !== 2) {
            $error      = 'Tens de selecionar exatamente 2 categorias.';
            $games      = $this->gameModel->all();
            $categories = $this->categoryModel->all();
            require __DIR__ . '/../views/mods/create.php';
            return;
        }

        try {
            $coverPath = Upload::image($_FILES['cover_image'], 'covers');
            $filePath  = Upload::mod($_FILES['mod_file']);
        } catch (RuntimeException $e) {
            $error      = $e->getMessage();
            $games      = $this->gameModel->all();
            $categories = $this->categoryModel->all();
            require __DIR__ . '/../views/mods/create.php';
            return;
        }

        $modId = $this->modModel->create([
            'title'            => $title,
            'description'      => $description,
            'cover_image_path' => $coverPath,
            'file_path'        => $filePath,
            'visibility'       => $visibility,
            'game_id'          => $gameId,
            'uploaded_by'      => Auth::id(),
        ]);

        if ($categoryIds) {
            $this->modModel->attachCategories($modId, $categoryIds);
        }

        $extraImages = $_FILES['extra_images'] ?? [];
        if (!empty($extraImages['name'][0])) {
            foreach ($extraImages['name'] as $index => $name) {
                if ($extraImages['error'][$index] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $singleFile = [
                    'name'     => $name,
                    'type'     => $extraImages['type'][$index],
                    'tmp_name' => $extraImages['tmp_name'][$index],
                    'error'    => $extraImages['error'][$index],
                    'size'     => $extraImages['size'][$index],
                ];
                try {
                    $imagePath = Upload::image($singleFile, 'mods');
                    $this->modModel->addImage($modId, $imagePath, $index);
                } catch (RuntimeException) {
                    continue;
                }
            }
        }

        header('Location: ' . BASE_URL . '/mods?created=1');
        exit;
    }

    public function download(): void
    {
        $id  = (int) ($_GET['id'] ?? 0);
        $mod = $this->modModel->findById($id);

        if (!$mod) {
            http_response_code(404);
            echo 'Mod não encontrado.';
            return;
        }

        $user = Auth::user();

        if (!$this->modModel->isVisible($mod, $user['id'], $user['role'])) {
            http_response_code(403);
            echo 'Acesso negado.';
            return;
        }

        $this->modModel->incrementDownload($id);

        $relativePath = $mod['file_path'];
        if (defined('BASE_URL') && strpos($relativePath, BASE_URL) === 0) {
            $relativePath = substr($relativePath, strlen(BASE_URL));
        }
        $fullPath = __DIR__ . '/../public' . $relativePath;

        if (!file_exists($fullPath)) {
            http_response_code(404);
            echo 'Ficheiro não encontrado no servidor.';
            return;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    public function delete(): void
    {
        Auth::require('user');

        $id  = (int) ($_GET['id'] ?? 0);
        $mod = $this->modModel->findById($id);

        if (!$mod) {
            header('Location: ' . BASE_URL . '/mods');
            exit;
        }

        $user = Auth::user();

        if (!$this->modModel->canDelete($id, $user['id'], $user['role'])) {
            http_response_code(403);
            echo 'Acesso negado.';
            return;
        }

        Upload::delete($mod['cover_image_path']);
        Upload::delete($mod['file_path']);

        foreach ($this->modModel->getImages($id) as $image) {
            Upload::delete($image['image_path']);
        }

        $this->modModel->delete($id);
        header('Location: ' . BASE_URL . '/mods');
        exit;
    }
}
