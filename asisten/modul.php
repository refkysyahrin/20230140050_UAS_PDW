<?php
require_once '../config.php';
$pageTitle = 'Manajemen Modul';
$activePage = 'modul';
require_once 'templates/header.php';

$id_asisten = $_SESSION['user_id'];
$message = '';

// Membuat direktori uploads jika belum ada
$upload_dir = '../uploads/materi/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Logika untuk menangani form (Create/Update)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_modul'])) {
    $id_praktikum = $_POST['id_praktikum'];
    $nama_modul = $_POST['nama_modul'];
    $deskripsi_modul = $_POST['deskripsi_modul'];
    $file_materi_lama = $_POST['file_materi_lama'] ?? '';
    $id_modul = $_POST['id_modul'] ?? null;
    $file_materi_path = $file_materi_lama;

    // Logika upload file
    if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == 0) {
        $file_name = time() . '_' . basename($_FILES['file_materi']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['file_materi']['tmp_name'], $target_file)) {
            $file_materi_path = $target_file;
            // Hapus file lama jika ada saat update
            if (!empty($file_materi_lama) && file_exists($file_materi_lama)) {
                unlink($file_materi_lama);
            }
        } else {
            $message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'><p>Gagal mengunggah file.</p></div>";
        }
    }

    if (empty($message)) {
        if ($id_modul) { // Update
            $sql = "UPDATE modul SET id_praktikum=?, nama_modul=?, deskripsi_modul=?, file_materi=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssi", $id_praktikum, $nama_modul, $deskripsi_modul, $file_materi_path, $id_modul);
        } else { // Create
            $sql = "INSERT INTO modul (id_praktikum, nama_modul, deskripsi_modul, file_materi) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $id_praktikum, $nama_modul, $deskripsi_modul, $file_materi_path);
        }
        
        if ($stmt->execute()) {
            $message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'><p>Modul berhasil disimpan.</p></div>";
        } else {
            $message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'><p>Gagal menyimpan modul.</p></div>";
        }
    }
}

// Logika Hapus
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_modul = $_GET['id'];
    // Ambil path file untuk dihapus
    $stmt_file = $conn->prepare("SELECT file_materi FROM modul WHERE id=?");
    $stmt_file->bind_param("i", $id_modul);
    $stmt_file->execute();
    $result_file = $stmt_file->get_result();
    if($row_file = $result_file->fetch_assoc()){
        if(!empty($row_file['file_materi']) && file_exists($row_file['file_materi'])){
            unlink($row_file['file_materi']);
        }
    }
    
    $stmt_delete = $conn->prepare("DELETE FROM modul WHERE id=?");
    $stmt_delete->bind_param("i", $id_modul);
    if($stmt_delete->execute()){
         $message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'><p>Modul berhasil dihapus.</p></div>";
    }
}

// Ambil data praktikum milik asisten
$praktikum_list = $conn->prepare("SELECT id, nama_praktikum FROM mata_praktikum WHERE id_asisten = ?");
$praktikum_list->bind_param("i", $id_asisten);
$praktikum_list->execute();
$result_praktikum = $praktikum_list->get_result();

// Data untuk form edit
$edit_modul = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_modul_edit = $_GET['id'];
    $stmt_edit = $conn->prepare("SELECT * FROM modul WHERE id = ?");
    $stmt_edit->bind_param("i", $id_modul_edit);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();
    $edit_modul = $result_edit->fetch_assoc();
}
?>

<?php echo $message; ?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h2 class="text-2xl font-bold mb-6 text-gray-800"><?php echo $edit_modul ? 'Edit' : 'Tambah'; ?> Modul</h2>
    <form action="modul.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_modul" value="<?php echo $edit_modul['id'] ?? ''; ?>">
        <input type="hidden" name="file_materi_lama" value="<?php echo $edit_modul['file_materi'] ?? ''; ?>">
        
        <div class="mb-4">
            <label for="id_praktikum" class="block text-gray-700 text-sm font-bold mb-2">Pilih Mata Praktikum</label>
            <select name="id_praktikum" id="id_praktikum" class="shadow border rounded w-full py-2 px-3 text-gray-700" required>
                <option value="">-- Pilih Praktikum --</option>
                <?php while($p = $result_praktikum->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo (isset($edit_modul) && $edit_modul['id_praktikum'] == $p['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['nama_praktikum']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-4">
            <label for="nama_modul" class="block text-gray-700 text-sm font-bold mb-2">Nama Modul</label>
            <input type="text" id="nama_modul" name="nama_modul" value="<?php echo htmlspecialchars($edit_modul['nama_modul'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
        </div>

        <div class="mb-4">
            <label for="deskripsi_modul" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
            <textarea id="deskripsi_modul" name="deskripsi_modul" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"><?php echo htmlspecialchars($edit_modul['deskripsi_modul'] ?? ''); ?></textarea>
        </div>

        <div class="mb-6">
            <label for="file_materi" class="block text-gray-700 text-sm font-bold mb-2">Upload File Materi (PDF/DOCX)</label>
            <input type="file" id="file_materi" name="file_materi" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
            <?php if (isset($edit_modul) && !empty($edit_modul['file_materi'])): ?>
                <p class="text-xs text-gray-500 mt-1">File saat ini: <a href="<?php echo htmlspecialchars($edit_modul['file_materi']); ?>" class="text-blue-500" target="_blank"><?php echo basename($edit_modul['file_materi']); ?></a>. Biarkan kosong jika tidak ingin mengubah.</p>
            <?php endif; ?>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" name="simpan_modul" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <?php echo $edit_modul ? 'Update Modul' : 'Simpan Modul'; ?>
            </button>
             <?php if ($edit_modul): ?>
                <a href="modul.php" class="font-bold text-sm text-blue-500 hover:text-blue-800">Batal Edit</a>
            <?php endif; ?>
        </div>
    </form>
</div>


<div class="bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Daftar Modul</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Praktikum</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Nama Modul</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">File Materi</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php
                $sql = "SELECT m.id, m.nama_modul, m.file_materi, mp.nama_praktikum 
                        FROM modul m
                        JOIN mata_praktikum mp ON m.id_praktikum = mp.id
                        WHERE mp.id_asisten = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id_asisten);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='py-3 px-4'>" . htmlspecialchars($row['nama_praktikum']) . "</td>";
                        echo "<td class='py-3 px-4'>" . htmlspecialchars($row['nama_modul']) . "</td>";
                        echo "<td class='py-3 px-4'>";
                        if (!empty($row['file_materi'])) {
                            echo "<a href='" . htmlspecialchars($row['file_materi']) . "' class='text-blue-500 hover:underline' target='_blank'>Download</a>";
                        } else {
                            echo "Tidak ada";
                        }
                        echo "</td>";
                        echo "<td class='py-3 px-4'>";
                        echo "<a href='modul.php?action=edit&id=" . $row['id'] . "' class='text-blue-500 hover:text-blue-700 mr-4'>Edit</a>";
                        echo "<a href='modul.php?action=delete&id=" . $row['id'] . "' onclick='return confirm(\"Yakin ingin menghapus modul ini?\")' class='text-red-500 hover:text-red-700'>Hapus</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center py-4'>Belum ada modul yang ditambahkan.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>