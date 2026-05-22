<?php

require_once __DIR__ . '/../models/Mod.php';
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../Lib/lib-mail-v2.php';

class NotificationService
{
    public static function notifySubscribers(int $modId): void
    {
        $modModel = new Mod();
        $mod = $modModel->findById($modId);
        if (!$mod) {
            return;
        }

        $gameId = (int)$mod['game_id'];
        $gameName = $mod['game_name'];
        $modTitle = $mod['title'];

        // 1. Obter todos os subscritores deste jogo
        $subModel = new Subscription();
        $subscribers = $subModel->getSubscribersForGame($gameId);

        if (empty($subscribers)) {
            return;
        }

        // 2. Preparar a mensagem de notificação na aplicação
        $notifMessage = "Novo mod '{$modTitle}' publicado para o jogo '{$gameName}'!";

        // 3. Ler as configurações de SMTP
        $configEmailFile = __DIR__ . '/../config/configuracoes/.htconfigEmail.xml';
        $emailConfigured = false;

        if (file_exists($configEmailFile)) {
            $xmlEmail = @simplexml_load_file($configEmailFile);
            if ($xmlEmail !== false && isset($xmlEmail->Account[0])) {
                $emailAccount = $xmlEmail->Account[0];
                $smtpServer = strval($emailAccount->Server);
                $useSSL = strval($emailAccount->SSL) === "TRUE" ? 1 : 0;
                $port = intval($emailAccount->Port);
                $timeout = intval($emailAccount->Timeout) ?: 30;
                $loginName = strval($emailAccount->LoginName);
                $passwordEmail = strval($emailAccount->Password);
                $fromEmail = strval($emailAccount->Email);
                $fromName = strval($emailAccount->DisplayName);
                $emailConfigured = true;
            }
        }

        // 4. Construir as ligações
        $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $serverPort = $_SERVER['SERVER_PORT'] ?? '80';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $portString = ($serverPort !== '80' && $serverPort !== '443') ? ':' . $serverPort : '';

        $baseUrl = defined('BASE_URL') ? BASE_URL : '/Modyssey/public';
        $modLink = $protocol . '://' . $serverName . $portString . $baseUrl . '/mods/' . $modId;

        // 5. Notificar cada subscritor
        $notifModel = new Notification();
        foreach ($subscribers as $subscriber) {
            // A. Criar notificação na aplicação
            $notifModel->create((int)$subscriber['id'], $notifMessage);

            // B. Enviar notificação por e-mail
            if ($emailConfigured) {
                $subject = "Novo Mod Disponível: {$modTitle}";
                $body = "Olá {$subscriber['username']},\n\nUm novo mod com o título \"{$modTitle}\" foi publicado para o jogo \"{$gameName}\" que você subscreveu.\n\nLink para o mod: {$modLink}\n\nDescrição:\n{$mod['description']}\n\nCumprimentos,\nEquipa Modyssey";

                // try-catch para garantir que uma falha no envio de e-mail não bloqueia as notificações dos restantes subscritores
                try {
                    @sendAuthEmail(
                        $smtpServer,
                        $useSSL,
                        $port,
                        $timeout,
                        $loginName,
                        $passwordEmail,
                        $fromEmail,
                        $fromName,
                        $subscriber['username'] . " <" . $subscriber['email'] . ">",
                        null,
                        null,
                        $subject,
                        $body,
                        false
                    );
                } catch (Exception) {
                    // Ignorar falhas no envio de e-mail e continuar
                }
            }
        }
    }
}