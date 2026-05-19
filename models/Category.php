<?php

require_once __DIR__ . '/../core/Model.php';

class Category extends Model
{
    public function all(): array
    {
        return $this->fetchAll(
            'SELECT c.*, u.username AS added_by_username
               FROM categories c
               JOIN users u ON u.id = c.added_by
           ORDER BY c.type ASC, c.name ASC'
        );
    }

    public function findById(int $id): array|false
    {
        return $this->fetchOne(
            'SELECT * FROM categories WHERE id = ?',
            [$id]
        );
    }

    public function create(string $name, string $type, int $addedBy): int
    {
        $this->execute(
            'INSERT INTO categories (name, type, added_by) VALUES (?, ?, ?)',
            [$name, $type, $addedBy]
        );

        return (int) $this->lastInsertId();
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM categories WHERE id = ?', [$id]);
    }

    public function canDelete(int $categoryId, int $userId, string $role): bool
    {
        if ($role === 'admin') {
            return true;
        }

        $category = $this->findById($categoryId);
        return $category && (int) $category['added_by'] === $userId;
    }
}
