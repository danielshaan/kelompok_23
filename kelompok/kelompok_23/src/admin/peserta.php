<?php
require_once __DIR__ . '/_auth_admin.php';

$search = $_GET['search'] ?? '';

if ($search) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.created_at,
               (SELECT COUNT(*) 
                FROM event_registrations r 
                WHERE r.user_id = u.id AND r.status = 'approved') AS total_approved
        FROM users u
        WHERE u.role = 'anggota'
          AND (u.name LIKE ? OR u.email LIKE ?)
        ORDER BY u.created_at DESC
    ");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("
        SELECT u.id, u.name, u.email, u.created_at,
               (SELECT COUNT(*) 
                FROM event_registrations r 
                WHERE r.user_id = u.id AND r.status = 'approved') AS total_approved
        FROM users u
        WHERE u.role = 'anggota'
        ORDER BY u.created_at DESC
    ");
}

$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Peserta - EventHub</title>
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
            <h2 class="text-3xl font-bold text-slate-800">Data Peserta</h2>
            <p class="text-slate-500 text-sm">Daftar seluruh pengguna yang terdaftar sebagai anggota.</p>
        </div>
    </div>

    <!-- Pencarian -->
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mb-8">

        <form method="GET" class="flex items-center gap-4">

            <input type="text" name="search" placeholder="Cari nama atau email..."
                   value="<?= htmlspecialchars($search) ?>"
                   class="px-4 py-2 border rounded-lg w-72">

            <?php if ($search): ?>
                <a href="peserta.php"
                   class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                    Reset
                </a>
            <?php endif; ?>

            <button class="px-6 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">
                Cari
            </button>
        </form>

    </div>

    <!-- Tabel Peserta -->
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200">
        <h3 class="text-xl font-bold text-slate-800 mb-4">Daftar Peserta</h3>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                <tr class="bg-gray-100 text-slate-700">
                    <th class="py-3 px-4">Nama</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4">Bergabung</th>
                    <th class="py-3 px-4">Event Diikuti</th>
                </tr>
                </thead>

                <tbody>
                <?php if (!$users): ?>

                    <tr>
                        <td colspan="4" class="text-center py-6 text-slate-500">
                            Tidak ada peserta ditemukan.
                        </td>
                    </tr>

                <?php else: ?>
                    <?php foreach ($users as $u): ?>

                        <tr class="border-b hover:bg-gray-50">

                            <td class="py-3 px-4 font-semibold">
                                <?= htmlspecialchars($u['name']) ?>
                            </td>

                            <td class="py-3 px-4">
                                <?= htmlspecialchars($u['email']) ?>
                            </td>

                            <td class="py-3 px-4">
                                <?= date('d M Y', strtotime($u['created_at'])) ?>
                            </td>

                            <td class="py-3 px-4">
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">
                                    <?= $u['total_approved'] ?> event
                                </span>
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