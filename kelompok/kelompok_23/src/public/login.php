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

        // mencatat riwayat login
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $log = $pdo->prepare("
            INSERT INTO login_logs (user_id, ip_address, user_agent)
            VALUES (?, ?, ?)
        ");
        $log->execute([$user['id'], $ip, $ua]);

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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        
        body { 
            font-family: 'Inter', sans-serif;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(3deg); }
        }
        
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .animate-float { 
            animation: float 6s ease-in-out infinite; 
        }
        
        .animate-gradient { 
            background: linear-gradient(270deg, #1e40af, #1e3a8a, #3b82f6, #1e40af);
            background-size: 800% 800%;
            animation: gradient 15s ease infinite;
        }
        
        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .animate-slideInRight {
            animation: slideInRight 0.6s ease-out forwards;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .gradient-border {
            position: relative;
            background: white;
            border-radius: 1.5rem;
        }
        
        .gradient-border::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 1.5rem;
            padding: 2px;
            background: linear-gradient(135deg, #1e40af, #1e3a8a, #3b82f6);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
        }
        
        .input-focus {
            transition: all 0.3s ease;
        }
        
        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(30, 64, 175, 0.15);
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }
        
        .btn-gradient:hover::before {
            left: 100%;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(30, 64, 175, 0.4);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-pattern {
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(30, 64, 175, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(30, 58, 138, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(59, 130, 246, 0.06) 0%, transparent 50%);
        }
        
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 hero-pattern relative overflow-hidden">

<div class="absolute inset-0 overflow-hidden pointer-events-none">
    <div class="absolute top-20 left-20 w-96 h-96 bg-gradient-to-r from-blue-300/15 to-indigo-300/15 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-20 right-20 w-[500px] h-[500px] bg-gradient-to-r from-blue-400/15 to-blue-600/15 rounded-full blur-3xl animate-float" style="animation-delay:1.5s"></div>
</div>

<!-- Back to Home Button -->
<a href="index.php" class="absolute top-8 left-8 z-10 glass-effect px-6 py-3 rounded-xl text-gray-700 font-semibold hover:shadow-xl transition duration-300 flex items-center gap-2 animate-fadeInUp">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Kembali
</a>

<div class="w-full max-w-md mx-4 relative z-10">
    <!-- Login Card -->
    <div class="glass-effect rounded-2xl shadow-2xl overflow-hidden gradient-border animate-fadeInUp delay-200">
        
        <!-- Header Section -->
        <div class="relative bg-gradient-to-br from-blue-800 via-blue-900 to-blue-950 p-8 text-center overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 left-0 w-32 h-32 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 right-0 w-48 h-48 bg-white rounded-full translate-x-1/3 translate-y-1/3"></div>
            </div>
            
            <div class="relative z-10">
                <div class="inline-flex items-center justify-center gap-3 mb-2">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center shadow-xl">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                </div>
                <h1 class="text-3xl font-black text-white mb-1">EventHub</h1>
                <p class="text-blue-200 text-xs font-medium">Platform Manajemen Event Modern</p>
            </div>
        </div>

        <!-- Form Section -->
        <div class="p-8">
            <div class="text-center mb-6 animate-slideInRight delay-300">
                <h2 class="text-2xl font-black text-gray-900 mb-1">Selamat Datang! 👋</h2>
                <p class="text-gray-600 text-sm">Masuk untuk melanjutkan ke dashboard</p>
            </div>

            <?php if ($errors): ?>
                <div class="mb-5 text-sm text-red-700 bg-gradient-to-r from-red-50 to-pink-50 border-2 border-red-200 px-4 py-3 rounded-xl flex items-center gap-3 shadow-lg animate-fadeInUp">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-semibold"><?= htmlspecialchars($errors) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div class="animate-slideInRight delay-400">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input type="email" name="email"
                               class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-400 transition duration-300 input-focus bg-gray-50 font-medium text-sm"
                               placeholder="nama@email.com" required>
                    </div>
                </div>

                <div class="animate-slideInRight delay-500">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input type="password" name="password"
                               class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-400 transition duration-300 input-focus bg-gray-50 font-medium text-sm"
                               placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit"
                        class="w-full btn-gradient text-white font-bold py-3.5 rounded-xl shadow-lg hover:shadow-2xl transition duration-300 flex items-center justify-center gap-2 animate-slideInRight delay-600">
                    Masuk Sekarang
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </form>

            <div class="mt-6 text-center animate-fadeInUp delay-700">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="px-3 bg-white text-gray-500 font-medium">atau</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 text-center text-sm animate-fadeInUp delay-800">
                <span class="text-gray-600">Belum punya akun?</span>
                <a href="register.php" class="gradient-text font-bold hover:underline ml-1">
                    Daftar disini →
                </a>
            </div>
        </div>
    </div>

    <div class="mt-6 text-center text-xs text-gray-600 animate-fadeInUp delay-900">
        <p>Dengan masuk, Anda menyetujui <a href="#" class="gradient-text font-semibold hover:underline">Syarat & Ketentuan</a> kami</p>
    </div>
</div>

</body>
</html>