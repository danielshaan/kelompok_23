<?php
session_start();
require_once __DIR__ . '/../database/db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userId     = $_SESSION['user_id'] ?? null;
$role       = $_SESSION['role'] ?? 'guest';
$name       = $_SESSION['name'] ?? '';

// Ambil ID event
$event_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    die("Event tidak ditemukan.");
}

// Hitung jumlah peserta yang approved
$stmtCount = $pdo->prepare("
    SELECT COUNT(*) FROM event_registrations 
    WHERE event_id = ? AND status = 'approved'
");
$stmtCount->execute([$event_id]);
$totalApproved = $stmtCount->fetchColumn();

// Cek apakah user sudah mendaftar event
$isRegistered = false;
if ($isLoggedIn && $role === 'anggota') {
    $stmtRegistered = $pdo->prepare("
        SELECT * FROM event_registrations 
        WHERE event_id = ? AND user_id = ?
    ");
    $stmtRegistered->execute([$event_id, $userId]);
    $isRegistered = $stmtRegistered->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($event['title']) ?> - EventHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">

<!-- NAVBAR -->
<nav class="fixed w-full top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-6 flex justify-between items-center py-4">
        <h1 class="text-2xl font-bold text-gray-800">EventHub</h1>

        <div class="flex gap-4">
            <?php if (!$isLoggedIn): ?>
                <a href="login.php" class="text-gray-700 font-semibold px-4 py-2 hover:bg-gray-100 rounded-lg">
                    Login
                </a>
                <a href="register.php" class="bg-[#111C3D] text-white px-4 py-2 rounded-lg hover:bg-[#0d152f]">
                    Daftar
                </a>
            <?php else: ?>
                <span class="text-gray-600">Hai, <?= htmlspecialchars($name) ?></span>
                <a href="logout.php" class="text-gray-700 hover:underline">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="pt-28 max-w-5xl mx-auto px-4">

    <!-- Poster Event -->
    <div class="rounded-2xl overflow-hidden shadow-xl mb-8">
        <?php if (!empty($event['thumbnail'])): ?>
            <img src="../uploads/event_images/<?= htmlspecialchars($event['thumbnail']) ?>"
                 class="w-full h-80 object-cover">
        <?php else: ?>
            <div class="w-full h-80 bg-gray-300"></div>
        <?php endif; ?>
    </div>

    <!-- Informasi Event -->
    <div class="bg-white border rounded-2xl p-8 shadow">

        <span class="px-4 py-1 bg-blue-100 text-blue-700 text-sm rounded-full font-semibold">
            <?= htmlspecialchars($event['kategori'] ?: 'Event') ?>
        </span>

        <h1 class="text-4xl font-bold text-gray-900 mt-4 mb-4">
            <?= htmlspecialchars($event['title']) ?>
        </h1>

        <p class="text-gray-600 text-lg leading-relaxed mb-6">
            <?= nl2br(htmlspecialchars($event['description'])) ?>
        </p>

        <!-- Informasi detail -->
        <div class="grid sm:grid-cols-2 gap-6 mb-8">

            <div>
                <h3 class="font-semibold text-gray-900 mb-1">Tanggal & Waktu</h3>
                <p class="text-gray-700">
                    <?= date('d M Y', strtotime($event['tanggal'])) ?>  
                    • <?= substr($event['waktu'], 0, 5) ?>
                </p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900 mb-1">Lokasi</h3>
                <p class="text-gray-700"><?= htmlspecialchars($event['lokasi']) ?></p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900 mb-1">Kuota</h3>
                <p class="text-gray-700">
                    <?= $totalApproved ?> / <?= $event['kuota'] ?> peserta
                </p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900 mb-1">Status Anda</h3>
                <?php if (!$isLoggedIn): ?>
                    <p class="text-yellow-700">Silakan login terlebih dahulu.</p>
                <?php elseif ($role === 'admin'): ?>
                    <p class="text-gray-600">Admin tidak dapat mendaftar.</p>
                <?php elseif ($isRegistered): ?>
                    <p class="text-emerald-700 font-semibold">Anda sudah terdaftar</p>
                <?php else: ?>
                    <p class="text-blue-700">Anda belum mendaftar</p>
                <?php endif; ?>
            </div>

        </div>

        <!-- Tombol Daftar Event -->
        <div class="mt-6">
            <?php if (!$isLoggedIn): ?>
                <a href="login.php"
                   class="block text-center bg-[#111C3D] text-white py-4 rounded-xl font-semibold hover:bg-[#0d152f]">
                    Login untuk Mendaftar
                </a>

            <?php elseif ($role === 'admin'): ?>
                <button class="w-full bg-gray-300 py-4 rounded-xl text-gray-600 font-semibold" disabled>
                    Admin tidak dapat mendaftar
                </button>

            <?php elseif ($isRegistered): ?>
                <button class="w-full bg-gray-200 py-4 rounded-xl text-gray-600 font-semibold" disabled>
                    Anda sudah terdaftar
                </button>

            <?php else: ?>
                <form action="daftar_event.php" method="POST">
                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                    <button type="submit"
                            class="w-full bg-[#111C3D] text-white py-4 rounded-xl font-bold hover:bg-[#0d152f] transition">
                        Daftar Sekarang
                    </button>
                </form>
            <?php endif; ?>
        </div>

    </div>
</div>

<div class="h-24"></div>

</body>
</html>
