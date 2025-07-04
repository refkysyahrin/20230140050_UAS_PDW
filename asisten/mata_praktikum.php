<?php
require_once '../config.php';

$pageTitle = 'Manajemen Mata Praktikum';
$activePage = 'mata_praktikum';
require_once 'templates/header.php';

// Logika untuk menghapus
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_praktikum = $_GET['id'];
    $id_asisten = $_SESSION['user_id'];
    
    // Pastikan asisten hanya bisa menghapus praktikum miliknya
    $sql_delete = "DELETE FROM mata_praktikum WHERE id = ? AND id_asisten = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("ii", $id_praktikum, $id_asisten);
    if ($stmt->execute()) {
        echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'><p>Mata praktikum berhasil dihapus.</p></div>";
    } else {
        echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'><p>Gagal menghapus mata praktikum.</p></div>";
    }
}

// Logika untuk menambah atau mengedit
$nama_praktikum = '';
$deskripsi = '';
$is_edit = false;
$edit_id = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_praktikum = $_POST['nama_praktikum'];
    $deskripsi = $_POST['deskripsi'];
    $id_asisten = $_SESSION['user_id'];
    
    if (isset($_POST['id']) && !empty($_POST['id'])) { // Proses Update
        $edit_id = $_POST['id'];
        $sql = "UPDATE mata_praktikum SET nama_praktikum = ?, deskripsi = ? WHERE id = ? AND id_asisten = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $nama_praktikum, $deskripsi, $edit_id, $id_asisten);
    } else { // Proses Create
        $sql = "INSERT INTO mata_praktikum (nama_praktikum, deskripsi, id_asisten) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nama_praktikum, $deskripsi, $id_asisten);
    }

    if ($stmt->execute()) {
        echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'><p>Data berhasil disimpan.</p></div>";
    } else {
        echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'><p>Gagal menyimpan data.</p></div>";
    }
}

// Untuk mode edit
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $is_edit = true;
    $edit_id = $_GET['id'];
    $id_asisten = $_SESSION['user_id'];
    $sql_edit = "SELECT nama_praktikum, deskripsi FROM mata_praktikum WHERE id = ? AND id_asisten = ?";
    $stmt = $conn->prepare($sql_edit);
    $stmt->bind_param("ii", $edit_id, $id_asisten);
    $stmt->execute();
    $result = $stmt->get_result();
    if($row = $result->fetch_assoc()) {
        $nama_praktikum = $row['nama_praktikum'];
        $deskripsi = $row['deskripsi'];
    }
}
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h2 class="text-2xl font-bold mb-6 text-gray-800"><?php echo $is_edit ? 'Edit' : 'Tambah'; ?> Mata Praktikum</h2>
    <form action="mata_praktikum.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
        <div class="mb-4">
            <label for="nama_praktikum" class="block text-gray-700 text-sm font-bold mb-2">Nama Praktikum</label>
            <input type="text" id="nama_praktikum" name="nama_praktikum" value="<?php echo htmlspecialchars($nama_praktikum); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-6">
            <label for="deskripsi" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($deskripsi); ?></textarea>
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo $is_edit ? 'Update Data' : 'Simpan'; ?>
            </button>
            <?php if ($is_edit): ?>
            <a href="mata_praktikum.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                Batal Edit
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Daftar Mata Praktikum Saya</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="w-1/3 text-left py-3 px-4 uppercase font-semibold text-sm">Nama Praktikum</th>
                    <th class="w-1/3 text-left py-3 px-4 uppercase font-semibold text-sm">Deskripsi</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php
                $id_asisten = $_SESSION['user_id'];
                $sql = "SELECT id, nama_praktikum, deskripsi FROM mata_praktikum WHERE id_asisten = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id_asisten);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='w-1/3 text-left py-3 px-4'>" . htmlspecialchars($row['nama_praktikum']) . "</td>";
                        echo "<td class='w-1/3 text-left py-3 px-4'>" . htmlspecialchars($row['deskripsi']) . "</td>";
                        echo "<td class='text-left py-3 px-4'>";
                        echo "<a href='mata_praktikum.php?action=edit&id=" . $row['id'] . "' class='text-blue-500 hover:text-blue-700 mr-4'>Edit</a>";
                        echo "<a href='mata_praktikum.php?action=delete&id=" . $row['id'] . "' onclick='return confirm(\"Apakah Anda yakin ingin menghapus data ini?\")' class='text-red-500 hover:text-red-700'>Hapus</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='text-center py-4'>Belum ada mata praktikum yang ditambahkan.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>