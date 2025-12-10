<?php
require_once __DIR__ . '/_auth_admin.php';

// ========================
// 1. Statistik utama
// ========================
$totalEvent = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();

$totalPendaftarApproved = (int)$pdo->query("
    SELECT COUNT(*) FROM event_registrations WHERE status = 'approved'
")->fetchColumn();

$totalPesertaUnik = (int)$pdo->query("
    SELECT COUNT(DISTINCT user_id) FROM event_registrations WHERE status = 'approved'
")->fetchColumn();

// sementara, jika belum ada tabel kehadiran, kita pakai nilai dummy
$attendanceRate = 89; // bisa diganti nanti dengan hitung berdasarkan tabel kehadiran

// ========================
// 2. Event mendatang (next 3)
// ========================
$upcomingStmt = $pdo->prepare("
    SELECT e.*,
           (SELECT COUNT(*) FROM event_registrations r 
            WHERE r.event_id = e.id AND r.status = 'approved') AS total_approved
    FROM events e
    WHERE e.tanggal >= CURDATE()
    ORDER BY e.tanggal ASC
    LIMIT 3
");
$upcomingStmt->execute();
$upcomingEvents = $upcomingStmt->fetchAll();

// Jika kurang dari 3 event, sisanya nanti tidak ditampilkan, tapi layout tetap rapi.

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EventHub</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .fc-event { cursor: pointer; border: none; }
        .fc-daygrid-event { background-color: #10b981; border-radius: 4px; padding: 2px 4px; }
        .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 700; color: #1e293b; }
        .fc-button-primary { background-color: #10b981 !important; border-color: #10b981 !important; }
        .fc-button-active { background-color: #059669 !important; }
    </style>
</head>

<body class="bg-gray-50 flex h-screen overflow-hidden">

<?php include 'sidebar.php'; ?>

<main class="flex-1 ml-64 flex flex-col h-screen overflow-y-auto">

    <!-- HEADER persis seperti HTML -->
    <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
        <div class="flex items-center gap-4 text-slate-500">
            <button class="hover:text-slate-800">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
            <span>Selamat datang di EventHub</span>
        </div>
        <div class="relative">
            <button class="p-2 text-slate-500 hover:bg-gray-100 rounded-full relative">
                <i class="fa-regular fa-bell text-xl"></i>
                <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>
        </div>
    </header>

    <!-- KONTEN DASHBOARD -->
    <div class="p-8">

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-800">Dashboard</h2>
            <p class="text-slate-500">Ringkasan aktivitas event dan peserta organisasi Anda.</p>
        </div>

        <!-- CARD STATISTICS (disamakan dengan HTML, angka diisi dari DB) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

            <!-- Total Event -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Total Event</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1">
                            <?= $totalEvent ?>
                        </h3>
                    </div>
                    <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                        <i class="fa-regular fa-calendar text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-emerald-600 font-semibold">
                    <!-- placeholder growth -->
                    +12% <span class="text-slate-400 font-normal">event terdata di sistem</span>
                </p>
            </div>

            <!-- Total Peserta (pendaftar approved) -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Total Pendaftar</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1">
                            <?= $totalPendaftarApproved ?>
                        </h3>
                    </div>
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                        <i class="fa-solid fa-users text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-emerald-600 font-semibold">
                    +8% <span class="text-slate-400 font-normal">pendaftaran yang disetujui</span>
                </p>
            </div>

            <!-- Peserta Unik -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Peserta Unik</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1">
                            <?= $totalPesertaUnik ?>
                        </h3>
                    </div>
                    <div class="p-2 bg-orange-50 rounded-lg text-orange-600">
                        <i class="fa-solid fa-clipboard-check text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-emerald-600 font-semibold">
                    +23% <span class="text-slate-400 font-normal">mengikuti minimal 1 event</span>
                </p>
            </div>

            <!-- Tingkat Kehadiran (sementara statis) -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Tingkat Kehadiran</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1">
                            <?= $attendanceRate ?>%
                        </h3>
                    </div>
                    <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                        <i class="fa-solid fa-chart-line text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-emerald-600 font-semibold">
                    +5% <span class="text-slate-400 font-normal">estimasi rata-rata</span>
                </p>
            </div>

        </div>

        <!-- GRID: Event Mendatang + Aksi Cepat -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Event Mendatang (dinamis, tapi UI sama) -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Event Mendatang</h3>
                <p class="text-sm text-slate-400 mb-6">Daftar event yang akan segera berlangsung</p>

                <div class="space-y-4">
                    <?php if (!$upcomingEvents): ?>
                        <p class="text-sm text-slate-500">Belum ada event mendatang.</p>
                    <?php else: ?>
                        <?php foreach ($upcomingEvents as $ev): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100">
                                <div>
                                    <h4 class="font-semibold text-slate-800">
                                        <?= htmlspecialchars($ev['title']) ?>
                                    </h4>
                                    <p class="text-xs text-slate-500 mt-1">
                                        <i class="fa-regular fa-calendar mr-1"></i>
                                        <?= date('d M Y', strtotime($ev['tanggal'])) ?>
                                        <?php if (!empty($ev['waktu'])): ?>
                                            • <?= substr($ev['waktu'], 0, 5) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="flex items-center text-slate-500 text-sm">
                                    <i class="fa-solid fa-user-group mr-2"></i>
                                    <?= $ev['total_approved'] ?> peserta
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Aksi Cepat (tetap sama, hanya link diarahkan ke .php) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Aksi Cepat</h3>
                <p class="text-sm text-slate-400 mb-6">Pintasan untuk tugas yang sering dilakukan</p>

                <div class="grid grid-cols-2 gap-4">
                    <a href="events.php#form-tambah"
                       class="p-4 rounded-lg border border-gray-200 hover:border-emerald-500 hover:bg-emerald-50 transition text-left group">
                        <i class="fa-regular fa-calendar-plus text-2xl text-slate-400 group-hover:text-emerald-600 mb-2 block"></i>
                        <span class="text-sm font-semibold text-slate-700 group-hover:text-emerald-800">
                            Buat Event Baru
                        </span>
                        <span class="text-xs text-slate-400 block mt-1">Tambah event baru</span>
                    </a>

                    <a href="peserta.php"
                       class="p-4 rounded-lg border border-gray-200 hover:border-emerald-500 hover:bg-emerald-50 transition text-left group">
                        <i class="fa-solid fa-users-viewfinder text-2xl text-slate-400 group-hover:text-emerald-600 mb-2 block"></i>
                        <span class="text-sm font-semibold text-slate-700 group-hover:text-emerald-800">Lihat Peserta</span>
                        <span class="text-xs text-slate-400 block mt-1">Kelola data peserta</span>
                    </a>

                    <a href="pendaftaran.php"
                       class="p-4 rounded-lg border border-gray-200 hover:border-emerald-500 hover:bg-emerald-50 transition text-left group">
                        <i class="fa-solid fa-file-pen text-2xl text-slate-400 group-hover:text-emerald-600 mb-2 block"></i>
                        <span class="text-sm font-semibold text-slate-700 group-hover:text-emerald-800">Pendaftaran</span>
                        <span class="text-xs text-slate-400 block mt-1">Kelola pendaftar event</span>
                    </a>

                    <a href="laporan.php"
                       class="p-4 rounded-lg border border-gray-200 hover:border-emerald-500 hover:bg-emerald-50 transition text-left group">
                        <i class="fa-solid fa-chart-pie text-2xl text-slate-400 group-hover:text-emerald-600 mb-2 block"></i>
                        <span class="text-sm font-semibold text-slate-700 group-hover:text-emerald-800">Buat Laporan</span>
                        <span class="text-xs text-slate-400 block mt-1">Lihat laporan sistem</span>
                    </a>
                </div>
            </div>

        </div>

    </div>
</main>

</body>
</html>
