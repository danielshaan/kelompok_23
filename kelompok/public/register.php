<?php
session_start();
require_once __DIR__ . '/../database/db.php';

$errors = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($password !== $confirm) {
        $errors = 'Konfirmasi password tidak cocok.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors = 'Email sudah terdaftar.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?, 'anggota')");
            $stmt->execute([$name, $email, $hash]);
            $success = 'Registrasi berhasil, silakan login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EventHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">

<div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="bg-slate-900 p-6 text-center text-white font-bold text-xl">
        Daftar Akun Baru
    </div>

    <div class="p-8">
        <?php if ($errors): ?>
            <div class="mb-4 text-sm text-red-600 bg-red-50 border border-red-200 px-4 py-2 rounded-lg">
                <?= htmlspecialchars($errors) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-4 text-sm text-emerald-600 bg-emerald-50 border border-emerald-200 px-4 py-2 rounded-lg">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1 text-slate-700">Nama Lengkap</label>
                <input type="text" name="name" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1 text-slate-700">Email</label>
                <input type="email" name="email" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1 text-slate-700">Password</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-1 text-slate-700">Konfirmasi Password</label>
                <input type="password" name="confirm" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500">
            </div>

            <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-lg">
                Daftar
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-500">
            Sudah punya akun?
            <a href="login.php" class="text-emerald-600 font-semibold hover:underline">Login</a>
        </div>
    </div>
</div>

</body>
</html>
