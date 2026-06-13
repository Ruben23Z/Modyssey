<?php

require_once __DIR__ . '/../models/Stats.php';

class StatsController
{
    private Stats $stats;

    public function __construct()
    {
        $this->stats = new Stats();
    }

    public function index(): void
    {
        $topMods        = $this->stats->topMods();
        $topGames       = $this->stats->topGames();
        $topUsers       = $this->stats->topUsers();
        $uploadsPerMonth = $this->stats->uploadsPerMonth();

        require __DIR__ . '/../views/stats/index.php';
    }
}
