<?php
session_start();
require_once __DIR__ . '/../database/db.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$errors = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $errors = 'Email atau password salah.';
    } else {

        // simpan session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['name']    = $user['name'];

        // catat riwayat login
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $log = $pdo->prepare("
            INSERT INTO login_logs (user_id, ip_address, user_agent)
            VALUES (?, ?, ?)
        ");
        $log->execute([$user['id'], $ip, $ua]);

        // redirect ke dashboard sesuai role
        if ($user['role'] === 'admin') {
            header('Location: ../admin/dashboard.php');
        } else {
            header('Location: index.php');
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EventHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-gradient-to-r from-indigo-100 via-purple-200 to-pink-200">

<div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="bg-indigo-700 p-6 text-center">
        <h1 class="text-3xl font-extrabold text-white flex items-center justify-center gap-2">
            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                </path>
            </svg>
            EventHub
        </h1>
        <p class="text-slate-300 text-sm mt-1">Manajemen Event Terpadu</p>
    </div>

    <div class="p-8">
        <h2 class="text-2xl font-semibold text-slate-800 text-center mb-6">Masuk ke Akun</h2>

        <?php if ($errors): ?>
            <div class="mb-4 text-sm text-red-600 bg-red-50 border border-red-200 px-4 py-2 rounded-lg">
                <?= htmlspecialchars($errors) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="block text-slate-600 text-sm font-medium mb-2">Email Address</label>
                <input type="email" name="email"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 transition duration-200"
                       placeholder="nama@email.com" required>
            </div>

            <div class="mb-4">
                <label class="block text-slate-600 text-sm font-medium mb-2">Password</label>
                <input type="password" name="password"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 transition duration-200"
                       placeholder="••••••••" required>
            </div>

            <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg transition duration-200">
                Masuk Sekarang
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-500">
            Belum punya akun?
            <a href="register.php" class="text-emerald-600 font-semibold hover:underline">
                Daftar disini
            </a>
        </div>
    </div>
</div>

</body>
</html>
