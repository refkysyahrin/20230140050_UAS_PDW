<?php
// Pastikan session sudah dimulai di awal file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login, arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit(); // Pastikan untuk keluar setelah redirect
}

/**
 * Variabel untuk menandai halaman aktif.
 * Seharusnya di-set di setiap halaman yang meng-include file ini.
 * Contoh: <?php $activePage = 'dashboard'; ?>
 */
$activePage = $activePage ?? ''; // Default ke string kosong jika tidak di-set

/**
 * Variabel untuk judul halaman.
 * Contoh: <?php $pageTitle = 'Dashboard Utama'; ?>
 */
$pageTitle = $pageTitle ?? 'Dashboard'; // Default title

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Mahasiswa - <?php echo htmlspecialchars($pageTitle); ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-gray-100 font-sans">

    <nav class="bg-blue-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-white text-2xl font-bold">SIMPRAK</span>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <?php 
                                $activeClass = 'bg-blue-700 text-white';
                                $inactiveClass = 'text-gray-200 hover:bg-blue-700 hover:text-white';
                            ?>
                            <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?> px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                            <a href="my_courses.php" class="<?php echo ($activePage == 'my_courses') ? $activeClass : $inactiveClass; ?> px-3 py-2 rounded-md text-sm font-medium">Praktikum Saya</a>
                            <a href="courses.php" class="<?php echo ($activePage == 'courses') ? $activeClass : $inactiveClass; ?> px-3 py-2 rounded-md text-sm font-medium">Cari Praktikum</a>
                        </div>
                    </div>
                </div>

                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md transition-colors duration-300">
                            Logout
                        </a>
                    </div>
                </div>

                <div class="-mr-2 flex md:hidden">
                    <button id="mobile-menu-button" class="bg-blue-600 inline-flex items-center justify-center p-2 rounded-md text-gray-200 hover:text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-600 focus:ring-white">
                        <span class="sr-only">Buka menu utama</span>
                        <svg class="h-6 w-6 block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="h-6 w-6 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?> block px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
                <a href="my_courses.php" class="<?php echo ($activePage == 'my_courses') ? $activeClass : $inactiveClass; ?> block px-3 py-2 rounded-md text-base font-medium">Praktikum Saya</a>
                <a href="courses.php" class="<?php echo ($activePage == 'courses') ? $activeClass : $inactiveClass; ?> block px-3 py-2 rounded-md text-base font-medium">Cari Praktikum</a>
            </div>
            <div class="pt-4 pb-3 border-t border-blue-700">
                <div class="flex items-center px-5">
                    <a href="../logout.php" class="w-full text-left bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-3 rounded-md transition-colors duration-300">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mx-auto p-6 lg:p-8">