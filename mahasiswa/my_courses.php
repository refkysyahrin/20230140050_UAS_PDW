<?php
require_once '../config.php';
$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses';
require_once 'templates/header_mahasiswa.php';

$id_mahasiswa = $_SESSION['user_id'];

// Query untuk mengambil semua praktikum yang diikuti oleh mahasiswa
$sql = "SELECT mp.id, mp.nama_praktikum, mp.deskripsi, u.nama as nama_asisten 
        FROM pendaftaran_praktikum pp
        JOIN mata_praktikum mp ON pp.id_praktikum = mp.id
        JOIN users u ON mp.id_asisten = u.id
        WHERE pp.id_mahasiswa = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Daftar Praktikum yang Saya Ikuti</h2>
    
    <?php if ($result->num_rows > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="bg-gray-50 border border-gray-200 rounded-lg overflow-hidden flex flex-col">
                    <div class="p-6 flex-grow">
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($row['nama_praktikum']); ?></h3>
                        <p class="text-sm text-gray-500 mb-3">Asisten: <?php echo htmlspecialchars($row['nama_asisten']); ?></p>
                        <p class="text-gray-700 text-base">
                            <?php echo htmlspecialchars($row['deskripsi']); ?>
                        </p>
                    </div>
                    <div class="bg-gray-100 p-4">
                        <a href="detail_praktikum.php?id=<?php echo $row['id']; ?>" class="block text-center w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors duration-300">
                            Lihat Detail & Tugas
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-10">
            <p class="text-gray-500 text-lg">Anda belum mendaftar pada praktikum manapun.</p>
            <a href="courses.php" class="mt-4 inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                Cari Praktikum Sekarang
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>