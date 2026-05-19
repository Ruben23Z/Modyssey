<?php

class Upload
{
    private const MAX_IMAGE_SIZE = 5 * 1024 * 1024;
    private const MAX_MOD_SIZE   = 500 * 1024 * 1024;

    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    private const ALLOWED_MOD_TYPES   = ['application/zip', 'application/x-zip-compressed'];

    private const BASE_PATH = __DIR__ . '/../public/uploads/';

    public static function image(array $file, string $subfolder): string
    {
        return self::save($file, $subfolder, self::ALLOWED_IMAGE_TYPES, self::MAX_IMAGE_SIZE);
    }

    public static function mod(array $file): string
    {
        return self::save($file, 'mods', self::ALLOWED_MOD_TYPES, self::MAX_MOD_SIZE);
    }

    private static function save(array $file, string $subfolder, array $allowedTypes, int $maxSize): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Erro no upload do ficheiro.');
        }

        if ($file['size'] > $maxSize) {
            throw new RuntimeException('Ficheiro demasiado grande.');
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowedTypes, true)) {
            throw new RuntimeException('Tipo de ficheiro não permitido.');
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
        $dir      = self::BASE_PATH . $subfolder . '/';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            throw new RuntimeException('Não foi possível guardar o ficheiro.');
        }

        return '/uploads/' . $subfolder . '/' . $filename;
    }

    public static function delete(string $path): void
    {
        $full = self::BASE_PATH . ltrim($path, '/uploads/');
        if (file_exists($full)) {
            unlink($full);
        }
    }
}
