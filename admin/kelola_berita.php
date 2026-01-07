<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Kelola Berita";

// --- LOGIKA PENCARIAN, FILTER, DAN PAGINASI ---
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'semua';
$type_filter = $_GET['type'] ?? 'semua';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Bangun query dasar
$sql_base = "FROM berita";
$where_clauses = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_clauses[] = "(judul LIKE ? OR penulis LIKE ?)";
    $search_param = "%{$search}%";
    array_push($params, $search_param, $search_param);
    $types .= 'ss';
}

if ($status_filter !== 'semua' && in_array($status_filter, ['published', 'pending', 'rejected'])) {
    $where_clauses[] = "status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($type_filter !== 'semua' && in_array($type_filter, ['berita', 'opini'])) {
    $where_clauses[] = "type = ?";
    $params[] = $type_filter;
    $types .= 's';
}

$sql_where = count($where_clauses) > 0 ? " WHERE " . implode(' AND ', $where_clauses) : '';

// Query untuk menghitung total data
$sql_total = "SELECT COUNT(id) as total " . $sql_base . $sql_where;
$stmt_total = $mysqli->prepare($sql_total);
if (!empty($types)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_results = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);
$stmt_total->close();

// Query untuk mengambil data per halaman
$sql_data = "SELECT id, judul, gambar, penulis, status, type, created_at " . $sql_base . $sql_where . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt_data = $mysqli->prepare($sql_data);
$stmt_data->bind_param($types, ...$params);
$stmt_data->execute();
$result_berita = $stmt_data->get_result();
$stmt_data->close();

function get_status_badge($status) {
    switch ($status) {
        case 'published': return '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Published</span>';
        case 'pending': return '<span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">Pending</span>';
        case 'rejected': return '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Rejected</span>';
        default: return '<span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Unknown</span>';
    }
}

function get_type_badge($type) {
    switch ($type) {
        case 'berita': return '<span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">Berita</span>';
        case 'opini': return '<span class="px-2 py-1 text-xs font-semibold text-purple-800 bg-purple-100 rounded-full">Opini</span>';
        default: return '<span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Unknown</span>';
    }
}

require_once 'templates/header_admin.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-bold text-dark-text">Kelola Berita</h1>
        <a href="tambah_berita.php" class="btn-primary">
            <i class="bi bi-plus-circle mr-2"></i> Tulis Berita Baru
        </a>
    </div>

    <!-- Filter dan Pencarian -->
    <div class="content-card">
        <form action="kelola_berita.php" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="md:col-span-2">
                    <label for="search" class="form-label">Cari Berita</label>
                    <input type="text" id="search" name="search" class="form-input" placeholder="Judul atau Penulis..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div>
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="semua" <?php if ($status_filter == 'semua') echo 'selected'; ?>>Semua Status
                        </option>
                        <option value="published" <?php if ($status_filter == 'published') echo 'selected'; ?>>Published
                        </option>
                        <option value="pending" <?php if ($status_filter == 'pending') echo 'selected'; ?>>Pending
                        </option>
                        <option value="rejected" <?php if ($status_filter == 'rejected') echo 'selected'; ?>>Rejected
                        </option>
                    </select>
                </div>
                <div>
                    <label for="type" class="form-label">Tipe</label>
                    <select id="type" name="type" class="form-select">
                        <option value="semua" <?php if ($type_filter == 'semua') echo 'selected'; ?>>Semua Tipe
                        </option>
                        <option value="berita" <?php if ($type_filter == 'berita') echo 'selected'; ?>>Berita
                        </option>
                        <option value="opini" <?php if ($type_filter == 'opini') echo 'selected'; ?>>Opini
                        </option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <a href="kelola_berita.php" class="btn-secondary mr-2">Reset</a>
                <button type="submit" class="btn-primary">Filter</button>
            </div>
        </form>
    </div>

    <!-- Daftar Berita -->
    <div class="content-card mt-6">
        <div class="space-y-4">
            <?php if ($result_berita->num_rows > 0): ?>
            <?php while($berita = $result_berita->fetch_assoc()): ?>
            <div
                class="flex flex-col md:flex-row items-center gap-4 p-4 border border-gray-200 rounded-lg bg-white hover:shadow-md transition-shadow">
                <img src="../assets/uploads/berita/<?php echo htmlspecialchars($berita['gambar']); ?>"
                    alt="Gambar Berita" class="w-full md:w-32 h-32 md:h-20 object-cover rounded-md">
                <div class="flex-1">
                    <h3 class="font-bold text-dark-text"><?php echo htmlspecialchars($berita['judul']); ?></h3>
                    <p class="text-sm text-gray-500">
                        Oleh <?php echo htmlspecialchars($berita['penulis']); ?> pada
                        <?php echo date('d M Y', strtotime($berita['created_at'])); ?>
                    </p>
                </div>
                <div class="flex-shrink-0 flex gap-2">
                    <?php echo get_type_badge($berita['type']); ?>
                    <?php echo get_status_badge($berita['status']); ?>
                </div>
                <div class="flex-shrink-0 flex gap-2">
                    <?php if ($berita['status'] == 'pending'): ?>
                    <a href="proses_persetujuan_berita.php?id=<?php echo $berita['id']; ?>&action=setujui"
                        class="btn-icon bg-green-100 text-green-600 hover:bg-green-200" title="Setujui"><i
                            class="bi bi-check-lg"></i></a>
                    <a href="proses_persetujuan_berita.php?id=<?php echo $berita['id']; ?>&action=tolak"
                        class="btn-icon bg-red-100 text-red-600 hover:bg-red-200" title="Tolak"><i
                            class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                    <a href="edit_berita.php?id=<?php echo $berita['id']; ?>"
                        class="btn-icon bg-yellow-100 text-yellow-600 hover:bg-yellow-200" title="Edit"><i
                            class="bi bi-pencil-square"></i></a>
                    <a href="hapus_berita.php?id=<?php echo $berita['id']; ?>"
                        class="btn-icon bg-gray-100 text-gray-600 hover:bg-gray-200"
                        onclick="return confirm('Yakin ingin menghapus berita ini?');" title="Hapus"><i
                            class="bi bi-trash"></i></a>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <div class="text-center py-10">
                <p class="text-gray-500">Tidak ada berita yang cocok dengan kriteria pencarian Anda.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Paginasi -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&type=<?php echo $type_filter; ?>"
                class="<?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'templates/footer_admin.php'; ?>