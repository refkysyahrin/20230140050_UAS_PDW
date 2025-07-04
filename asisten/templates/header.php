<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login atau bukan asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Asisten - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="flex h-screen bg-gray-50">
    <aside class="w-64 bg-gray-800 text-white flex flex-col fixed h-full">
        <div class="px-6 py-4 text-center border-b border-gray-700">
            <h3 class="text-xl font-bold">PANEL ASISTEN</h3>
            <p class="text-sm text-gray-400 mt-1"><?php echo htmlspecialchars($_SESSION['nama']); ?></p>
        </div>
        <nav class="flex-grow p-4">
            <ul class="space-y-2">
                <?php
                    $activeClass = 'bg-gray-900 text-white';
                    $inactiveClass = 'text-gray-400 hover:bg-gray-700 hover:text-white';
                    
                    // Definisikan menu dalam array untuk kemudahan pengelolaan
                    $menus = [
                        ['page' => 'dashboard', 'title' => 'Dashboard', 'icon' => '<svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>'],
                        ['page' => 'mata_praktikum', 'title' => 'Mata Praktikum', 'icon' => '<svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>'],
                        ['page' => 'modul', 'title' => 'Manajemen Modul', 'icon' => '<svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>'],
                        ['page' => 'laporan', 'title' => 'Laporan Masuk', 'icon' => '<svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>'],
                        ['page' => 'pengguna', 'title' => 'Manajemen Pengguna', 'icon' => '<svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>']
                    ];
                ?>
                <?php foreach ($menus as $menu): ?>
                <li>
                    <a href="<?php echo $menu['page']; ?>.php" 
                       class="<?php echo ($activePage == $menu['page']) ? $activeClass : $inactiveClass; ?> flex items-center px-4 py-3 rounded-lg transition-colors duration-200">
                        <?php echo $menu['icon']; ?>
                        <span><?php echo $menu['title']; ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <div class="p-4 mt-auto">
             <a href="../logout.php" class="flex items-center justify-center bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300 w-full">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <main class="flex-1 p-6 lg:p-10 ml-64">
        <header class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-800 capitalize"><?php echo $pageTitle ?? str_replace('_', ' ', $activePage); ?></h1>
        </header>