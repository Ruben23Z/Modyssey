<?php

require_once __DIR__ . '/../core/Model.php';

class Subscription extends Model
{
    public function subscribe(int $userId, int $gameId): bool
    {
        if ($this->isSubscribed($userId, $gameId)) {
            return true;
        }
        return $this->execute(
            'INSERT INTO game_subscription (user_id, game_id) VALUES (?, ?)',
            [$userId, $gameId]
        );
    }

    public function unsubscribe(int $userId, int $gameId): bool
    {
        return $this->execute(
            'DELETE FROM game_subscription WHERE user_id = ? AND game_id = ?',
            [$userId, $gameId]
        );
    }

    public function isSubscribed(int $userId, int $gameId): bool
    {
        $row = $this->fetchOne(
            'SELECT user_id FROM game_subscription WHERE user_id = ? AND game_id = ?',
            [$userId, $gameId]
        );
        return $row !== false;
    }

    public function getSubscribedGames(int $userId): array
    {
        return $this->fetchAll(
            'SELECT g.IDGame AS id, g.name, g.image_path, g.added_by
               FROM game g
               JOIN game_subscription gs ON gs.game_id = g.IDGame
              WHERE gs.user_id = ?
           ORDER BY g.name ASC',
            [$userId]
        );
    }

    public function getSubscribersForGame(int $gameId): array
    {
        return $this->fetchAll(
            'SELECT u.IDUser AS id, u.username, u.email
               FROM user u
               JOIN game_subscription gs ON gs.user_id = u.IDUser
              WHERE gs.game_id = ?',
            [$gameId]
        );
    }
}
