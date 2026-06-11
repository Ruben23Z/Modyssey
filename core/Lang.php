<?php

class Lang
{
    private static ?array $translations = null;
    private static string $currentLang = 'pt';

    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if lang is provided via query param
        if (isset($_GET['lang'])) {
            $lang = strtolower($_GET['lang']);
            if (in_array($lang, ['pt', 'en'], true)) {
                $_SESSION['lang'] = $lang;

                // Persist preference for logged-in users (used e.g. for notification emails)
                if (isset($_SESSION['user_id'])) {
                    try {
                        require_once __DIR__ . '/Database.php';
                        $db = Database::getInstance();
                        $stmt = $db->prepare('UPDATE user SET lang = ? WHERE IDUser = ?');
                        $stmt->execute([$lang, $_SESSION['user_id']]);
                    } catch (Exception) {
                        // Coluna pode ainda não existir; ignorar silenciosamente
                    }
                }
            }
        }

        self::$currentLang = $_SESSION['lang'] ?? 'pt';

        $file = __DIR__ . '/../lang/' . self::$currentLang . '.php';
        if (file_exists($file)) {
            self::$translations = require $file;
        } else {
            self::$translations = [];
        }
    }

    public static function getLang(): string
    {
        if (self::$translations === null) {
            self::init();
        }
        return self::$currentLang;
    }

    public static function t(string $key, array $replacements = []): string
    {
        if (self::$translations === null) {
            self::init();
        }

        $translation = self::$translations[$key] ?? $key;

        foreach ($replacements as $placeholder => $value) {
            $translation = str_replace('{' . $placeholder . '}', $value, $translation);
        }

        return $translation;
    }

    /**
     * Traduz numa língua específica (independente da sessão).
     * Útil para emails/notificações enviados a outros utilizadores.
     */
    public static function tIn(string $lang, string $key, array $replacements = []): string
    {
        $lang = in_array($lang, ['pt', 'en'], true) ? $lang : 'pt';
        $file = __DIR__ . '/../lang/' . $lang . '.php';
        $translations = file_exists($file) ? require $file : [];

        $translation = $translations[$key] ?? $key;

        foreach ($replacements as $placeholder => $value) {
            $translation = str_replace('{' . $placeholder . '}', $value, $translation);
        }

        return $translation;
    }
}
