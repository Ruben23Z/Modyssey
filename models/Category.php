<?php

require_once __DIR__ . '/../core/Model.php';

class Category extends Model
{
    public function all(): array
    {
        return $this->fetchAll(
            'SELECT c.*, c.IDCategory AS id, u.username AS added_by_username
               FROM category c
               JOIN user u ON u.IDUser = c.added_by
           ORDER BY c.type ASC, c.name ASC'
        );
    }

    public function findById(int $id): array|false
    {
        return $this->fetchOne(
            'SELECT c.*, c.IDCategory AS id FROM category c WHERE c.IDCategory = ?',
            [$id]
        );
    }

    public function create(string $name, string $type, int $addedBy): int
    {
        $this->execute(
            'INSERT INTO category (name, type, added_by) VALUES (?, ?, ?)',
            [$name, $type, $addedBy]
        );

        return (int) $this->lastInsertId();
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM category WHERE IDCategory = ?', [$id]);
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
