<?php
require_once '../config.php';
$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';
require_once 'templates/header.php';

$id_asisten = $_SESSION['user_id'];

// Logika Filter
$filter_praktikum = $_GET['filter_praktikum'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';
$where_clauses = ["mp.id_asisten = ?"];
$params = ['i', $id_asisten];

if (!empty($filter_praktikum)) {
    $where_clauses[] = "mp.id = ?";
    $params[0] .= 'i';
    $params[] = $filter_praktikum;
}

if ($filter_status == 'dinilai') {
    $where_clauses[] = "l.nilai IS NOT NULL";
} elseif ($filter_status == 'belum_dinilai') {
    $where_clauses[] = "l.nilai IS NULL";
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

$sql = "SELECT l.id, u.nama as nama_mahasiswa, mp.nama_praktikum, m.nama_modul, l.tanggal_kumpul, l.nilai 
        FROM laporan l
        JOIN users u ON l.id_mahasiswa = u.id
        JOIN modul m ON l.id_modul = m.id
        JOIN mata_praktikum mp ON m.id_praktikum = mp.id
        $where_sql
        ORDER BY l.tanggal_kumpul DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    // Dynamically bind parameters
    if (count($params) > 1) {
        $stmt->bind_param(...$params);
    } else {
         $stmt->bind_param($params[0], $params[1]);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("SQL Error: " . $conn->error);
}


// Ambil data praktikum untuk filter
$praktikum_list = $conn->prepare("SELECT id, nama_praktikum FROM mata_praktikum WHERE id_asisten = ?");
$praktikum_list->bind_param("i", $id_asisten);
$praktikum_list->execute();
$result_praktikum = $praktikum_list->get_result();

?>

<div class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h2 class="text-xl font-bold mb-4 text-gray-800">Filter Laporan</h2>
    <form action="laporan.php" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="filter_praktikum" class="block text-gray-700 text-sm font-bold mb-2">Mata Praktikum</label>
            <select name="filter_praktikum" id="filter_praktikum" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                <option value="">Semua Praktikum</option>
                 <?php while($p = $result_praktikum->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo ($filter_praktikum == $p['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['nama_praktikum']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="filter_status" class="block text-gray-700 text-sm font-bold mb-2">Status Penilaian</label>
            <select name="filter_status" id="filter_status" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                <option value="">Semua Status</option>
                <option value="dinilai" <?php echo ($filter_status == 'dinilai') ? 'selected' : ''; ?>>Sudah Dinilai</option>
                <option value="belum_dinilai" <?php echo ($filter_status == 'belum_dinilai') ? 'selected' : ''; ?>>Belum Dinilai</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
                Terapkan Filter
            </button>
        </div>
    </form>
</div>


<div class="bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Daftar Laporan Masuk</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Mahasiswa</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Praktikum</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Modul</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Tgl Kumpul</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Status</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $status_nilai = is_null($row['nilai']) 
                            ? "<span class='bg-yellow-200 text-yellow-800 py-1 px-3 rounded-full text-xs'>Belum Dinilai</span>"
                            : "<span class='bg-green-200 text-green-800 py-1 px-3 rounded-full text-xs'>Dinilai (" . $row['nilai'] . ")</span>";
                        
                        echo "<tr>";
                        echo "<td class='py-3 px-4'>" . htmlspecialchars($row['nama_mahasiswa']) . "</td>";
                        echo "<td class='py-3 px-4'>" . htmlspecialchars($row['nama_praktikum']) . "</td>";
                        echo "<td class='py-3 px-4'>" . htmlspecialchars($row['nama_modul']) . "</td>";
                        echo "<td class='py-3 px-4'>" . date('d M Y H:i', strtotime($row['tanggal_kumpul'])) . "</td>";
                        echo "<td class='py-3 px-4 text-center'>$status_nilai</td>";
                        echo "<td class='py-3 px-4'><a href='nilai_laporan.php?id=" . $row['id'] . "' class='text-blue-500 hover:underline font-semibold'>Lihat & Nilai</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center py-4'>Tidak ada laporan yang masuk.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>