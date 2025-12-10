<?php
require_once __DIR__ . '/_auth_admin.php';

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ../public/login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) {
    die("User tidak ditemukan.");
}

$errors_profile  = [];
$success_profile = '';
$errors_password = [];
$success_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_profile') {
        $name          = trim($_POST['name'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $jenis_kelamin = $_POST['jenis_kelamin'] ?? null;
        $tanggal_lahir = $_POST['tanggal_lahir'] ?? null;
        $notif_email   = isset($_POST['notif_email']) ? 1 : 0;
        $dark_mode     = isset($_POST['dark_mode']) ? 1 : 0;

        if ($name === '')  $errors_profile[] = "Nama tidak boleh kosong.";
        if ($email === '') $errors_profile[] = "Email tidak boleh kosong.";

        if ($email !== '') {
            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmtCheck->execute([$email, $userId]);
            if ($stmtCheck->fetch()) {
                $errors_profile[] = "Email sudah digunakan oleh pengguna lain.";
            }
        }

        $foto_profil = $user['foto_profil'] ?? null;

        if (!empty($_FILES['foto_profil']['name'])) {
            $file      = $_FILES['foto_profil'];
            $allowed   = ['image/jpeg', 'image/png', 'image/jpg'];
            $maxSize   = 2 * 1024 * 1024;

            if ($file['error'] === UPLOAD_ERR_OK) {
                if (!in_array($file['type'], $allowed)) {
                    $errors_profile[] = "Format foto harus JPG atau PNG.";
                } elseif ($file['size'] > $maxSize) {
                    $errors_profile[] = "Ukuran foto maksimal 2MB.";
                } else {
                    $uploadDir = __DIR__ . '/../uploads/profile/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $newName = 'profile_' . $userId . '_' . time() . '.' . $ext;

                    if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
                        if (!empty($foto_profil) && file_exists($uploadDir . $foto_profil)) {
                            @unlink($uploadDir . $foto_profil);
                        }
                        $foto_profil = $newName;
                    } else {
                        $errors_profile[] = "Gagal mengupload foto profil.";
                    }
                }
            } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                $errors_profile[] = "Terjadi kesalahan upload file.";
            }
        }

        if (empty($errors_profile)) {
            $stmtUpdate = $pdo->prepare("
                UPDATE users
                SET name = ?, email = ?, jenis_kelamin = ?, tanggal_lahir = ?, 
                    foto_profil = ?, notif_email = ?, dark_mode = ?
                WHERE id = ?
            ");
            $stmtUpdate->execute([
                $name,
                $email,
                $jenis_kelamin ?: null,
                $tanggal_lahir ?: null,
                $foto_profil,
                $notif_email,
                $dark_mode,
                $userId
            ]);

            $success_profile = "Profil berhasil diperbarui.";

            $_SESSION['name'] = $name;
            $_SESSION['role'] = $user['role'];

            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        }

    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($current === '' || $new === '' || $confirm === '') {
            $errors_password[] = "Semua field password wajib diisi.";
        } else {
            if (!password_verify($current, $user['password'])) {
                $errors_password[] = "Password lama tidak sesuai.";
            }

            if (strlen($new) < 6) {
                $errors_password[] = "Password baru minimal 6 karakter.";
            }

            if ($new !== $confirm) {
                $errors_password[] = "Konfirmasi password baru tidak sama.";
            }
        }

        if (empty($errors_password)) {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $stmtPwd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmtPwd->execute([$hash, $userId]);
            $success_password = "Password berhasil diperbarui.";
        }
    }
}

