<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Upload.php';
require_once __DIR__ . '/../models/Game.php';
require_once __DIR__ . '/../models/Mod.php';

class GameController
{
    private Game $gameModel;
    private Mod  $modModel;

    public function __construct()
    {
        $this->gameModel = new Game();
        $this->modModel  = new Mod();
    }

    public function index(): void
    {
        $games = $this->gameModel->all();
        require __DIR__ . '/../views/games/index.php';
    }

    public function show(): void
    {
        $id   = (int) ($_GET['id'] ?? 0);
        $game = $this->gameModel->findById($id);

        if (!$game) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        $user = Auth::user();
        $mods = $this->modModel->allVisible($user['id'], $user['role']);
        $mods = array_filter($mods, fn($m) => (int) $m['game_id'] === $id);

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

        if (!$name) {
            $error = 'O nome do jogo é obrigatório.';
            require __DIR__ . '/../views/games/create.php';
            return;
        }

        if (empty($_FILES['image']['name'])) {
            $error = 'A imagem do jogo é obrigatória.';
            require __DIR__ . '/../views/games/create.php';
            return;
        }

        try {
            $imagePath = Upload::image($_FILES['image'], 'games');
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
            require __DIR__ . '/../views/games/create.php';
            return;
        }

        $this->gameModel->create($name, $imagePath, Auth::id());
        header('Location: ' . BASE_URL . '/games?created=1');
        exit;
    }

    public function delete(): void
    {
        Auth::require('sympathizer');

        $id   = (int) ($_GET['id'] ?? 0);
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
