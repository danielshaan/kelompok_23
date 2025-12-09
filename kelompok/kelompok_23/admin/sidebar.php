<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-slate-900 text-white flex flex-col fixed h-full transition-all duration-300 z-10">
    <div class="p-6 flex items-center gap-3">
        <div class="bg-emerald-500 p-1.5 rounded-lg">
            <i class="fa-solid fa-calendar-days text-xl text-white"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold tracking-wide">EventHub</h1>
            <p class="text-xs text-slate-400">Manajemen Event</p>
        </div>
    </div>

    <nav class="flex-1 px-4 space-y-2 mt-4">
        <p class="text-xs text-slate-500 uppercase font-semibold px-2 mb-2">Menu Utama</p>

        <a href="dashboard.php"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition
           text-slate-300 hover:bg-slate-800 hover:text-white
           <?= $current === 'dashboard.php' ? 'bg-slate-800 text-emerald-400' : '' ?>">
            <i class="fa-solid fa-grid-2 w-5 text-center"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <a href="events.php"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition
           text-slate-300 hover:bg-slate-800 hover:text-white
           <?= $current === 'events.php' ? 'bg-slate-800 text-emerald-400' : '' ?>">
            <i class="fa-solid fa-calendar-check w-5 text-center"></i>
            <span class="font-medium">Events</span>
        </a>

        <a href="pendaftaran.php"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition
           text-slate-300 hover:bg-slate-800 hover:text-white
           <?= $current === 'pendaftaran.php' ? 'bg-slate-800 text-emerald-400' : '' ?>">
            <i class="fa-solid fa-clipboard-list w-5 text-center"></i>
            <span class="font-medium">Pendaftaran</span>
        </a>

        <a href="peserta.php"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition
           text-slate-300 hover:bg-slate-800 hover:text-white
           <?= $current === 'peserta.php' ? 'bg-slate-800 text-emerald-400' : '' ?>">
            <i class="fa-solid fa-users w-5 text-center"></i>
            <span class="font-medium">Peserta</span>
        </a>

        <a href="laporan.php"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition
           text-slate-300 hover:bg-slate-800 hover:text-white
           <?= $current === 'laporan.php' ? 'bg-slate-800 text-emerald-400' : '' ?>">
            <i class="fa-solid fa-chart-simple w-5 text-center"></i>
            <span class="font-medium">Laporan</span>
        </a>

        <p class="text-xs text-slate-500 uppercase font-semibold px-2 mt-6 mb-2">Lainnya</p>

        <a href="settings.php"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition
           text-slate-300 hover:bg-slate-800 hover:text-white
           <?= $current === 'settings.php' ? 'bg-slate-800 text-emerald-400' : '' ?>">
            <i class="fa-solid fa-gear w-5 text-center"></i>
            <span class="font-medium">Pengaturan</span>
        </a>
    </nav>

    <div class="p-4 border-t border-slate-800">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-white font-bold">
                AU
            </div>
            <div>
                <p class="text-sm font-semibold">Admin User</p>
                <p class="text-xs text-slate-400">admin@eventhub.id</p>
            </div>
            <a href="../public/logout.php" class="ml-auto text-slate-400 hover:text-white">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </div>
</aside>