<?php
require_once __DIR__ . '/../core/Model.php';

class ModVersion extends Model
{
    public function findByModId(int $modId): array
    {
        return $this->fetchAll(
            'SELECT * FROM mod_version WHERE mod_id = ? ORDER BY created_at DESC',
            [$modId]
        );
    }

    public function latestByModId(int $modId): array|false
    {
        return $this->fetchOne(
            'SELECT * FROM mod_version WHERE mod_id = ? ORDER BY created_at DESC LIMIT 1',
            [$modId]
        );
    }

    public function create(int $modId, string $version, string $filePath, string $changelog): int
    {
        $this->execute(
            'INSERT INTO mod_version (mod_id, version, file_path, changelog) VALUES (?, ?, ?, ?)',
            [$modId, $version, $filePath, $changelog]
        );
        return (int)$this->lastInsertId();
    }
}
?>
