<?php

require_once __DIR__ . '/../core/Model.php';

class Game extends Model
{
    public function all(): array
    {
        return $this->fetchAll(
            'SELECT g.*, u.username AS added_by_username
               FROM games g
               JOIN users u ON u.id = g.added_by
           ORDER BY g.name ASC'
        );
    }

    public function findById(int $id): array|false
    {
        return $this->fetchOne('SELECT * FROM games WHERE id = ?', [$id]);
    }

    public function create(string $name, string $imagePath, int $addedBy): int
    {
        $this->execute(
            'INSERT INTO games (name, image_path, added_by) VALUES (?, ?, ?)',
            [$name, $imagePath, $addedBy]
        );

        return (int) $this->lastInsertId();
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM games WHERE id = ?', [$id]);
    }

    public function canDelete(int $gameId, int $userId, string $role): bool
    {
        if ($role === 'admin') {
            return true;
        }

        $game = $this->findById($gameId);
        return $game && (int) $game['added_by'] === $userId;
    }
}
