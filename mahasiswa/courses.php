<?php
require_once '../config.php';
$pageTitle = 'Cari Praktikum';
$activePage = 'courses';
require_once 'templates/header_mahasiswa.php';

$id_mahasiswa = $_SESSION['user_id'];

// Logika untuk mendaftar
if (isset($_POST['daftar'])) {
    $id_praktikum = $_POST['id_praktikum'];
    
    // Cek dulu apakah sudah terdaftar
    $sql_check = "SELECT id FROM pendaftaran_praktikum WHERE id_mahasiswa = ? AND id_praktikum = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $id_mahasiswa, $id_praktikum);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo "<div class='bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4' role='alert'><p>Anda sudah terdaftar pada praktikum ini.</p></div>";
    } else {
        $sql_daftar = "INSERT INTO pendaftaran_praktikum (id_mahasiswa, id_praktikum) VALUES (?, ?)";
        $stmt_daftar = $conn->prepare($sql_daftar);
        $stmt_daftar->bind_param("ii", $id_mahasiswa, $id_praktikum);
        if ($stmt_daftar->execute()) {
            echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'><p>Pendaftaran berhasil!</p></div>";
        } else {
            echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'><p>Gagal mendaftar.</p></div>";
        }
    }
}
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php
    // Query untuk mengambil semua mata praktikum beserta nama asistennya
    $sql = "SELECT mp.id, mp.nama_praktikum, mp.deskripsi, u.nama as nama_asisten 
            FROM mata_praktikum mp 
            JOIN users u ON mp.id_asisten = u.id";
    $result = $conn->query($sql);
    
    // Query untuk mendapatkan ID praktikum yang sudah diikuti mahasiswa
    $sql_terdaftar = "SELECT id_praktikum FROM pendaftaran_praktikum WHERE id_mahasiswa = ?";
    $stmt_terdaftar = $conn->prepare($sql_terdaftar);
    $stmt_terdaftar->bind_param("i", $id_mahasiswa);
    $stmt_terdaftar->execute();
    $result_terdaftar = $stmt_terdaftar->get_result();
    $praktikum_terdaftar = [];
    while($row_terdaftar = $result_terdaftar->fetch_assoc()) {
        $praktikum_terdaftar[] = $row_terdaftar['id_praktikum'];
    }

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
    ?>
        <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:-translate-y-2 transition-transform duration-300">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($row['nama_praktikum']); ?></h3>
                <p class="text-sm text-gray-500 mb-3">Dosen Pengampu: <?php echo htmlspecialchars($row['nama_asisten']); ?></p>
                <p class="text-gray-600 text-base mb-4">
                    <?php echo htmlspecialchars($row['deskripsi']); ?>
                </p>
                
                <form action="courses.php" method="POST">
                    <input type="hidden" name="id_praktikum" value="<?php echo $row['id']; ?>">
                    <?php if (in_array($row['id'], $praktikum_terdaftar)): ?>
                        <button type="button" class="w-full bg-gray-400 text-white font-bold py-2 px-4 rounded cursor-not-allowed" disabled>
                            Sudah Terdaftar
                        </button>
                    <?php else: ?>
                        <button type="submit" name="daftar" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors duration-300">
                            Daftar Praktikum
                        </button>
                    <?php endif; ?>
                </form>

            </div>
        </div>
    <?php
        }
    } else {
        echo "<p class='col-span-full text-center text-gray-500'>Belum ada mata praktikum yang tersedia.</p>";
    }
    ?>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>