<?php
require_once __DIR__ . '/_auth_admin.php';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        $pdo->prepare("UPDATE event_registrations SET status = 'approved' WHERE id = ?")
            ->execute([$id]);
        header("Location: pendaftaran.php?msg=approved");
        exit;

    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE event_registrations SET status = 'rejected' WHERE id = ?")
            ->execute([$id]);
        header("Location: pendaftaran.php?msg=rejected");
        exit;
    }
}

$eventList = $pdo->query("SELECT id, title FROM events ORDER BY tanggal DESC")
                ->fetchAll();

$filter_event = $_GET['event'] ?? 'all';

if ($filter_event === 'all') {
    $stmt = $pdo->query("
        SELECT r.id AS reg_id, r.status, r.registered_at,
               u.name AS user_name, u.email,
               e.title AS event_title, e.tanggal, e.waktu
        FROM event_registrations r
        JOIN users u ON r.user_id = u.id
        JOIN events e ON r.event_id = e.id
        ORDER BY r.registered_at DESC
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT r.id AS reg_id, r.status, r.registered_at,
               u.name AS user_name, u.email,
               e.title AS event_title, e.tanggal, e.waktu
        FROM event_registrations r
        JOIN users u ON r.user_id = u.id
        JOIN events e ON r.event_id = e.id
        WHERE e.id = ?
        ORDER BY r.registered_at DESC
    ");
    $stmt->execute([$filter_event]);
}

$registrations = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pendaftaran Event - EventHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50 flex">
<?php include 'sidebar.php'; ?>

<main class="flex-1 ml-64 p-8">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-3xl font-bold text-slate-800">Pendaftaran Event</h2>
            <p class="text-slate-500 text-sm">Kelola pendaftar untuk semua event.</p>
        </div>
    </div>

    <!-- Notifikasi -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="mb-6 p-4 rounded-lg text-white
            <?= $_GET['msg'] === 'approved' ? 'bg-emerald-600' : 'bg-red-600' ?>">
            <?= $_GET['msg'] === 'approved' ? 'Pendaftaran berhasil di-approve!' : 'Pendaftaran berhasil ditolak!' ?>
        </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mb-8">
        <form method="GET" class="flex items-center gap-4">

            <label class="font-semibold text-slate-700">Filter Event:</label>

            <select name="event" class="px-4 py-2 border rounded-lg">
                <option value="all">Semua Event</option>

                <?php foreach ($eventList as $ev): ?>
                    <option value="<?= $ev['id'] ?>"
                        <?= $filter_event == $ev['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ev['title']) ?>
                    </option>
                <?php endforeach; ?>

            </select>

            <button class="px-6 py-2 bg-slate-900 rounded-lg text-white hover:bg-slate-800">
                Terapkan
            </button>

        </form>
    </div>

    <!-- Tabel Pendaftaran -->
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200">
        <h3 class="text-xl font-bold text-slate-800 mb-4">Daftar Pendaftar</h3>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                <tr class="bg-gray-100 text-slate-700">
                    <th class="py-3 px-4">Nama</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4">Event</th>
                    <th class="py-3 px-4">Tanggal</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Aksi</th>
                </tr>
                </thead>

                <tbody>
                <?php if (!$registrations): ?>
                    <tr>
                        <td colspan="6" class="text-center py-6 text-slate-500">
                            Tidak ada pendaftar untuk event ini.
                        </td>
                    </tr>

                <?php else: ?>
                    <?php foreach ($registrations as $reg): ?>
                        <tr class="border-b hover:bg-gray-50">

                            <td class="py-3 px-4 font-semibold">
                                <?= htmlspecialchars($reg['user_name']) ?>
                            </td>

                            <td class="py-3 px-4">
                                <?= htmlspecialchars($reg['email']) ?>
                            </td>

                            <td class="py-3 px-4">
                                <?= htmlspecialchars($reg['event_title']) ?>
                            </td>

                            <td class="py-3 px-4">
                                <?= date('d M Y', strtotime($reg['tanggal'])) ?>
                                <br>
                                <span class="text-xs text-slate-400">
                                    <?= substr($reg['waktu'], 0, 5) ?>
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <?php if ($reg['status'] === 'pending'): ?>
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">
                                        Pending
                                    </span>
                                <?php elseif ($reg['status'] === 'approved'): ?>
                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">
                                        Approved
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                                        Rejected
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="py-3 px-4">
                                <?php if ($reg['status'] === 'pending'): ?>

                                    <a href="pendaftaran.php?action=approve&id=<?= $reg['reg_id'] ?>"
                                       class="text-emerald-600 font-semibold hover:underline mr-4">
                                        <i class="fa-solid fa-check"></i> Approve
                                    </a>

                                    <a href="pendaftaran.php?action=reject&id=<?= $reg['reg_id'] ?>"
                                       class="text-red-600 font-semibold hover:underline"
                                       onclick="return confirm('Tolak pendaftaran ini?')">
                                        <i class="fa-solid fa-xmark"></i> Reject
                                    </a>

                                <?php else: ?>
                                    <span class="text-slate-400 text-sm italic">Tidak ada aksi</span>
                                <?php endif; ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                </tbody>
            </table>
        </div>

    </div>

</main>
</body>
</html>