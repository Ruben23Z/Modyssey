<?php

require_once __DIR__ . '/../core/Model.php';

class User extends Model
{
    public function findByEmail(string $email): array|false
    {
        return $this->fetchOne(
            'SELECT u.*, r.name AS role_name
               FROM users u
               JOIN roles r ON r.id = u.role_id
              WHERE u.email = ?',
            [$email]
        );
    }

    public function findById(int $id): array|false
    {
        return $this->fetchOne(
            'SELECT u.*, r.name AS role_name
               FROM users u
               JOIN roles r ON r.id = u.role_id
              WHERE u.id = ?',
            [$id]
        );
    }

    public function create(string $username, string $email, string $password): int
    {
        $this->execute(
            'INSERT INTO users (username, email, password, role_id)
             VALUES (?, ?, ?, (SELECT id FROM roles WHERE name = "user"))',
            [$username, $email, password_hash($password, PASSWORD_BCRYPT)]
        );

        return (int) $this->lastInsertId();
    }

    public function emailExists(string $email): bool
    {
        $row = $this->fetchOne('SELECT id FROM users WHERE email = ?', [$email]);
        return $row !== false;
    }

    public function usernameExists(string $username): bool
    {
        $row = $this->fetchOne('SELECT id FROM users WHERE username = ?', [$username]);
        return $row !== false;
    }

    public function all(): array
    {
        return $this->fetchAll(
            'SELECT u.id, u.username, u.email, r.name AS role_name, u.created_at
               FROM users u
               JOIN roles r ON r.id = u.role_id
           ORDER BY u.created_at DESC'
        );
    }

    public function updateRole(int $userId, int $roleId): bool
    {
        return $this->execute(
            'UPDATE users SET role_id = ? WHERE id = ?',
            [$roleId, $userId]
        );
    }
}
