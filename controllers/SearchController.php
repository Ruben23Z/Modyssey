<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Mod.php';
require_once __DIR__ . '/../models/Game.php';

class SearchController
{
    private Mod $modModel;
    private Game $gameModel;

    public function __construct()
    {
        $this->modModel = new Mod();
        $this->gameModel = new Game();
    }

    public function search(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $query = trim($_GET['q'] ?? '');
        if ($query === '') {
            echo json_encode(['mods' => [], 'games' => []]);
            exit;
        }

        $user = Auth::user();
        $userId = $user ? (int) $user['id'] : null;
        $role = $user ? $user['role'] : 'guest';

        $mods = $this->modModel->search($query, $userId, $role);
        $games = $this->gameModel->search($query);

        $mappedMods = array_map(function ($mod) {
            return [
                'id' => $mod['id'],
                'title' => $mod['title'],
                'cover_image_path' => $mod['cover_image_path'],
                'game_name' => $mod['game_name'],
                'uploader' => $mod['uploader'],
            ];
        }, $mods);

        $mappedGames = array_map(function ($game) {
            return [
                'id' => $game['id'],
                'name' => $game['name'],
                'image_path' => $game['image_path'],
            ];
        }, $games);

        echo json_encode([
            'mods' => $mappedMods,
            'games' => $mappedGames
        ]);
        exit;
    }
}
