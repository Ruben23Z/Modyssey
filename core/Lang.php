<?php

class Lang
{
    private static array $translations = [];
    private static string $currentLang = 'pt';

    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if lang is provided via query param
        if (isset($_GET['lang'])) {
            $lang = strtolower($_GET['lang']);
            if (in_array($lang, ['pt', 'en'])) {
                $_SESSION['lang'] = $lang;
            }
        }

        // Set current language
        if (isset($_SESSION['lang'])) {
            self::$currentLang = $_SESSION['lang'];
        } else {
            self::$currentLang = 'pt'; // default to Portuguese
        }

        // Define translations inline
        self::$translations = [
            'pt' => [
                'nav_mods' => 'Mods',
                'nav_games' => 'Jogos',
                'nav_categories' => 'Categorias',
                'nav_subscriptions' => 'Subscrições',
                'nav_administration' => 'Administração',
                'search_placeholder' => 'Pesquisar...',
                'publish_mod' => 'Publicar Mod',
                'role_guest' => 'Convidado',
                'role_user' => 'Utilizador',
                'role_sympathizer' => 'Simpatizante',
                'role_admin' => 'Administrador',
                'nav_logout' => 'Sair',
                'nav_login' => 'Iniciar Sessão',
                'nav_register' => 'Registar',
                'sub_subscribe' => 'Subscrever',
                'sub_unsubscribe' => 'Cancelar Subscrição',
                'sub_page_title' => 'As Minhas Subscrições',
                'sub_page_subtitle' => 'Jogos que está a seguir e notificações recebidas.',
                'sub_no_subs' => 'Ainda não subscreveu nenhum jogo.',
                'sub_notifications_title' => 'Notificações',
                'sub_no_notifications' => 'Sem notificações de momento.',
                'sub_new' => 'Novo',
            ],
            'en' => [
                'nav_mods' => 'Mods',
                'nav_games' => 'Games',
                'nav_categories' => 'Categories',
                'nav_subscriptions' => 'Subscriptions',
                'nav_administration' => 'Administration',
                'search_placeholder' => 'Search...',
                'publish_mod' => 'Publish Mod',
                'role_guest' => 'Guest',
                'role_user' => 'User',
                'role_sympathizer' => 'Sympathizer',
                'role_admin' => 'Administrator',
                'nav_logout' => 'Logout',
                'nav_login' => 'Login',
                'nav_register' => 'Register',
                'sub_subscribe' => 'Subscribe',
                'sub_unsubscribe' => 'Unsubscribe',
                'sub_page_title' => 'My Subscriptions',
                'sub_page_subtitle' => 'Games you are following and received notifications.',
                'sub_no_subs' => 'You have not subscribed to any game yet.',
                'sub_notifications_title' => 'Notifications',
                'sub_no_notifications' => 'No notifications at the moment.',
                'sub_new' => 'New',
            ]
        ];
    }

    public static function getLang(): string
    {
        return self::$currentLang;
    }

    public static function t(string $key): string
    {
        return self::$translations[self::$currentLang][$key] ?? $key;
    }
}
