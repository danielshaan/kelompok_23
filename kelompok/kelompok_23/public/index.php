<?php
session_start();
require_once __DIR__ . '/../database/db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$role       = $_SESSION['role'] ?? 'guest';
$name       = $_SESSION['name'] ?? '';

$stmt = $pdo->query("SELECT * FROM events ORDER BY tanggal ASC LIMIT 6");
$events = $stmt->fetchAll();

// Pesan setelah daftar event
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub - Temukan Event Menarik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
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
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-gradient { 
            background: linear-gradient(270deg, #003366, #00509d, #0074d9);
            background-size: 600% 600%;
            animation: gradient 15s ease infinite;
        }
        .glass-white {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        .glass-dark {
            background: rgba(17, 28, 61, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(17, 28, 61, 0.1);
        }
        .card-hover { 
            transition: all .4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .card-hover::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        .card-hover:hover::before {
            left: 100%;
        }
        .card-hover:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.25);
        }
        .gradient-text {
            background: linear-gradient(135deg, #003366 0%, #00509d 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .btn-gradient {
            background: linear-gradient(135deg, #003366 0%, #00509d 100%);
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
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        .feature-icon {
            transition: all 0.3s ease;
        }
        .glass-white:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }
        .hero-pattern {
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(240, 147, 251, 0.06) 0%, transparent 50%);
        }
        .event-thumbnail {
            transition: transform 0.5s ease;
        }
        .card-hover:hover .event-thumbnail {
            transform: scale(1.1);
        }
        .stats-card {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border: 2px solid transparent;
            background-clip: padding-box;
            position: relative;
        }
        .stats-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 2px;
            background: linear-gradient(135deg, #003366, #00509d);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
        }
        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        .delay-500 { animation-delay: 0.5s; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 font-sans">

<nav class="fixed w-full top-0 z-50 glass-white border-b border-white/20 shadow-xl">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-center py-5">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                <div class="w-11 h-11 btn-gradient rounded-xl flex items-center justify-center shadow-xl">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <span class="gradient-text">EventHub</span>
            </h1>

            <div class="flex gap-3">
                <?php if (!$isLoggedIn): ?>
                    <a href="login.php"
                       class="px-6 py-2.5 text-gray-700 font-semibold rounded-xl hover:bg-white/60 transition duration-300">
                        Login
                    </a>
                    <a href="register.php"
                       class="px-6 py-2.5 btn-gradient text-white font-semibold rounded-xl shadow-lg">
                        Daftar
                    </a>
                <?php else: ?>
                    <span class="px-4 py-2.5 text-gray-700 text-sm bg-white/50 rounded-xl">
                        Hai, <span class="font-bold gradient-text"><?= htmlspecialchars($name) ?></span>
                        <span class="text-xs text-gray-500">(<?= htmlspecialchars($role) ?>)</span>
                    </span>
                    <?php if ($role === 'admin'): ?>
                        <a href="../admin/dashboard.php"
                           class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-xl hover:shadow-xl transition duration-300 text-sm font-semibold">
                            Admin Panel
                        </a>
                    <?php endif; ?>
                    <a href="logout.php"
                       class="px-5 py-2.5 text-gray-700 font-semibold rounded-xl hover:bg-white/60 transition duration-300 text-sm">
                        Logout
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section with Dark Blue Background and Animations -->
<section class="relative min-h-screen flex items-center justify-center px-4 pt-32 pb-20 overflow-hidden hero-pattern">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-20 left-20 w-96 h-96 bg-gradient-to-r from-purple-300/20 to-pink-300/20 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-20 right-20 w-[500px] h-[500px] bg-gradient-to-r from-blue-300/20 to-indigo-300/20 rounded-full blur-3xl animate-float"
             style="animation-delay:1.5s"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-r from-violet-300/10 to-purple-300/10 rounded-full blur-3xl animate-float"
             style="animation-delay:3s"></div>
    </div>

    <div class="relative z-10 text-center max-w-5xl mx-auto">
        <div class="inline-block mb-6 px-6 py-2 glass-white rounded-full text-sm font-semibold text-purple-700 shadow-lg animate-fadeInUp">
            🎉 Platform Event Terpercaya & Modern
        </div>
        
        <h2 class="text-6xl md:text-8xl font-black mt-6 mb-6 animate-fadeInUp delay-100">
            <span class="gradient-text">Temukan & Ikuti</span><br/>
            <span class="text-gray-800">Event Terbaik</span>
        </h2>
        
        <p class="text-xl md:text-2xl text-gray-600 max-w-3xl mx-auto leading-relaxed mb-10 animate-fadeInUp delay-200">
            EventHub membantu menemukan, mendaftar, dan mengelola event kampus, organisasi, maupun komunitas dengan lebih mudah dan modern.
        </p>

        <div class="flex flex-col sm:flex-row justify-center gap-5 animate-fadeInUp delay-300">
            <a href="#events"
               class="group px-10 py-4 btn-gradient text-white rounded-2xl font-bold shadow-2xl hover:shadow-purple-500/50 transition duration-300 flex items-center justify-center gap-2">
                Jelajahi Event
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
            <a href="#features"
               class="px-10 py-4 glass-white text-gray-700 font-bold rounded-2xl hover:shadow-xl transition duration-300">
                Pelajari Lebih Lanjut
            </a>
        </div>

        <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto animate-fadeInUp delay-400">
            <div class="stats-card rounded-2xl p-8 backdrop-blur-sm hover:scale-105 transition-transform duration-300">
                <h3 class="text-5xl font-black gradient-text mb-2">500+</h3>
                <p class="text-gray-600 font-semibold">Event Tersedia</p>
            </div>
            <div class="stats-card rounded-2xl p-8 backdrop-blur-sm hover:scale-105 transition-transform duration-300">
                <h3 class="text-5xl font-black gradient-text mb-2">10K+</h3>
                <p class="text-gray-600 font-semibold">Peserta Aktif</p>
            </div>
            <div class="stats-card rounded-2xl p-8 backdrop-blur-sm hover:scale-105 transition-transform duration-300">
                <h3 class="text-5xl font-black gradient-text mb-2">50+</h3>
                <p class="text-gray-600 font-semibold">Organisasi Partner</p>
            </div>
        </div>
    </div>
</section>

<!-- Event Section -->
<section id="events" class="py-24 px-4 hero-pattern scroll-mt-20">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <div class="inline-block mb-4 px-5 py-2 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-full text-sm font-bold text-indigo-700">
                Event Pilihan
            </div>
            <h3 class="text-5xl md:text-6xl font-black text-gray-900 mb-4">Event <span class="gradient-text">Terbaru</span></h3>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">Ikuti berbagai event menarik dari komunitas dan organisasi terpercaya</p>
        </div>

        <?php if ($msg === 'daftar_sukses'): ?>
            <div class="max-w-3xl mx-auto mb-8 bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-200 text-emerald-700 px-6 py-4 rounded-2xl shadow-lg flex items-center gap-3">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-semibold">Pendaftaran event berhasil! Silakan menunggu konfirmasi dari admin.</span>
            </div>
        <?php elseif ($msg === 'sudah_daftar'): ?>
            <div class="max-w-3xl mx-auto mb-8 bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-200 text-yellow-700 px-6 py-4 rounded-2xl shadow-lg flex items-center gap-3">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span class="font-semibold">Anda sudah terdaftar pada event ini.</span>
            </div>
        <?php endif; ?>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (!$events): ?>
                <div class="col-span-3 text-center py-20">
                    <div class="inline-block p-6 bg-gray-100 rounded-full mb-4">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-lg">Belum ada event yang tersedia saat ini.</p>
                </div>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <div class="glass-white rounded-3xl overflow-hidden shadow-xl card-hover">
                        <div class="h-48 relative overflow-hidden bg-gradient-to-br from-purple-500 to-indigo-600">
                            <?php if (!empty($event['thumbnail'])): ?>
                                <img src="../uploads/event_images/<?= htmlspecialchars($event['thumbnail']) ?>"
                                    class="w-full h-full object-cover event-thumbnail">
                            <?php else: ?>
                                <div class="w-full h-full btn-gradient flex items-center justify-center">
                                    <svg class="w-16 h-16 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            <?php endif; ?>

                            <div class="absolute bottom-4 left-4">
                                <span class="px-4 py-1.5 bg-white/95 backdrop-blur-sm text-purple-700 rounded-full text-xs font-bold shadow-lg">
                                    <?= htmlspecialchars($event['kategori'] ?: 'Event') ?>
                                </span>
                            </div>
                        </div>

                        <div class="p-7">
                            <h4 class="text-2xl font-black text-gray-900 mb-3 leading-tight">
                                <a href="detail_event.php?id=<?= $event['id'] ?>" 
                                class="hover:text-purple-600 transition duration-300">
                                <?= htmlspecialchars($event['title']) ?>
                                </a>
                            </h4>
                            <p class="text-gray-600 mb-5 leading-relaxed">
                                <?= htmlspecialchars(mb_strimwidth($event['description'] ?? '', 0, 100, '...')) ?>
                            </p>

                            <div class="flex items-center gap-2 text-gray-500 text-sm mb-6 bg-gray-50 rounded-xl p-3">
                                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="font-semibold"><?= date('d M Y', strtotime($event['tanggal'])) ?> • <?= substr($event['waktu'],0,5) ?></span>
                            </div>

                            <?php if (!$isLoggedIn): ?>
                                <button onclick="window.location.href='login.php'"
                                        class="w-full btn-gradient text-white py-3.5 rounded-xl font-bold shadow-lg hover:shadow-2xl transition duration-300">
                                    Login untuk Mendaftar
                                </button>
                            <?php elseif ($role === 'anggota'): ?>
                                <form action="daftar_event.php" method="POST">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <button type="submit"
                                            class="w-full btn-gradient text-white py-3.5 rounded-xl font-bold shadow-lg hover:shadow-2xl transition duration-300">
                                        Daftar Sekarang
                                    </button>
                                </form>
                            <?php else: ?>
                                <button disabled
                                        class="w-full bg-gray-200 text-gray-500 py-3.5 rounded-xl font-bold cursor-not-allowed">
                                    Admin tidak dapat mendaftar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Footer Section -->
<section class="py-24 px-4">
    <div class="max-w-5xl mx-auto">
        <div class="glass-white p-12 md:p-16 rounded-[2rem] text-center shadow-2xl card-hover relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 btn-gradient"></div>
            
            <div class="inline-block p-4 bg-gradient-to-br from-purple-100 to-pink-100 rounded-2xl mb-6">
                <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            
            <h3 class="text-4xl md:text-5xl font-black text-gray-900 mb-4">Siap Memulai <span class="gradient-text">Perjalananmu?</span></h3>
            <p class="text-gray-600 text-lg mb-10 max-w-2xl mx-auto leading-relaxed">Temukan berbagai event menarik dan daftar dalam hitungan detik. Jangan lewatkan kesempatan emas ini!</p>

            <a href="<?= $isLoggedIn ? '#events' : 'register.php' ?>"
               class="inline-flex items-center gap-3 px-12 py-5 btn-gradient text-white font-black rounded-2xl shadow-2xl hover:shadow-purple-500/50 transition duration-300 text-lg">
                <?= $isLoggedIn ? 'Lihat Event' : 'Daftar Sekarang' ?>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </div>
</section>

<footer class="py-16 border-t border-gray-200 bg-gradient-to-br from-slate-50 to-gray-100">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-10 mb-12">
            <div>
                <h4 class="text-gray-900 font-black text-xl mb-4 flex items-center gap-3">
                    <div class="w-10 h-10 btn-gradient rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="gradient-text">EventHub</span>
                </h4>

                <div class="text-gray-600 font-semibold">
                    <p>&copy; 2025 EventHub. All rights reserved.</p>
                </div>
            </div>

            <div>
                <h4 class="text-gray-900 font-black text-xl mb-4">Quick Links</h4>
                <ul class="text-gray-600">
                    <li><a href="#" class="hover:text-purple-600">Home</a></li>
                    <li><a href="#" class="hover:text-purple-600">About Us</a></li>
                    <li><a href="#" class="hover:text-purple-600">Events</a></li>
                    <li><a href="#" class="hover:text-purple-600">Contact</a></li>
                </ul>
            </div>

            <div class="col-span-2">
                <h4 class="text-gray-900 font-black text-xl mb-4">Follow Us</h4>
                <div class="flex gap-4">
                    <a href="#" class="w-10 h-10 bg-gradient-to-br from-purple-100 to-pink-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="w-10 h-10 bg-gradient-to-br from-purple-100 to-pink-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    </a>
                    <a href="#" class="w-10 h-10 bg-gradient-to-br from-purple-100 to-pink-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 00-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="pt-8 border-t-2 border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-center md:text-left text-gray-600 text-sm">
                    <p>&copy; 2025 EventHub. All rights reserved.</p>
                </div>
                <div class="flex items-center gap-6 text-sm text-gray-500">
                    <a href="#" class="hover:text-purple-600 transition duration-300">Privasi</a>
                    <span>•</span>
                    <a href="#" class="hover:text-purple-600 transition duration-300">Kebijakan</a>
                    <span>•</span>
                    <a href="#" class="hover:text-purple-600 transition duration-300">Sitemap</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Additional Information for Group Members -->
<section class="py-8 bg-gray-100">
    <div class="max-w-7xl mx-auto text-center">
        <p class="text-gray-600 text-sm">
            Tugas Besar Praktikum Pemrograman Web 2025 - <span class="font-bold text-gray-900">EventHub</span><br>
            Kelompok 23
        </p>
    </div>
</section>

</body>
</html>
