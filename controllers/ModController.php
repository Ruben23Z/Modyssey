<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Upload.php';
require_once __DIR__ . '/../models/Mod.php';
require_once __DIR__ . '/../models/Game.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../services/NotificationService.php';

class ModController
{
    private Mod $modModel;
    private Game $gameModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->modModel = new Mod();
        $this->gameModel = new Game();
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
        $id = (int)($_GET['id'] ?? 0);
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
        $images = $this->modModel->getImages($id);
        require __DIR__ . '/../views/mods/show.php';
    }

    public function createForm(): void
    {
        Auth::require('user');
        $games = $this->gameModel->all();
        $categories = $this->categoryModel->all();
        require __DIR__ . '/../views/mods/create.php';
    }

    public function store(): void
    {
        Auth::require('user');

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        // Safely determine visibility, default to 'public' if not provided
        $visibility = (!empty($_POST['visibility']) && $_POST['visibility'] === 'private') ? 'private' : 'public';
        $gameId = (int)($_POST['game_id'] ?? 0);
        $categoryIds = array_map('intval', (array)($_POST['category_ids'] ?? []));

        if (!$title || !$description || !$gameId) {
            $error = 'Preenche todos os campos obrigatórios.';
            $games = $this->gameModel->all();
            $categories = $this->categoryModel->all();
            require __DIR__ . '/../views/mods/create.php';
            return;
        }

        if (count($categoryIds) !== 2) {
            $error = 'Tens de selecionar exatamente 2 categorias.';
            $games = $this->gameModel->all();
            $categories = $this->categoryModel->all();
            require __DIR__ . '/../views/mods/create.php';
            return;
        }
        $videoPath = null;
        try {
            $coverPath = Upload::image($_FILES['cover_image'], 'covers');
            $filePath = Upload::mod($_FILES['mod_file']);
            if (!empty($_FILES['demo_video']['name'])) {
                $videoPath = Upload::video($_FILES['demo_video']);
            }
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
            $games = $this->gameModel->all();
            $categories = $this->categoryModel->all();
            require __DIR__ . '/../views/mods/create.php';
            return;
        }


        $modId = $this->modModel->create([
            'title' => $title,
            'description' => $description,
            'cover_image_path' => $coverPath,
            'file_path' => $filePath,
            'video_path' => $videoPath,
            'visibility' => $visibility,
            'game_id' => $gameId,
            'uploaded_by' => Auth::id(),
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
                    'name' => $name,
                    'type' => $extraImages['type'][$index],
                    'tmp_name' => $extraImages['tmp_name'][$index],
                    'error' => $extraImages['error'][$index],
                    'size' => $extraImages['size'][$index],
                ];
                try {
                    $imagePath = Upload::image($singleFile, 'mods');
                    $this->modModel->addImage($modId, $imagePath, $index);
                } catch (RuntimeException) {
                    continue;
                }
            }
        }

        // Notify subscribers of the new mod
        if ($visibility === 'public') {
            NotificationService::notifySubscribers($modId);
        }

        header('Location: ' . BASE_URL . '/mods?created=1');
        exit;
    }

    public function download(): void
    {
        $id = (int)($_GET['id'] ?? 0);
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
        $rawgImageUrl = trim($_POST['rawg_image_url'] ?? '');

        if ($rawgImageUrl) {
            // Validação de Segurança contra SSRF
            if (strpos($rawgImageUrl, 'https://media.rawg.io/') !== 0) {
                throw new RuntimeException("Origem da imagem inválida.");
            }

            // Determinar extensão do ficheiro
            $ext = pathinfo(parse_url($rawgImageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filename = bin2hex(random_bytes(16)) . '.' . $ext;

            $dir = __DIR__ . '/../public/uploads/games/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Download com contexto de User-Agent simulado
            $context = stream_context_create([
                'http' => ['header' => "User-Agent: ModysseyUniversityProject/1.0\r\n"]
            ]);
            $imgData = @file_get_contents($rawgImageUrl, false, $context);

            if ($imgData !== false) {
                file_put_contents($dir . $filename, $imgData);
                $imagePath = BASE_URL . '/uploads/games/' . $filename;
            } else {
                throw new RuntimeException("Falha ao descarregar a imagem da RAWG.");
            }
        } else {
            // Upload clássico
            $imagePath = Upload::image($_FILES['image'], 'games');
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

        $id = (int)($_GET['id'] ?? 0);
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
        Upload::delete($mod['video_path']);

        foreach ($this->modModel->getImages($id) as $image) {
            Upload::delete($image['image_path']);
        }

        $this->modModel->delete($id);
        header('Location: ' . BASE_URL . '/mods');
        exit;
    }

    public function toggleVisibility(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = (int) ($_GET['id'] ?? 0);
        $mod = $this->modModel->findById($id);

        if (!$mod) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Mod não encontrado.']);
            exit;
        }

        if (!Auth::isOwnerOrAdmin((int)$mod['uploaded_by'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $visibility = trim($input['visibility'] ?? '');

        if ($visibility !== 'public' && $visibility !== 'private') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Visibilidade inválida.']);
            exit;
        }

        $success = $this->modModel->updateVisibility($id, $visibility);
        if ($success) {
            echo json_encode([
                'success' => true,
                'visibility' => $visibility,
                'label' => $visibility === 'private' ? 'Privado' : 'Público'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Falha ao atualizar a visibilidade.']);
        }
        exit;
    }
}
