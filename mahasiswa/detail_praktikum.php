<?php
require_once '../config.php';
$pageTitle = 'Detail Praktikum';
$activePage = 'my_courses';
require_once 'templates/header_mahasiswa.php';

$id_praktikum = $_GET['id'] ?? 0;
$id_mahasiswa = $_SESSION['user_id'];
$message = '';

// Membuat direktori untuk upload laporan jika belum ada
$upload_dir = '../uploads/laporan/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Logika untuk mengunggah laporan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kumpul_laporan'])) {
    $id_modul = $_POST['id_modul'];

    if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] == 0) {
        $file_name = $id_mahasiswa . '_' . $id_modul . '_' . basename($_FILES['file_laporan']['name']);
        $target_file = $upload_dir . $file_name;

        // Cek apakah laporan sudah pernah diunggah
        $stmt_check = $conn->prepare("SELECT id FROM laporan WHERE id_mahasiswa=? AND id_modul=?");
        $stmt_check->bind_param("ii", $id_mahasiswa, $id_modul);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $message = "<div class='bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4' role='alert'><p>Anda sudah mengumpulkan laporan untuk modul ini.</p></div>";
        } else {
            if (move_uploaded_file($_FILES['file_laporan']['tmp_name'], $target_file)) {
                $sql_insert = "INSERT INTO laporan (id_modul, id_mahasiswa, file_laporan) VALUES (?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("iis", $id_modul, $id_mahasiswa, $target_file);
                if ($stmt_insert->execute()) {
                    $message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'><p>Laporan berhasil dikumpulkan.</p></div>";
                }
            } else {
                $message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'><p>Gagal mengunggah file.</p></div>";
            }
        }
    } else {
         $message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'><p>Tidak ada file yang dipilih atau terjadi kesalahan.</p></div>";
    }
}


// Ambil detail praktikum
$stmt_praktikum = $conn->prepare("SELECT nama_praktikum, deskripsi FROM mata_praktikum WHERE id = ?");
$stmt_praktikum->bind_param("i", $id_praktikum);
$stmt_praktikum->execute();
$praktikum = $stmt_praktikum->get_result()->fetch_assoc();

// Ambil semua modul untuk praktikum ini
$stmt_modul = $conn->prepare("SELECT * FROM modul WHERE id_praktikum = ? ORDER BY created_at ASC");
$stmt_modul->bind_param("i", $id_praktikum);
$stmt_modul->execute();
$modul_list = $stmt_modul->get_result();
?>

<?php echo $message; ?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <a href="my_courses.php" class="text-blue-500 hover:underline mb-6 inline-block">&larr; Kembali ke Daftar Praktikum</a>
    <h2 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></h2>
    <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></p>
</div>

<div class="space-y-6">
<?php
while ($modul = $modul_list->fetch_assoc()):
    // Cek status pengumpulan dan nilai untuk setiap modul
    $stmt_laporan = $conn->prepare("SELECT nilai, feedback FROM laporan WHERE id_modul = ? AND id_mahasiswa = ?");
    $stmt_laporan->bind_param("ii", $modul['id'], $id_mahasiswa);
    $stmt_laporan->execute();
    $laporan = $stmt_laporan->get_result()->fetch_assoc();
?>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="md:flex justify-between items-start">
            <div>
                <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($modul['nama_modul']); ?></h3>
                <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($modul['deskripsi_modul']); ?></p>
            </div>
            <?php if (!empty($modul['file_materi'])): ?>
                <a href="<?php echo htmlspecialchars($modul['file_materi']); ?>" download class="mt-4 md:mt-0 flex-shrink-0 inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                    Unduh Materi
                </a>
            <?php endif; ?>
        </div>

        <div class="border-t my-4"></div>

        <div>
            <h4 class="font-semibold text-gray-700 mb-2">Status Pengumpulan</h4>
            <?php if ($laporan): // Jika sudah mengumpulkan ?>
                <div class="bg-green-100 p-4 rounded-lg">
                    <p class="font-bold text-green-800">Anda sudah mengumpulkan laporan untuk modul ini.</p>
                    <?php if (!is_null($laporan['nilai'])): ?>
                        <div class="mt-2">
                            <p class="font-semibold">Nilai: <span class="text-2xl font-bold text-blue-600"><?php echo $laporan['nilai']; ?></span></p>
                            <?php if (!empty($laporan['feedback'])): ?>
                                <p class="font-semibold mt-1">Feedback Asisten:</p>
                                <p class="text-gray-700 italic">"<?php echo htmlspecialchars($laporan['feedback']); ?>"</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-yellow-800 mt-2">Laporan Anda sedang menunggu penilaian dari asisten.</p>
                    <?php endif; ?>
                </div>
            <?php else: // Jika belum mengumpulkan ?>
                <form action="detail_praktikum.php?id=<?php echo $id_praktikum; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_modul" value="<?php echo $modul['id']; ?>">
                    <div class="flex items-center">
                        <input type="file" name="file_laporan" class="shadow-sm border rounded py-2 px-3 text-gray-700 w-full" required>
                        <button type="submit" name="kumpul_laporan" class="ml-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex-shrink-0">
                            Kumpul
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endwhile; ?>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>