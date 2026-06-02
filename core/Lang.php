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

        // Check if language switcher was toggled
        if (isset($_GET['lang'])) {
            $lang = $_GET['lang'] === 'en' ? 'en' : 'pt';
            $_SESSION['lang'] = $lang;
        }

        self::$currentLang = $_SESSION['lang'] ?? 'pt';

        $file = __DIR__ . '/../lang/' . self::$currentLang . '.php';
        if (file_exists($file)) {
            self::$translations = require $file;
        } else {
            self::$translations = [];
        }
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


    public static function getLang(): string
    {
        if (self::$translations === null) {
            self::init();
        }
        return self::$currentLang;
    }
}
