<?php

require_once __DIR__ . '/../core/Model.php';

class Stats extends Model
{
    public function topMods(): array
    {
        return $this->fetchAll(
            'SELECT title, download_count FROM `mod` WHERE visibility = "public" ORDER BY download_count DESC LIMIT 5'
        );
    }

    public function topGames(): array
    {
        return $this->fetchAll(
            'SELECT g.name, COUNT(m.IDMod) AS mod_count
               FROM game g
               JOIN `mod` m ON m.game_id = g.IDGame
           GROUP BY g.IDGame
           ORDER BY mod_count DESC
           LIMIT 5'
        );
    }

    public function topUsers(): array
    {
        return $this->fetchAll(
            'SELECT u.username, COUNT(m.IDMod) AS mod_count
               FROM user u
               JOIN `mod` m ON m.uploaded_by = u.IDUser
           GROUP BY u.IDUser
           ORDER BY mod_count DESC
           LIMIT 5'
        );
    }

    public function uploadsPerMonth(): array
    {
        return $this->fetchAll(
            'SELECT DATE_FORMAT(created_at, "%Y-%m") AS month, COUNT(*) AS total
               FROM `mod`
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
           GROUP BY month
           ORDER BY month ASC'
        );
    }
}
