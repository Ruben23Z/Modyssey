<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/Game.php';
require_once __DIR__ . '/../models/Category.php';

class SubscriptionController
{
    private Game $gameModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->gameModel = new Game();
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        Auth::require('user');
        $userId = Auth::id();

        $games = $this->gameModel->all();
        $categories = $this->categoryModel->all();

        $db = Database::getInstance();
        
        $gameSubStmt = $db->prepare('SELECT game_id FROM user_subscription WHERE user_id = ? AND game_id IS NOT NULL');
        $gameSubStmt->execute([$userId]);
        $subscribedGames = $gameSubStmt->fetchAll(PDO::FETCH_COLUMN);

        $catSubStmt = $db->prepare('SELECT category_id FROM user_subscription WHERE user_id = ? AND category_id IS NOT NULL');
        $catSubStmt->execute([$userId]);
        $subscribedCategories = $catSubStmt->fetchAll(PDO::FETCH_COLUMN);

        require __DIR__ . '/../views/subscriptions/index.php';
    }

    public function toggle(): void
    {
        Auth::require('user');
        $userId = Auth::id();

        // Read dynamic JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $type = $input['type'] ?? '';
        $id = (int)($input['id'] ?? 0);

        if (!in_array($type, ['game', 'category']) || !$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Pedido inválido']);
            exit;
        }

        $db = Database::getInstance();
        header('Content-Type: application/json');

        try {
            if ($type === 'game') {
                $stmt = $db->prepare('SELECT id FROM user_subscription WHERE user_id = ? AND game_id = ?');
                $stmt->execute([$userId, $id]);
                $exists = $stmt->fetch();

                if ($exists) {
                    $delStmt = $db->prepare('DELETE FROM user_subscription WHERE user_id = ? AND game_id = ?');
                    $delStmt->execute([$userId, $id]);
                    echo json_encode(['success' => true, 'subscribed' => false]);
                } else {
                    $insStmt = $db->prepare('INSERT INTO user_subscription (user_id, game_id) VALUES (?, ?)');
                    $insStmt->execute([$userId, $id]);
                    echo json_encode(['success' => true, 'subscribed' => true]);
                }
            } else {
                $stmt = $db->prepare('SELECT id FROM user_subscription WHERE user_id = ? AND category_id = ?');
                $stmt->execute([$userId, $id]);
                $exists = $stmt->fetch();

                if ($exists) {
                    $delStmt = $db->prepare('DELETE FROM user_subscription WHERE user_id = ? AND category_id = ?');
                    $delStmt->execute([$userId, $id]);
                    echo json_encode(['success' => true, 'subscribed' => false]);
                } else {
                    $insStmt = $db->prepare('INSERT INTO user_subscription (user_id, category_id) VALUES (?, ?)');
                    $insStmt->execute([$userId, $id]);
                    echo json_encode(['success' => true, 'subscribed' => true]);
                }
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
