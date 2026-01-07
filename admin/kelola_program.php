<?php
require_once '../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
$page_title = "Kelola Program Donasi";
require_once 'templates/header_admin.php';

// Menambahkan kolom 'kategori' pada query SELECT
$result_program = $mysqli->query("SELECT id, nama_program, kategori, target_donasi, donasi_terkumpul FROM program ORDER BY created_at DESC");
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-bold text-dark-text"><?php echo $page_title; ?></h1>
        <a href="tambah_program.php" class="btn-primary"><i class="bi bi-plus-circle mr-2"></i> Tambah Program</a>
    </div>

    <div class="content-card">
        <div class="space-y-4">
            <?php if ($result_program && $result_program->num_rows > 0): ?>
            <?php while($program = $result_program->fetch_assoc()): 
                    $persentase = $program['target_donasi'] > 0 ? min(100, ($program['donasi_terkumpul'] / $program['target_donasi']) * 100) : 0;
                ?>
            <div
                class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 border border-gray-200 rounded-lg bg-white hover:shadow-md transition-shadow">
                
                <!-- Program Details -->
                <div class="flex-1 space-y-2">
                    <div class="flex items-center gap-3">
                        <span class="badge-info"><?php echo htmlspecialchars($program['kategori']); ?></span>
                        <h3 class="font-bold text-dark-text text-lg"><?php echo htmlspecialchars($program['nama_program']); ?></h3>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-green-600">Rp <?php echo number_format($program['donasi_terkumpul'], 0, ',', '.'); ?></span>
                            <span class="text-sm font-medium text-gray-500">Target: Rp <?php echo number_format($program['target_donasi'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-primary-orange h-2.5 rounded-full" style="width: <?php echo $persentase; ?>%"></div>
                        </div>
                        <div class="text-right text-sm font-bold text-primary-orange mt-1"><?php echo round($persentase); ?>%</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex-shrink-0 flex w-full sm:w-auto justify-end gap-2 border-t sm:border-t-0 pt-4 sm:pt-0">
                    <a href="edit_program.php?id=<?php echo $program['id']; ?>"
                        class="btn-icon bg-yellow-100 text-yellow-600 hover:bg-yellow-200" title="Edit">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="hapus_program.php?id=<?php echo $program['id']; ?>"
                        class="btn-icon bg-red-100 text-red-600 hover:bg-red-200" title="Hapus"
                        onclick="return confirm('Anda yakin ingin menghapus program ini beserta semua data donasi terkait?');">
                        <i class="bi bi-trash"></i>
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <div class="text-center py-10">
                <i class="bi bi-folder-x text-4xl text-gray-300"></i>
                <p class="mt-2 text-gray-500">Belum ada program donasi yang ditambahkan.</p>
                <a href="tambah_program.php" class="btn-primary mt-4">Buat Program Pertama Anda</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'templates/footer_admin.php'; ?>