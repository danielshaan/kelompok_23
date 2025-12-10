<?php
require_once __DIR__ . '/_auth_admin.php';

/* ======================================================
   1. Query Statistik Utama
====================================================== */

$totalEvent = $pdo->query("SELECT COUNT(*) AS c FROM events")->fetch()['c'] ?? 0;

$totalPendaftar = $pdo->query("
    SELECT COUNT(*) AS c FROM event_registrations WHERE status = 'approved'
")->fetch()['c'] ?? 0;

$totalPesertaUnik = $pdo->query("
    SELECT COUNT(DISTINCT user_id) AS c 
    FROM event_registrations 
    WHERE status = 'approved'
")->fetch()['c'] ?? 0;


/* ======================================================
   2. Event Paling Populer (Top 5)
====================================================== */
$topEvent = $pdo->query("
    SELECT e.title, 
           COUNT(r.id) AS total_peserta
    FROM events e
    LEFT JOIN event_registrations r 
        ON r.event_id = e.id AND r.status = 'approved'
    GROUP BY e.id
    ORDER BY total_peserta DESC
    LIMIT 5
")->fetchAll();

/* ======================================================
   3. Chart: Jumlah Pendaftar per Event
====================================================== */
$chart_event = $pdo->query("
    SELECT e.title,
           (SELECT COUNT(*) FROM event_registrations r 
            WHERE r.event_id = e.id AND r.status = 'approved') AS jumlah
    FROM events e
    ORDER BY e.tanggal ASC
")->fetchAll();

/* ======================================================
   4. Chart Bulanan: Jumlah Event per Bulan
====================================================== */
$chart_bulan = $pdo->query("
    SELECT DATE_FORMAT(tanggal, '%Y-%m') AS bulan,
           COUNT(*) AS jumlah
    FROM events
    GROUP BY bulan
    ORDER BY bulan
")->fetchAll();

/* ======================================================
   5. Chart: Approved vs Rejected
====================================================== */
$stats_status = $pdo->query("
    SELECT 
        SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) AS approved,
        SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) AS rejected
    FROM event_registrations
")->fetch();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan & Statistik - EventHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50 flex">

<?php include 'sidebar.php'; ?>

<main class="flex-1 ml-64 p-8">

    <!-- HEADER -->
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-slate-800">Laporan & Statistik</h2>
        <p class="text-slate-500">Analisis kegiatan event dan peserta.</p>
    </div>

<!-- Tombol Export CSV -->
<div class="flex justify-end mb-6">
    <a href="export_laporan.php" 
       class="px-5 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition font-semibold">
        <i class="fa-solid fa-file-export mr-2"></i> Export Data (CSV)
    </a>
</div>

    <!-- STATISTIK UTAMA -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

        <div class="p-6 bg-white rounded-xl shadow border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <p class="text-sm text-slate-500">Total Event</p>
                    <h3 class="text-3xl font-bold"><?= $totalEvent ?></h3>
                </div>
                <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
                    <i class="fa-solid fa-calendar-days text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="p-6 bg-white rounded-xl shadow border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <p class="text-sm text-slate-500">Total Peserta Unik</p>
                    <h3 class="text-3xl font-bold"><?= $totalPesertaUnik ?></h3>
                </div>
                <div class="p-3 bg-emerald-100 text-emerald-600 rounded-lg">
                    <i class="fa-solid fa-users text-2xl"></i>
                </div>
            </div>

        </div>

        <div class="p-6 bg-white rounded-xl shadow border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <p class="text-sm text-slate-500">Pendaftar (Approved)</p>
                    <h3 class="text-3xl font-bold"><?= $totalPendaftar ?></h3>
                </div>
                <div class="p-3 bg-purple-100 text-purple-600 rounded-lg">
                    <i class="fa-solid fa-user-check text-2xl"></i>
                </div>
            </div>
        </div>

    </div>




    <!-- CHARTS GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">

        <!-- Chart: Pendaftar per Event -->
        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="text-xl font-bold text-slate-800 mb-4">Peserta per Event</h3>
            <canvas id="chartEvent"></canvas>
        </div>

        <!-- Chart: Event per Bulan -->
        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="text-xl font-bold text-slate-800 mb-4">Event per Bulan</h3>
            <canvas id="chartBulan"></canvas>
        </div>

        <!-- Chart: Approved vs Rejected -->
        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="text-xl font-bold text-slate-800 mb-4">Status Pendaftaran</h3>
            <canvas id="chartStatus"></canvas>
        </div>

    </div>




    <!-- TOP EVENT -->
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mb-10">
        <h3 class="text-xl font-bold text-slate-800 mb-4">Event Paling Populer</h3>

        <table class="w-full text-left">
            <thead>
            <tr class="bg-gray-100 text-slate-700">
                <th class="py-3 px-4">Event</th>
                <th class="py-3 px-4">Peserta Approved</th>
            </tr>
            </thead>

            <tbody>
            <?php if (!$topEvent): ?>
                <tr>
                    <td colspan="2" class="py-4 text-center text-slate-500">
                        Tidak ada data event.
                    </td>
                </tr>

            <?php else: ?>
                <?php foreach ($topEvent as $ev): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4 font-semibold">
                            <?= htmlspecialchars($ev['title']) ?>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">
                                <?= $ev['total_peserta'] ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>

        </table>

    </div>
</main>



<!-- ===============================
     CHART.JS SCRIPT
================================== -->

<script>
/* =============================
   Chart: Peserta per Event
============================= */
const ctxEvent = document.getElementById('chartEvent').getContext('2d');
new Chart(ctxEvent, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($chart_event, 'title')) ?>,
        datasets: [{
            label: 'Peserta',
            data: <?= json_encode(array_column($chart_event, 'jumlah')) ?>,
            backgroundColor: 'rgba(16, 185, 129, 0.7)',
            borderColor: '#10b981',
            borderWidth: 1
        }]
    },
    options: { responsive: true }
});

/* =============================
   Chart: Event per Bulan
============================= */
const ctxBulan = document.getElementById('chartBulan').getContext('2d');
new Chart(ctxBulan, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($chart_bulan, 'bulan')) ?>,
        datasets: [{
            label: 'Total Event',
            data: <?= json_encode(array_column($chart_bulan, 'jumlah')) ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.2)',
            fill: true,
            tension: 0.3
        }]
    },
    options: { responsive: true }
});

/* =============================
   Chart: Approved vs Rejected
============================= */
const ctxStatus = document.getElementById('chartStatus').getContext('2d');
new Chart(ctxStatus, {
    type: 'doughnut',
    data: {
        labels: ['Approved', 'Rejected'],
        datasets: [{
            data: [
                <?= $stats_status['approved'] ?>,
                <?= $stats_status['rejected'] ?>
            ],
            backgroundColor: ['#10b981', '#ef4444']
        }]
    },
    options: { responsive: true }
});
</script>

</body>
</html>
