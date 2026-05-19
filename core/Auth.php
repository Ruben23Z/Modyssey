<?php

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(array $user): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role_name'];
    }

    public static function logout(): void
    {
        self::start();
        session_unset();
        session_destroy();
    }

    public static function isLoggedIn(): bool
    {
        self::start();
        return isset($_SESSION['user_id']);
    }

    public static function user(): array
    {
        self::start();
        return [
            'id'       => $_SESSION['user_id']   ?? null,
            'username' => $_SESSION['username']  ?? null,
            'role'     => $_SESSION['role']      ?? 'guest',
        ];
    }

    public static function role(): string
    {
        return self::user()['role'] ?? 'guest';
    }

    public static function id(): ?int
    {
        return self::user()['id'];
    }

    public static function can(string $minimumRole): bool
    {
        $hierarchy = ['guest' => 0, 'user' => 1, 'sympathizer' => 2, 'admin' => 3];
        $current   = $hierarchy[self::role()]       ?? 0;
        $required  = $hierarchy[$minimumRole]       ?? 99;
        return $current >= $required;
    }

    public static function require(string $minimumRole): void
    {
        if (!self::can($minimumRole)) {
            http_response_code(403);
            header('Location: /login');
            exit;
        }
    }

    public static function isOwnerOrAdmin(int $resourceOwnerId): bool
    {
        if (!self::isLoggedIn()) {
            return false;
        }
        return self::role() === 'admin' || self::id() === $resourceOwnerId;
    }
}
