<?php
require_once __DIR__ . '/../../core/Lang.php';
$pageTitle = Lang::t('stats_page_title');
require __DIR__ . '/../layout/header.php';

// preparar dados para os gráficos Chart.js
$modsLabels    = json_encode(array_column($topMods, 'title'));
$modsData      = json_encode(array_column($topMods, 'download_count'));
$gamesLabels   = json_encode(array_column($topGames, 'name'));
$gamesData     = json_encode(array_column($topGames, 'mod_count'));
$usersLabels   = json_encode(array_column($topUsers, 'username'));
$usersData     = json_encode(array_column($topUsers, 'mod_count'));
$monthLabels   = json_encode(array_column($uploadsPerMonth, 'month'));
$monthData     = json_encode(array_column($uploadsPerMonth, 'total'));
?>

<main>
<div class="container" style="max-width:1000px;padding-top:32px;padding-bottom:48px;">
    <h1 style="font-size:1.6rem;font-weight:700;margin-bottom:8px;"><?= Lang::t('stats_title') ?></h1>
    <p style="color:var(--text-muted);margin-bottom:36px;font-size:.9rem;"><?= Lang::t('stats_subtitle') ?></p>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;" class="stats-grid">

        <!-- Top 5 mods mais descarregados -->
        <div class="card">
            <div class="card-body">
                <h2 style="font-size:1rem;font-weight:700;margin-bottom:16px;"><?= Lang::t('stats_top_mods') ?></h2>
                <canvas id="chartMods" height="220"></canvas>
            </div>
        </div>

        <!-- Jogos com mais mods -->
        <div class="card">
            <div class="card-body">
                <h2 style="font-size:1rem;font-weight:700;margin-bottom:16px;"><?= Lang::t('stats_top_games') ?></h2>
                <canvas id="chartGames" height="220"></canvas>
            </div>
        </div>

        <!-- Utilizadores mais ativos -->
        <div class="card">
            <div class="card-body">
                <h2 style="font-size:1rem;font-weight:700;margin-bottom:16px;"><?= Lang::t('stats_top_users') ?></h2>
                <canvas id="chartUsers" height="220"></canvas>
            </div>
        </div>

        <!-- Uploads por mês -->
        <div class="card">
            <div class="card-body">
                <h2 style="font-size:1rem;font-weight:700;margin-bottom:16px;"><?= Lang::t('stats_uploads_month') ?></h2>
                <canvas id="chartMonths" height="220"></canvas>
            </div>
        </div>

    </div>
</div>
</main>

<style>
@media (max-width: 650px) {
    .stats-grid { grid-template-columns: 1fr !important; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// cores bonitas mas simples
const colors = ['#6c63ff','#43b89c','#f7b731','#e74c3c','#3498db'];

const opts = {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
        x: { ticks: { color: '#aaa', font: { size: 11 } }, grid: { color: 'rgba(255,255,255,.05)' } },
        y: { ticks: { color: '#aaa', font: { size: 11 } }, grid: { color: 'rgba(255,255,255,.05)' }, beginAtZero: true }
    }
};

// mods mais descarregados — barras horizontais
new Chart(document.getElementById('chartMods'), {
    type: 'bar',
    data: {
        labels: <?= $modsLabels ?>,
        datasets: [{ data: <?= $modsData ?>, backgroundColor: colors }]
    },
    options: { ...opts, indexAxis: 'y' }
});

// jogos com mais mods — doughnut
new Chart(document.getElementById('chartGames'), {
    type: 'doughnut',
    data: {
        labels: <?= $gamesLabels ?>,
        datasets: [{ data: <?= $gamesData ?>, backgroundColor: colors }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { color: '#aaa', font: { size: 11 } } } } }
});

// utilizadores mais ativos — barras
new Chart(document.getElementById('chartUsers'), {
    type: 'bar',
    data: {
        labels: <?= $usersLabels ?>,
        datasets: [{ data: <?= $usersData ?>, backgroundColor: colors }]
    },
    options: opts
});

// uploads por mês — linha
new Chart(document.getElementById('chartMonths'), {
    type: 'line',
    data: {
        labels: <?= $monthLabels ?>,
        datasets: [{
            data: <?= $monthData ?>,
            borderColor: '#6c63ff',
            backgroundColor: 'rgba(108,99,255,.15)',
            fill: true,
            tension: 0.3,
            pointBackgroundColor: '#6c63ff'
        }]
    },
    options: opts
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
