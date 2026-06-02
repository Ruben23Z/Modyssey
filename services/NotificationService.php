<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../Lib/HtmlMimeMail.php';

class NotificationService
{
    /**
     * Notify subscribers of a new public mod.
     */
    public static function notifySubscribers(int $modId): void
    {
        try {
            $db = Database::getInstance();

            $stmt = $db->prepare('
                SELECT m.title, m.description, m.game_id, g.name AS game_name, u.username AS uploader_name
                  FROM `mod` m
                  JOIN game g ON g.IDGame = m.game_id
                  JOIN user u ON u.IDUser = m.uploaded_by
                 WHERE m.IDMod = ? AND m.visibility = "public"
            ');
            $stmt->execute([$modId]);
            $mod = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$mod) {
                return;
            }

            $stmt = $db->prepare('
                SELECT category_id FROM mod_category WHERE mod_id = ?
            ');
            $stmt->execute([$modId]);
            $categoryIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $query = '
                SELECT DISTINCT u.IDUser AS id, u.username, u.email
                  FROM user u
                  JOIN user_subscription us ON us.user_id = u.IDUser
                 WHERE u.active = 1 AND (us.game_id = ?';

            $params = [$mod['game_id']];

            if (!empty($categoryIds)) {
                $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
                $query .= ' OR us.category_id IN (' . $placeholders . ')';
                $params = array_merge($params, $categoryIds);
            }

            $query .= ')';

            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($subscribers)) {
                return;
            }

            $emailConfigFile = __DIR__ . '/../config/configuracoes/.htconfigEmail.xml';
            if (!file_exists($emailConfigFile)) {
                return;
            }

            $xml = simplexml_load_file($emailConfigFile);
            if ($xml === false) {
                return;
            }

            $smtpServer     = (string)$xml->Account->Server;
            $sslValue       = strtolower(trim((string)$xml->Account->SSL));
            $useSSL         = ($sslValue === 'true' || $sslValue === '1') ? 1 : 0;
            $port           = (int)$xml->Account->Port;
            $loginName      = (string)$xml->Account->LoginName;
            $passwordEmail  = (string)$xml->Account->Password;
            $fromEmail      = (string)$xml->Account->Email;
            $displayName    = (string)$xml->Account->DisplayName;

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443 ? 'https' : 'http';
            $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
            $serverPort = $_SERVER['SERVER_PORT'] ?? '80';
            $portPart = '';
            if (($protocol === 'http' && $serverPort != 80) && ($protocol === 'https' && $serverPort != 443)) {
                $portPart = ":$serverPort";
            }

            $baseUrl = defined('BASE_URL') ? BASE_URL : '/Modyssey/public';
            $link = "$protocol://$serverName$portPart" . $baseUrl . "/mods/$modId";

            $subject = "Novo Mod Disponível: " . $mod['title'] . " - Modyssey";

            foreach ($subscribers as $sub) {
                $username = htmlspecialchars($sub['username']);
                $modTitle = htmlspecialchars($mod['title']);
                $gameName = htmlspecialchars($mod['game_name']);
                $uploader = htmlspecialchars($mod['uploader_name']);
                $description = nl2br(htmlspecialchars($mod['description']));

                $msgHtml = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #fff; color: #333;'>
                        <h2 style='color: #8b5cf6; margin-top: 0;'>Olá, $username!</h2>
                        <p style='font-size: 1.1rem; line-height: 1.5;'>Um novo mod que te pode interessar foi publicado no <strong>Modyssey</strong>!</p>
                        <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                        <h3 style='margin-bottom: 5px; color: #111;'>$modTitle</h3>
                        <p style='margin: 0 0 15px 0; font-size: 0.9rem; color: #666;'><strong>Jogo:</strong> $gameName | <strong>Por:</strong> $uploader</p>
                        <blockquote style='margin: 0 0 20px 0; padding: 10px 15px; background: #f9f9f9; border-left: 4px solid #8b5cf6; font-style: italic;'>
                            $description
                        </blockquote>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='$link' style='background: #8b5cf6; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; display: inline-block;'>Ver e Descarregar Mod</a>
                        </div>
                        <p style='font-size: 0.8rem; color: #999; text-align: center; margin-top: 40px;'>
                            Recebeste esta mensagem porque estás subscrito a este jogo ou categoria no Modyssey.<br>
                            Podes gerir as tuas subscrições na tua área pessoal.
                        </p>
                    </div>
                ";


                $mail = new HtmlMimeMail();
                $mail->add_html($msgHtml, strip_tags($msgHtml));
                $mail->build_message();

                @$mail->send(
                    $smtpServer,
                    $useSSL,
                    $port,
                    $loginName,
                    $passwordEmail,
                    $sub['username'],
                    $sub['email'],
                    $displayName,
                    $fromEmail,
                    $subject
                );
            }
        } catch (Exception) {
        }
    }
}
