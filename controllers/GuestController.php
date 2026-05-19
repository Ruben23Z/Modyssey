<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Mod.php';

class GuestController
{
    private Mod $modModel;

    public function __construct()
    {
        $this->modModel = new Mod();
    }

    public function index(): void
    {
        $user = Auth::user();
        $mods = $this->modModel->allVisible($user['id'], $user['role']);
        require __DIR__ . '/../views/home/index.php';
    }
}
