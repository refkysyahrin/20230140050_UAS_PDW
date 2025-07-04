<?php
require_once '../config.php';
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header.php';

$id_asisten = $_SESSION['user_id'];

// --- FUNGSI BANTU UNTUK WAKTU ---
function time_ago($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);

    if ($seconds <= 60) return "Baru saja";
    else if ($minutes <= 60) return ($minutes == 1) ? "1 menit lalu" : "$minutes menit lalu";
    else if ($hours <= 24) return ($hours == 1) ? "1 jam lalu" : "$hours jam lalu";
    else if ($days <= 7) return ($days == 1) ? "1 hari lalu" : "$days hari lalu";
    else if ($weeks <= 4.3) return ($weeks == 1) ? "1 minggu lalu" : "$weeks minggu lalu";
    else if ($months <= 12) return ($months == 1) ? "1 bulan lalu" : "$months bulan lalu";
    else return ($years == 1) ? "1 tahun lalu" : "$years tahun lalu";
}

// --- PENGAMBILAN DATA STATISTIK ---
// (Sama seperti implementasi sebelumnya, mengambil data dinamis)
$total_praktikum_stmt = $conn->prepare("SELECT count(id) as total FROM mata_praktikum WHERE id_asisten = ?");
$total_praktikum_stmt->bind_param("i", $id_asisten);
$total_praktikum_stmt->execute();
$total_praktikum = $total_praktikum_stmt->get_result()->fetch_assoc()['total'];

$laporan_stmt = $conn->prepare("SELECT COUNT(l.id) as total_laporan, SUM(CASE WHEN l.nilai IS NULL THEN 1 ELSE 0 END) as belum_dinilai FROM laporan l JOIN modul m ON l.id_modul = m.id JOIN mata_praktikum mp ON m.id_praktikum = mp.id WHERE mp.id_asisten = ?");
$laporan_stmt->bind_param("i", $id_asisten);
$laporan_stmt->execute();
$laporan_data = $laporan_stmt->get_result()->fetch_assoc();
$total_laporan = $laporan_data['total_laporan'] ?? 0;
$belum_dinilai = $laporan_data['belum_dinilai'] ?? 0;

// --- PENGAMBILAN DATA UNTUK FEED AKTIVITAS ---
$aktivitas_terbaru = [];

// 1. Ambil 5 Laporan Masuk Terbaru
$laporan_terbaru_stmt = $conn->prepare("SELECT 'laporan_masuk' as tipe, u.nama, m.nama_modul, l.tanggal_kumpul as waktu FROM laporan l JOIN users u ON l.id_mahasiswa = u.id JOIN modul m ON l.id_modul = m.id JOIN mata_praktikum mp ON m.id_praktikum = mp.id WHERE mp.id_asisten = ? ORDER BY l.tanggal_kumpul DESC LIMIT 5");
$laporan_terbaru_stmt->bind_param("i", $id_asisten);
$laporan_terbaru_stmt->execute();
$result_laporan = $laporan_terbaru_stmt->get_result();
while($row = $result_laporan->fetch_assoc()) {
    $aktivitas_terbaru[] = $row;
}

// 2. Ambil 5 Pendaftar Baru Terbaru
$pendaftar_terbaru_stmt = $conn->prepare("SELECT 'pendaftar_baru' as tipe, u.nama, mp.nama_praktikum, pp.tanggal_daftar as waktu FROM pendaftaran_praktikum pp JOIN users u ON pp.id_mahasiswa = u.id JOIN mata_praktikum mp ON pp.id_praktikum = mp.id WHERE mp.id_asisten = ? ORDER BY pp.tanggal_daftar DESC LIMIT 5");
$pendaftar_terbaru_stmt->bind_param("i", $id_asisten);
$pendaftar_terbaru_stmt->execute();
$result_pendaftar = $pendaftar_terbaru_stmt->get_result();
while($row = $result_pendaftar->fetch_assoc()) {
    $aktivitas_terbaru[] = $row;
}

// Urutkan semua aktivitas berdasarkan waktu
usort($aktivitas_terbaru, function($a, $b) {
    return strtotime($b['waktu']) - strtotime($a['waktu']);
});

// Ambil 5 teratas setelah digabung
$aktivitas_terbaru = array_slice($aktivitas_terbaru, 0, 5);
?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl shadow-lg flex items-center space-x-4">
        <div>
            <p class="text-sm text-gray-500">Total Praktikum Diampu</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo $total_praktikum; ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-lg flex items-center space-x-4">
        <div>
            <p class="text-sm text-gray-500">Total Laporan Masuk</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo $total_laporan; ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-lg flex items-center space-x-4">
        <div>
            <p class="text-sm text-gray-500">Laporan Belum Dinilai</p>
            <p class="text-3xl font-bold text-yellow-500"><?php echo $belum_dinilai; ?></p>
        </div>
    </div>
</div>

<div class="bg-white p-8 rounded-2xl shadow-lg">
    <h3 class="text-2xl font-bold text-gray-800 mb-6">Notifikasi & Aktivitas Terbaru</h3>
    <div class="space-y-6">
        <?php if (!empty($aktivitas_terbaru)): ?>
            <?php foreach($aktivitas_terbaru as $aktivitas): ?>
                <div class="flex items-start">
                    <?php if ($aktivitas['tipe'] == 'laporan_masuk'): ?>
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <div>
                            <p class="text-gray-800">
                                <strong class="font-semibold"><?php echo htmlspecialchars($aktivitas['nama']); ?></strong> baru saja mengumpulkan laporan untuk modul
                                <strong class="font-semibold"><?php echo htmlspecialchars($aktivitas['nama_modul']); ?></strong>.
                            </p>
                            <p class="text-sm text-gray-500 mt-1"><?php echo time_ago($aktivitas['waktu']); ?></p>
                        </div>
                    <?php elseif ($aktivitas['tipe'] == 'pendaftar_baru'): ?>
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <div>
                            <p class="text-gray-800">
                                <strong class="font-semibold"><?php echo htmlspecialchars($aktivitas['nama']); ?></strong> telah mendaftar ke praktikum
                                <strong class="font-semibold"><?php echo htmlspecialchars($aktivitas['nama_praktikum']); ?></strong>.
                            </p>
                            <p class="text-sm text-gray-500 mt-1"><?php echo time_ago($aktivitas['waktu']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-10">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak Ada Aktivitas Baru</h3>
                <p class="mt-1 text-sm text-gray-500">Semua aktivitas terbaru akan ditampilkan di sini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>