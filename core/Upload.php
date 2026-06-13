<?php

require_once __DIR__ . '/Lang.php';

class Upload
{
    private const MAX_IMAGE_SIZE = 5 * 1024 * 1024;
    private const MAX_MOD_SIZE   = 500 * 1024 * 1024;

    private const MAX_VIDEO_SIZE = 50 * 1024 * 1024; // 50 MB
    private const ALLOWED_VIDEO_TYPES = ['video/mp4', 'video/webm', 'video/ogg'];

    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    // Extensões bloqueadas por segurança (executáveis no servidor web)
    private const BLOCKED_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
        'cgi', 'pl', 'asp', 'aspx', 'jsp', 'htaccess', 'shtml',
    ];

    private const BASE_PATH = __DIR__ . '/../public/uploads/';

    public static function image(array $file, string $subfolder): string
    {
        return self::save($file, $subfolder, self::ALLOWED_IMAGE_TYPES, self::MAX_IMAGE_SIZE);
    }


    public static function video(array $file): string
    {
        return self::save($file, "videos", self::ALLOWED_VIDEO_TYPES, self::MAX_VIDEO_SIZE);

    }

    /**
     * Guarda o ficheiro de um mod, validando a extensão contra a lista
     * permitida pelo jogo (lista separada por vírgulas, ex: "zip,rar,pak").
     */
    public static function mod(array $file, ?string $allowedExtensions = null): string
    {
        $extensions = self::parseExtensions($allowedExtensions);

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException(Lang::t('upload_error'));
        }

        if ($file['size'] > self::MAX_MOD_SIZE) {
            throw new RuntimeException(Lang::t('upload_too_large'));
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($ext === '' || in_array($ext, self::BLOCKED_EXTENSIONS, true)) {
            throw new RuntimeException(Lang::t('upload_type_blocked'));
        }

        if (!in_array('*', $extensions, true) && !in_array($ext, $extensions, true)) {
            throw new RuntimeException(Lang::t('upload_type_not_allowed_game', ['formats' => '.' . implode(', .', $extensions)]));
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dir      = self::BASE_PATH . 'mods/';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            throw new RuntimeException(Lang::t('upload_save_failed'));
        }

        return BASE_URL . '/uploads/mods/' . $filename;
    }

    /**
     * Normaliza uma lista de extensões ("zip, .RAR,7z") para ['zip','rar','7z'].
     */
    public static function parseExtensions(?string $list): array
    {
        $extensions = array_values(array_filter(array_map(
            fn($e) => strtolower(trim($e, " .\t")),
            explode(',', $list ?: 'zip')
        )));

        return $extensions ?: ['zip'];
    }

    private static function save(array $file, string $subfolder, array $allowedTypes, int $maxSize): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException(Lang::t('upload_error'));
        }

        if ($file['size'] > $maxSize) {
            throw new RuntimeException(Lang::t('upload_too_large'));
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowedTypes, true)) {
            throw new RuntimeException(Lang::t('upload_type_not_allowed'));
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
        $dir      = self::BASE_PATH . $subfolder . '/';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            throw new RuntimeException(Lang::t('upload_save_failed'));
        }

        return BASE_URL . '/uploads/' . $subfolder . '/' . $filename;
    }

    public static function delete(?string $path): void
    {
        if (empty($path)) {
            return;
        }
        $relativePath = $path;
        if (defined('BASE_URL') && strpos($path, BASE_URL) === 0) {
            $relativePath = substr($path, strlen(BASE_URL));
        }
        $full = __DIR__ . '/../public' . $relativePath;
        if (file_exists($full)) {
            unlink($full);
        }
    }
}
