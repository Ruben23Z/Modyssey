<?php

require_once __DIR__ . '/../core/Model.php';

class Notification extends Model
{
    public function create(int $userId, string $message): bool
    {
        return $this->execute(
            'INSERT INTO notification (user_id, message) VALUES (?, ?)',
            [$userId, $message]
        );
    }

    public function getForUser(int $userId): array
    {
        return $this->fetchAll(
            'SELECT id, message, is_read, created_at
               FROM notification
              WHERE user_id = ?
           ORDER BY created_at DESC',
            [$userId]
        );
    }

    public function getUnreadCount(int $userId): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS count FROM notification WHERE user_id = ? AND is_read = 0',
            [$userId]
        );
        return $row ? (int)$row['count'] : 0;
    }

    public function markAllAsRead(int $userId): bool
    {
        return $this->execute(
            'UPDATE notification SET is_read = 1 WHERE user_id = ? AND is_read = 0',
            [$userId]
        );
    }
}
