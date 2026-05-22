<?php

require_once __DIR__ . '/../core/Model.php';

class Game extends Model
{
    public function all(): array
    {
        return $this->fetchAll(
            'SELECT g.*, g.IDGame AS id, u.username AS added_by_username
               FROM game g
               JOIN user u ON u.IDUser = g.added_by
           ORDER BY g.name ASC'
        );
    }

    public function findById(int $id): array|false
    {
        return $this->fetchOne('SELECT g.*, g.IDGame AS id FROM game g WHERE g.IDGame = ?', [$id]);
    }

    public function create(string $name, string $imagePath, int $addedBy): int
    {
        $this->execute(
            'INSERT INTO game (name, image_path, added_by) VALUES (?, ?, ?)',
            [$name, $imagePath, $addedBy]
        );

        return (int) $this->lastInsertId();
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM game WHERE IDGame = ?', [$id]);
    }

    public function canDelete(int $gameId, int $userId, string $role): bool
    {
        if ($role === 'admin') {
            return true;
        }

        $game = $this->findById($gameId);
        return $game && (int) $game['added_by'] === $userId;
    }

    public function search(string $query): array
    {
        return $this->fetchAll(
            'SELECT g.*, g.IDGame AS id FROM game g WHERE g.name LIKE ? ORDER BY g.name ASC',
            ['%' . $query . '%']
        );
    }
}