$stmtLogs = $pdo->prepare("
    SELECT login_time, ip_address, user_agent
    FROM login_logs
    WHERE user_id = ?
    ORDER BY login_time DESC
    LIMIT 10
");
$stmtLogs->execute([$userId]);
$logs = $stmtLogs->fetchAll();

function get_initials($name) {
    $parts = preg_split('/\s+/', trim($name));
    $init = '';
    foreach ($parts as $p) {
        $init .= mb_substr($p, 0, 1);
        if (mb_strlen($init) >= 2) break;
    }
    return mb_strtoupper($init ?: 'AU');
}

$jk = $user['jenis_kelamin'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - EventHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-gray-50 flex h-screen overflow-hidden">

<?php include 'sidebar.php'; ?>

<main class="flex-1 ml-64 flex flex-col h-screen overflow-y-auto">

    <header class="bg-white border-b border-gray-200 px-8 py-4 sticky top-0 z-20">
        <h2 class="text-xl font-bold text-slate-800">Pengaturan</h2>
    </header>

    <div class="p-8">

        <p class="text-slate-500 mb-6">Kelola informasi profil dan preferensi akun Anda.</p>

        <!-- ALERT PROFIL -->
        <?php if (!empty($success_profile)): ?>
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3 rounded-lg">
                <?= htmlspecialchars($success_profile) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors_profile)): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg">
                <ul class="list-disc ml-5">
                    <?php foreach ($errors_profile as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- ALERT PASSWORD -->
        <?php if (!empty($success_password)): ?>
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3 rounded-lg">
                <?= htmlspecialchars($success_password) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors_password)): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg">
                <ul class="list-disc ml-5">
                    <?php foreach ($errors_password as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- TAB -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex space-x-6">
                <button id="tab-profil"
                        class="tab-btn pb-3 px-1 border-b-2 border-emerald-500 text-emerald-600 font-semibold text-sm">
                    Pengaturan Profil
                </button>

                <button id="tab-keamanan"
                        class="tab-btn pb-3 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-gray-300 text-sm">
                    Akun & Keamanan
                </button>
            </nav>
        </div>

        <!-- KONTEN PROFIL -->
        <div id="konten-profil">

            <form method="POST" enctype="multipart/form-data" class="space-y-8">
                <input type="hidden" name="action" value="save_profile">

                <!-- INFORMASI DASAR -->
                <div class="bg-white rounded-xl shadow p-6 border mb-8">
                    <h3 class="text-xl font-semibold mb-4">Informasi Dasar</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1 font-medium text-sm">Nama Lengkap</label>
                            <input type="text" name="name"
                                   value="<?= htmlspecialchars($user['name']) ?>"
                                   class="w-full p-3 rounded-lg border text-sm">
                        </div>

                        <div>
                            <label class="block mb-1 font-medium text-sm">Email</label>
                            <input type="email" name="email"
                                   value="<?= htmlspecialchars($user['email']) ?>"
                                   class="w-full p-3 rounded-lg border text-sm">
                        </div>

                        <div>
                            <label class="block mb-1 font-medium text-sm">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="w-full p-3 rounded-lg border text-sm">
                                <option value="">Pilih</option>
                                <option value="L" <?= $jk === 'L' ? 'selected' : '' ?>>Laki-Laki</option>
                                <option value="P" <?= $jk === 'P' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium text-sm">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir"
                                   value="<?= htmlspecialchars($user['tanggal_lahir'] ?? '') ?>"
                                   class="w-full p-3 rounded-lg border text-sm">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block mb-1 font-medium text-sm">Foto Profil</label>

                        <div class="flex items-center gap-4">
                            <?php
                            $fotoUrl = null;
                            if (!empty($user['foto_profil'])) {
                                $fotoUrl = '../uploads/profile/' . $user['foto_profil'];
                            }
                            ?>
                            <?php if ($fotoUrl && file_exists(__DIR__ . '/../uploads/profile/' . $user['foto_profil'])): ?>
                                <img src="<?= htmlspecialchars($fotoUrl) ?>"
                                     class="w-20 h-20 rounded-full border object-cover">
                            <?php else: ?>
                                <div class="w-20 h-20 rounded-full border bg-slate-200 flex items-center justify-center text-slate-600 font-bold">
                                    <?= htmlspecialchars(get_initials($user['name'])) ?>
                                </div>
                            <?php endif; ?>

                            <div>
                                <input type="file" name="foto_profil" class="block text-sm">
                                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG. Maksimal 2MB.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PREFERENSI -->
                <div class="bg-white rounded-xl shadow p-6 border">
                    <h3 class="text-xl font-semibold mb-4">Preferensi</h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Notifikasi Email</span>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notif_email" class="sr-only peer"
                                    <?= $user['notif_email'] ? 'checked' : '' ?>>
                                <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-blue-600 relative
                                            after:content-[''] after:absolute after:top-0.5 after:left-0.5
                                            after:bg-white after:h-5 after:w-5 after:rounded-full after:transition-all
                                            peer-checked:after:translate-x-5"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Mode Gelap</span>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="dark_mode" class="sr-only peer"
                                    <?= $user['dark_mode'] ? 'checked' : '' ?>>
                                <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-blue-600 relative
                                            after:content-[''] after:absolute after:top-0.5 after:left-0.5
                                            after:bg-white after:h-5 after:w-5 after:rounded-full after:transition-all
                                            peer-checked:after:translate-x-5"></div>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 border-t pt-4">
                        <button type="submit"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- KONTEN KEAMANAN -->
        <div id="konten-keamanan" class="hidden">

            <!-- UBAH PASSWORD -->
            <div class="bg-white p-6 rounded-xl shadow-sm border">
                <h3 class="font-bold text-lg mb-4">Ubah Password</h3>

                <form class="space-y-4" method="POST">
                    <input type="hidden" name="action" value="change_password">

                    <div>
                        <label class="block text-sm font-medium">Password Lama</label>
                        <input type="password" name="current_password"
                               class="mt-1 block w-full border rounded-lg p-2.5 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Password Baru</label>
                        <input type="password" name="new_password"
                               class="mt-1 block w-full border rounded-lg p-2.5 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password"
                               class="mt-1 block w-full border rounded-lg p-2.5 text-sm">
                    </div>

                    <div class="flex justify-end pt-4 border-t">
                        <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm">
                            Simpan Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- RIWAYAT LOGIN -->
            <div class="bg-white p-6 rounded-xl shadow-sm border mt-8">
                <h3 class="font-bold text-lg mb-4">Riwayat Login</h3>

                <table class="w-full text-sm">
                    <thead>
                    <tr class="text-left text-slate-600">
                        <th class="pb-2">Tanggal</th>
                        <th class="pb-2">IP Address</th>
                        <th class="pb-2">Perangkat</th>
                    </tr>
                    </thead>

                    <tbody class="text-slate-700">
                    <?php if (!$logs): ?>
                        <tr class="border-t">
                            <td colspan="3" class="py-3 text-center text-slate-500">
                                Belum ada data riwayat login.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr class="border-t">
                                <td class="py-2">
                                    <?= htmlspecialchars($log['login_time']) ?>
                                </td>
                                <td><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($log['user_agent'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- SCRIPT TAB -->
<script>
const tabProfil = document.getElementById("tab-profil");
const tabKeamanan = document.getElementById("tab-keamanan");
const kontenProfil = document.getElementById("konten-profil");
const kontenKeamanan = document.getElementById("konten-keamanan");

function aktifkanTab(tab) {
    document.querySelectorAll(".tab-btn").forEach(btn => {
        btn.classList.remove("text-emerald-600", "border-emerald-500", "font-semibold");
        btn.classList.add("text-slate-500", "border-transparent");
    });
    tab.classList.add("text-emerald-600", "border-emerald-500", "font-semibold");
}

tabProfil.addEventListener("click", () => {
    aktifkanTab(tabProfil);
    kontenProfil.classList.remove("hidden");
    kontenKeamanan.classList.add("hidden");
});

tabKeamanan.addEventListener("click", () => {
    aktifkanTab(tabKeamanan);
    kontenProfil.classList.add("hidden");
    kontenKeamanan.classList.remove("hidden");
});
</script>

</body>
</html>