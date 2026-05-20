<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Upload.php';
require_once __DIR__ . '/../models/Game.php';
require_once __DIR__ . '/../models/Mod.php';

class GameController
{
    private Game $gameModel;
    private Mod $modModel;
    const MAX_IMAGE_SIZE = 5 * 1024 * 1024;

    public function __construct()
    {
        $this->gameModel = new Game();
        $this->modModel = new Mod();
    }

    public function index(): void
    {
        $games = $this->gameModel->all();
        require __DIR__ . '/../views/games/index.php';
    }

    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $game = $this->gameModel->findById($id);

        if (!$game) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        $user = Auth::user();
        $mods = $this->modModel->allVisible($user['id'], $user['role']);
        $mods = array_filter($mods, fn($m) => (int)$m['game_id'] === $id);

        require_once __DIR__ . '/../models/Category.php';
        $categoryModel = new Category();
        $categories = $categoryModel->byGame($id);

        $selectedCategoryId = (int)($_GET['category_id'] ?? 0);
        if ($selectedCategoryId > 0) {
            $mods = array_filter($mods, function ($m) use ($selectedCategoryId) {
                $modCats = $this->modModel->getCategories((int)$m['id']);
                $modCatIds = array_map(fn($c) => (int)$c['id'], $modCats);
                return in_array($selectedCategoryId, $modCatIds, true);
            });
        }

        require __DIR__ . '/../views/games/show.php';
    }

    public function createForm(): void
    {
        Auth::require('sympathizer');
        require __DIR__ . '/../views/games/create.php';
    }

    public function store(): void
    {
        Auth::require('sympathizer');

        $name = trim($_POST['name'] ?? '');
        $rawgImageUrl = trim($_POST['rawg_image_url'] ?? '');

        if (!$name) {
            $error = 'O nome do jogo é obrigatório.';
            require __DIR__ . '/../views/games/create.php';
            return;
        }

        if (empty($_FILES['image']['name']) && !$rawgImageUrl) {
            $error = 'A imagem do jogo é obrigatória.';
            require __DIR__ . '/../views/games/create.php';
            return;
        }
        $imagePath = '';
        if (!empty($_FILES['image']['name'])) {
            try {
                $imagePath = Upload::image($_FILES['image'], 'games');
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
                require __DIR__ . '/../views/games/create.php';
                return;
            }
        } elseif ($rawgImageUrl) {
            $parsedUrl = parse_url($rawgImageUrl);
            $host = isset($parsedUrl['host']) ? strtolower($parsedUrl['host']) : '';
            $scheme = isset($parsedUrl['scheme']) ? strtolower($parsedUrl['scheme']) : '';
            if (($scheme !== 'http' && $scheme !== 'https') || $host !== 'media.rawg.io') {
                $error = 'Origem da imagem do RAWG inválida ou insegura.';
                require __DIR__ . '/../views/games/create.php';
                return;
            }
            try {
                // Definir User-Agent no cabeçalho do pedido, uma vez que a CDN do RAWG bloqueia pedidos anónimos
                $options = [
                    'http' => [
                        'method' => 'GET',
                        'header' => "User-Agent: Modyssey-App/1.0 (smitp; CMS Academic Project)\r\n"
                    ]
                ];
                $context = stream_context_create($options);
                $imageData = @file_get_contents($rawgImageUrl, false, $context);
                if ($imageData === false) {
                    throw new RuntimeException('Não foi possível transferir a imagem a partir do RAWG.');
                }
                // Limitar tamanho do download para evitar abusos (Max 5MB)
                if (strlen($imageData) > self::MAX_IMAGE_SIZE) {
                    throw new RuntimeException('A imagem selecionada é demasiado grande (Máx 5 MB).');
                }
                // Gerar nome de ficheiro seguro com extensão válida baseada no URL
                $pathExt = strtolower(pathinfo($parsedUrl['path'] ?? '', PATHINFO_EXTENSION));
                if (!in_array($pathExt, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    $pathExt = 'jpg'; // Extensão de recurso de segurança
                }
                $filename = bin2hex(random_bytes(16)) . '.' . $pathExt;
                $dir = __DIR__ . '/../public/uploads/games/';
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                if (file_put_contents($dir . $filename, $imageData) === false) {
                    throw new RuntimeException('Falha ao guardar a imagem no servidor.');
                }
                // Verificação de integridade: Validar o tipo MIME real do ficheiro descarregado
                $mimeType = mime_content_type($dir . $filename);
                if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                    unlink($dir . $filename); // Elimina imediatamente o ficheiro se for inválido/malicioso
                    throw new RuntimeException('O ficheiro descarregado não é uma imagem válida.');
                }
                $imagePath = BASE_URL . '/uploads/games/' . $filename;
            } catch (Exception $e) {
                $error = 'Erro na importação RAWG: ' . $e->getMessage();
                require __DIR__ . '/../views/games/create.php';
                return;
            }
        }

$this->gameModel->create($name, $imagePath, Auth::id());
header('Location: ' . BASE_URL . '/games?created=1');
exit;
}

public
function delete(): void
{
    Auth::require('sympathizer');

    $id = (int)($_GET['id'] ?? 0);
    $user = Auth::user();

    if (!$this->gameModel->canDelete($id, $user['id'], $user['role'])) {
        http_response_code(403);
        echo 'Acesso negado.';
        return;
    }

    $game = $this->gameModel->findById($id);

    if ($game) {
        Upload::delete($game['image_path']);
        $this->gameModel->delete($id);
    }

    header('Location: ' . BASE_URL . '/games');
    exit;
}
}
