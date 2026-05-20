<?php

require_once __DIR__ . '/../core/Model.php';

class User extends Model
{
    public function findByEmail(string $email): array|false
    {
        return $this->fetchOne(
            'SELECT u.*, u.IDUser AS id, r.name AS role_name
               FROM user u
               JOIN role r ON r.IDRole = u.IDRole
              WHERE u.email = ?',
            [$email]
        );
    }

    public function findById(int $id): array|false
    {
        return $this->fetchOne(
            'SELECT u.*, u.IDUser AS id, r.name AS role_name
               FROM user u
               JOIN role r ON r.IDRole = u.IDRole
              WHERE u.IDUser = ?',
            [$id]
        );
    }

    public function create(string $username, string $email, string $password): int
    {
        $this->execute(
            'INSERT INTO user (username, email, password, IDRole)
             VALUES (?, ?, ?, (SELECT IDRole FROM role WHERE name = "user"))',
            [$username, $email, password_hash($password, PASSWORD_BCRYPT)]
        );

        return (int)$this->lastInsertId();
    }

    public function emailExists(string $email): bool
    {
        $row = $this->fetchOne('SELECT IDUser AS id FROM user WHERE email = ?', [$email]);
        return $row !== false;
    }

    public function usernameExists(string $username): bool
    {
        $row = $this->fetchOne('SELECT IDUser AS id FROM user WHERE username = ?', [$username]);
        return $row !== false;
    }

    public function all(): array
    {
        return $this->fetchAll(
            'SELECT u.IDUser AS id, u.username, u.email, r.name AS role_name, u.created_at
               FROM user u
               JOIN role r ON r.IDRole = u.IDRole
           ORDER BY u.created_at DESC'
        );
    }

    public function updateRole(int $userId, int $roleId): bool
    {
        return $this->execute(
            'UPDATE user SET IDRole = ? WHERE IDUser = ?',
            [$roleId, $userId]
        );
    }

    public function createWithToken(string $username, string $email, string $password, string $token): int
    {

        $this->execute(
            'INSERT INTO user (username, email, password, IDRole, active, activation_token)
         VALUES (?, ?, ?, (SELECT IDRole FROM role WHERE name = "user"), 0, ?)',
            [$username, $email, password_hash($password, PASSWORD_BCRYPT), $token]
        );
        return (int)$this->lastInsertId();
    }
    public function activateByToken(string $token): bool
    {
        return $this->execute(
            'UPDATE user SET active = 1, activation_token = NULL WHERE activation_token = ?',
            [$token]
        );
    }

}
