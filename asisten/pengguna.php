<?php
require_once '../config.php';
$pageTitle = 'Manajemen Pengguna';
$activePage = 'pengguna';
require_once 'templates/header.php';
$message = '';

// --- LOGIKA AKSI (DELETE, CREATE, UPDATE) ---

// Logika Hapus
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_user = $_GET['id'];
    if ($id_user == $_SESSION['user_id']) {
        $message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-r-lg' role='alert'><p><strong>Error:</strong> Anda tidak bisa menghapus akun Anda sendiri.</p></div>";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id_user);
        if($stmt->execute()){
            $message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-r-lg' role='alert'><p>Pengguna berhasil dihapus.</p></div>";
        }
    }
}

// Data default untuk form
$is_edit = false;
$edit_data = ['id' => '', 'nama' => '', 'email' => '', 'role' => 'mahasiswa'];

// Cek jika mode edit
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $is_edit = true;
    $stmt_edit = $conn->prepare("SELECT id, nama, email, role FROM users WHERE id = ?");
    $stmt_edit->bind_param("i", $_GET['id']);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();
    if($result_edit->num_rows > 0) {
        $edit_data = $result_edit->fetch_assoc();
    } else {
        $is_edit = false; // kembali ke mode tambah jika ID tidak ditemukan
    }
}

// Logika Tambah/Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    if ($id) { // Proses UPDATE
        $sql = "UPDATE users SET nama=?, email=?, role=? WHERE id=?";
        $types = "sssi";
        $params = [$nama, $email, $role, $id];

        if (!empty($password)) { // Jika password diisi, update juga password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET nama=?, email=?, role=?, password=? WHERE id=?";
            $types = "ssssi";
            $params = [$nama, $email, $role, $hashed_password, $id];
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
             $message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-r-lg' role='alert'><p>Data pengguna berhasil diperbarui.</p></div>";
             $is_edit = false; // Kembali ke mode tambah
             $edit_data = ['id' => '', 'nama' => '', 'email' => '', 'role' => 'mahasiswa'];
        }

    } else { // Proses CREATE
        if (empty($nama) || empty($email) || empty($password) || empty($role)) {
            $message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-r-lg' role='alert'><p>Semua kolom wajib diisi untuk pengguna baru.</p></div>";
        } else {
            // Cek duplikasi email
            $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            if($stmt_check->get_result()->num_rows > 0) {
                $message = "<div class='bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded-r-lg' role='alert'><p>Email <strong>".htmlspecialchars($email)."</strong> sudah terdaftar.</p></div>";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $nama, $email, $hashed_password, $role);
                if ($stmt->execute()) {
                     $message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-r-lg' role='alert'><p>Pengguna baru berhasil ditambahkan.</p></div>";
                }
            }
        }
    }
}

// Ambil semua pengguna untuk ditampilkan di tabel
$users = $conn->query("SELECT id, nama, email, role FROM users ORDER BY role, nama");
?>

<?php if($message) echo $message; ?>

<div class="bg-white p-8 rounded-2xl shadow-lg mb-8">
    <h2 class="text-2xl font-bold mb-6 text-gray-800"><?php echo $is_edit ? 'Edit Data Pengguna' : 'Tambah Pengguna Baru'; ?></h2>
    <form action="pengguna.php" method="POST" class="space-y-6">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_data['id']); ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" value="<?php echo htmlspecialchars($edit_data['nama']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Alamat Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($edit_data['email']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Peran (Role)</label>
                <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="mahasiswa" <?php echo ($edit_data['role'] == 'mahasiswa') ? 'selected' : ''; ?>>Mahasiswa</option>
                    <option value="asisten" <?php echo ($edit_data['role'] == 'asisten') ? 'selected' : ''; ?>>Asisten</option>
                </select>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" <?php echo !$is_edit ? 'required' : ''; ?>>
                <?php if($is_edit): ?>
                    <p class="text-xs text-gray-500 mt-1">*) Biarkan kosong jika tidak ingin mengubah password.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="flex items-center space-x-4 pt-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                <?php echo $is_edit ? 'Simpan Perubahan' : 'Tambah Pengguna'; ?>
            </button>
            <?php if($is_edit): ?>
                <a href="pengguna.php" class="text-gray-600 hover:text-gray-900 font-medium">Batal Edit</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="bg-white p-8 rounded-2xl shadow-lg">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Daftar Semua Pengguna</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peran</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php while($user = $users->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['nama']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $user['role'] == 'asisten' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right space-x-4">
                        <a href="pengguna.php?action=edit&id=<?php echo $user['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        <a href="pengguna.php?action=delete&id=<?php echo $user['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.')" class="text-red-600 hover:text-red-900">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>