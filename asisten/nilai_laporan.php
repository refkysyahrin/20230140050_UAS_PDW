<?php
require_once '../config.php';
$pageTitle = 'Beri Nilai Laporan';
$activePage = 'laporan';
require_once 'templates/header.php';

$id_laporan = $_GET['id'] ?? 0;

// Logika simpan nilai dan feedback
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_nilai'])) {
    $nilai = $_POST['nilai'];
    $feedback = $_POST['feedback'];
    
    $sql_update = "UPDATE laporan SET nilai = ?, feedback = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("isi", $nilai, $feedback, $id_laporan);
    
    if ($stmt_update->execute()) {
        echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'><p>Nilai berhasil disimpan.</p></div>";
    } else {
        echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'><p>Gagal menyimpan nilai.</p></div>";
    }
}

// Ambil detail laporan
$sql = "SELECT l.id, l.file_laporan, l.nilai, l.feedback, u.nama as nama_mahasiswa, mp.nama_praktikum, m.nama_modul
        FROM laporan l
        JOIN users u ON l.id_mahasiswa = u.id
        JOIN modul m ON l.id_modul = m.id
        JOIN mata_praktikum mp ON m.id_praktikum = mp.id
        WHERE l.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_laporan);
$stmt->execute();
$result = $stmt->get_result();
$laporan = $result->fetch_assoc();

if (!$laporan) {
    echo "Laporan tidak ditemukan.";
    exit;
}
?>

<div class="bg-white p-8 rounded-lg shadow-md">
    <a href="laporan.php" class="text-blue-500 hover:underline mb-6 inline-block">&larr; Kembali ke Daftar Laporan</a>
    
    <h2 class="text-2xl font-bold mb-2 text-gray-800">Detail Laporan</h2>
    <p class="text-gray-600 mb-6">Penilaian untuk modul "<?php echo htmlspecialchars($laporan['nama_modul']); ?>"</p>

    <div class="mb-8 border-b pb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="font-bold text-gray-700">Nama Mahasiswa</p>
                <p><?php echo htmlspecialchars($laporan['nama_mahasiswa']); ?></p>
            </div>
            <div>
                <p class="font-bold text-gray-700">Mata Praktikum</p>
                <p><?php echo htmlspecialchars($laporan['nama_praktikum']); ?></p>
            </div>
            <div>
                <p class="font-bold text-gray-700">File Laporan</p>
                <a href="<?php echo htmlspecialchars($laporan['file_laporan']); ?>" class="text-blue-500 hover:underline" target="_blank">
                    Download Laporan
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <h3 class="text-xl font-bold mb-4 text-gray-800">Form Penilaian</h3>
    <form action="nilai_laporan.php?id=<?php echo $id_laporan; ?>" method="POST">
        <div class="mb-4">
            <label for="nilai" class="block text-gray-700 text-sm font-bold mb-2">Nilai (0-100)</label>
            <input type="number" name="nilai" id="nilai" min="0" max="100" value="<?php echo htmlspecialchars($laporan['nilai'] ?? ''); ?>" class="shadow appearance-none border rounded w-full md:w-1/3 py-2 px-3 text-gray-700" required>
        </div>
        <div class="mb-6">
             <label for="feedback" class="block text-gray-700 text-sm font-bold mb-2">Feedback (Opsional)</label>
            <textarea name="feedback" id="feedback" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"><?php echo htmlspecialchars($laporan['feedback'] ?? ''); ?></textarea>
        </div>
        <div>
            <button type="submit" name="simpan_nilai" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Simpan Penilaian
            </button>
        </div>
    </form>
</div>

<?php
require_once 'templates/footer.php';
?>