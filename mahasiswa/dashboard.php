<?php
require_once '../config.php';
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header_mahasiswa.php';

$id_mahasiswa = $_SESSION['user_id'];

// --- FUNGSI BANTU UNTUK FORMAT WAKTU ---
function time_ago($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);

    if ($seconds <= 60) return "Baru saja";
    else if ($minutes <= 60) return ($minutes == 1) ? "1 menit lalu" : "$minutes menit lalu";
    else if ($hours <= 24) return ($hours == 1) ? "1 jam lalu" : "$hours jam lalu";
    else return ($days == 1) ? "kemarin" : "$days hari lalu";
}

// --- PENGAMBILAN DATA STATISTIK ---

// 1. Hitung praktikum yang diikuti
$stmt_diikuti = $conn->prepare("SELECT COUNT(id) as total FROM pendaftaran_praktikum WHERE id_mahasiswa = ?");
$stmt_diikuti->bind_param("i", $id_mahasiswa);
$stmt_diikuti->execute();
$praktikum_diikuti = $stmt_diikuti->get_result()->fetch_assoc()['total'];

// 2. Hitung tugas yang sudah dikumpulkan (selesai)
$stmt_selesai = $conn->prepare("SELECT COUNT(id) as total FROM laporan WHERE id_mahasiswa = ?");
$stmt_selesai->bind_param("i", $id_mahasiswa);
$stmt_selesai->execute();
$tugas_selesai = $stmt_selesai->get_result()->fetch_assoc()['total'];

// 3. Hitung tugas yang masih menunggu (Total Modul - Tugas Selesai)
$stmt_total_modul = $conn->prepare("SELECT COUNT(m.id) as total FROM modul m JOIN pendaftaran_praktikum pp ON m.id_praktikum = pp.id_praktikum WHERE pp.id_mahasiswa = ?");
$stmt_total_modul->bind_param("i", $id_mahasiswa);
$stmt_total_modul->execute();
$total_modul = $stmt_total_modul->get_result()->fetch_assoc()['total'];
$tugas_menunggu = $total_modul - $tugas_selesai;

// --- PENGAMBILAN DATA NOTIFIKASI ---
$notifikasi_terbaru = [];

// Notifikasi: Nilai baru diberikan
$stmt_nilai = $conn->prepare("SELECT 'nilai_baru' as tipe, m.nama_modul, l.id as id_laporan, m.id_praktikum FROM laporan l JOIN modul m ON l.id_modul = m.id WHERE l.id_mahasiswa = ? AND l.nilai IS NOT NULL ORDER BY l.tanggal_kumpul DESC LIMIT 2");
$stmt_nilai->bind_param("i", $id_mahasiswa);
$stmt_nilai->execute();
$result_nilai = $stmt_nilai->get_result();
while($row = $result_nilai->fetch_assoc()) {
    $notifikasi_terbaru[] = $row;
}

// Notifikasi: Berhasil mendaftar praktikum
$stmt_daftar = $conn->prepare("SELECT 'daftar_baru' as tipe, mp.nama_praktikum, pp.id_praktikum, pp.tanggal_daftar FROM pendaftaran_praktikum pp JOIN mata_praktikum mp ON pp.id_praktikum = mp.id WHERE pp.id_mahasiswa = ? ORDER BY pp.tanggal_daftar DESC LIMIT 1");
$stmt_daftar->bind_param("i", $id_mahasiswa);
$stmt_daftar->execute();
$result_daftar = $stmt_daftar->get_result();
while($row = $result_daftar->fetch_assoc()) {
    $notifikasi_terbaru[] = $row;
}
?>

<div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-8 rounded-xl shadow-lg mb-8">
    <h1 class="text-3xl font-bold">Selamat Datang Kembali, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h1>
    <p class="mt-2 text-indigo-100">Terus semangat dalam menyelesaikan semua modul praktikummu.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-md text-center transform hover:-translate-y-1 transition-transform">
        <div class="text-5xl font-extrabold text-blue-600"><?php echo $praktikum_diikuti; ?></div>
        <div class="mt-2 text-lg text-gray-600 font-medium">Praktikum Diikuti</div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-md text-center transform hover:-translate-y-1 transition-transform">
        <div class="text-5xl font-extrabold text-green-500"><?php echo $tugas_selesai; ?></div>
        <div class="mt-2 text-lg text-gray-600 font-medium">Tugas Selesai</div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-md text-center transform hover:-translate-y-1 transition-transform">
        <div class="text-5xl font-extrabold text-yellow-500"><?php echo $tugas_menunggu; ?></div>
        <div class="mt-2 text-lg text-gray-600 font-medium">Tugas Menunggu</div>
    </div>
</div>

<div class="bg-white p-6 rounded-xl shadow-md">
    <h3 class="text-2xl font-bold text-gray-800 mb-4">Notifikasi Terbaru</h3>
    <?php if (!empty($notifikasi_terbaru)): ?>
        <ul class="space-y-4">
            <?php foreach ($notifikasi_terbaru as $notif): ?>
                <li class="flex items-start p-3 hover:bg-gray-50 rounded-lg">
                    <?php if ($notif['tipe'] == 'nilai_baru'): ?>
                        <span class="text-2xl mr-4">🔔</span>
                        <div>
                            Nilai untuk <a href="detail_praktikum.php?id=<?php echo $notif['id_praktikum']; ?>" class="font-semibold text-blue-600 hover:underline"><?php echo htmlspecialchars($notif['nama_modul']); ?></a> telah diberikan.
                        </div>
                    <?php elseif ($notif['tipe'] == 'daftar_baru'): ?>
                        <span class="text-2xl mr-4">✅</span>
                        <div>
                            Anda berhasil mendaftar pada mata praktikum <a href="detail_praktikum.php?id=<?php echo $notif['id_praktikum']; ?>" class="font-semibold text-blue-600 hover:underline"><?php echo htmlspecialchars($notif['nama_praktikum']); ?></a>.
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="text-center py-8">
            <p class="text-gray-500">Belum ada notifikasi terbaru untuk Anda.</p>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>