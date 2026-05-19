<?php

require_once __DIR__ . '/../core/Model.php';

class Mod extends Model
{
    public function allVisible(int $userId = null, string $role = 'guest'): array
    {
        if ($role === 'admin') {
            return $this->fetchAll(
                'SELECT m.*, g.name AS game_name, u.username AS uploader
                   FROM mods m
                   JOIN games g ON g.id = m.game_id
                   JOIN users u ON u.id = m.uploaded_by
               ORDER BY m.created_at DESC'
            );
        }

        if ($userId) {
            return $this->fetchAll(
                'SELECT m.*, g.name AS game_name, u.username AS uploader
                   FROM mods m
                   JOIN games g ON g.id = m.game_id
                   JOIN users u ON u.id = m.uploaded_by
                  WHERE m.visibility = "public" OR m.uploaded_by = ?
               ORDER BY m.created_at DESC',
                [$userId]
            );
        }

        return $this->fetchAll(
            'SELECT m.*, g.name AS game_name, u.username AS uploader
               FROM mods m
               JOIN games g ON g.id = m.game_id
               JOIN users u ON u.id = m.uploaded_by
              WHERE m.visibility = "public"
           ORDER BY m.created_at DESC'
        );
    }

    public function findById(int $id): array|false
    {
        return $this->fetchOne(
            'SELECT m.*, g.name AS game_name, u.username AS uploader
               FROM mods m
               JOIN games g ON g.id = m.game_id
               JOIN users u ON u.id = m.uploaded_by
              WHERE m.id = ?',
            [$id]
        );
    }

    public function isVisible(array $mod, int $userId = null, string $role = 'guest'): bool
    {
        if ($mod['visibility'] === 'public') {
            return true;
        }

        if ($role === 'admin') {
            return true;
        }

        return $userId !== null && (int) $mod['uploaded_by'] === $userId;
    }

    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO mods
             (title, description, cover_image_path, file_path, visibility, game_id, uploaded_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $data['title'],
                $data['description'],
                $data['cover_image_path'],
                $data['file_path'],
                $data['visibility'],
                $data['game_id'],
                $data['uploaded_by'],
            ]
        );

        return (int) $this->lastInsertId();
    }

    public function attachCategories(int $modId, array $categoryIds): void
    {
        $categoryIds = array_slice(array_unique($categoryIds), 0, 2);

        foreach ($categoryIds as $categoryId) {
            $this->execute(
                'INSERT IGNORE INTO mod_categories (mod_id, category_id) VALUES (?, ?)',
                [$modId, (int) $categoryId]
            );
        }
    }

    public function getCategories(int $modId): array
    {
        return $this->fetchAll(
            'SELECT c.*
               FROM categories c
               JOIN mod_categories mc ON mc.category_id = c.id
              WHERE mc.mod_id = ?',
            [$modId]
        );
    }

    public function getImages(int $modId): array
    {
        return $this->fetchAll(
            'SELECT * FROM mod_images WHERE mod_id = ? ORDER BY sort_order ASC',
            [$modId]
        );
    }

    public function addImage(int $modId, string $imagePath, int $sortOrder = 0): void
    {
        $this->execute(
            'INSERT INTO mod_images (mod_id, image_path, sort_order) VALUES (?, ?, ?)',
            [$modId, $imagePath, $sortOrder]
        );
    }

    public function incrementDownload(int $modId): void
    {
        $this->execute(
            'UPDATE mods SET download_count = download_count + 1 WHERE id = ?',
            [$modId]
        );
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM mods WHERE id = ?', [$id]);
    }

    public function canDelete(int $modId, int $userId, string $role): bool
    {
        if ($role === 'admin') {
            return true;
        }

        $mod = $this->findById($modId);
        return $mod && (int) $mod['uploaded_by'] === $userId;
    }
}
