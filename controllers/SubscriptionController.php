<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/Notification.php';

class SubscriptionController
{
    private Subscription $subscriptionModel;
    private Notification $notificationModel;

    public function __construct()
    {
        $this->subscriptionModel = new Subscription();
        $this->notificationModel = new Notification();
    }

    public function index(): void
    {
        Auth::require('user');
        
        $user = Auth::user();
        $userId = (int)$user['id'];

        $subscribedGames = $this->subscriptionModel->getSubscribedGames($userId);
        $notifications = $this->notificationModel->getForUser($userId);

        $this->notificationModel->markAllAsRead($userId);

        require __DIR__ . '/../views/subscriptions/index.php';
    }

    public function toggle(): void
    {
        Auth::require('user');

        $user = Auth::user();
        $userId = (int)$user['id'];
        $gameId = (int)($_POST['game_id'] ?? 0);

        if ($gameId > 0) {
            $isSubscribed = $this->subscriptionModel->isSubscribed($userId, $gameId);
            
            if ($isSubscribed) {
                $this->subscriptionModel->unsubscribe($userId, $gameId);
            } else {
                $this->subscriptionModel->subscribe($userId, $gameId);
            }
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (str_contains($referer, '/games/' . $gameId)) {
            header('Location: ' . BASE_URL . '/games/' . $gameId);
        } else {
            header('Location: ' . BASE_URL . '/subscriptions');
        }
        exit;
    }
}
