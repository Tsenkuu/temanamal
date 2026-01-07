<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Pengaturan Website";

// Ambil nilai saat ini dari database
$result_total = $mysqli->query("SELECT nilai_pengaturan FROM pengaturan WHERE nama_pengaturan = 'total_donasi_disalurkan'");
$total_donasi_disalurkan = $result_total->fetch_assoc()['nilai_pengaturan'] ?? '0';

$result_wa = $mysqli->query("SELECT nilai_pengaturan FROM pengaturan WHERE nama_pengaturan = 'admin_wa_number'");
$admin_wa_number = $result_wa->fetch_assoc()['nilai_pengaturan'] ?? '';

require_once 'templates/header_admin.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-bold text-dark-text"><?php echo $page_title; ?></h1>
    </div>

    <?php
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <div class="content-card">
            <h3 class="card-title mb-4">Pengaturan Umum</h3>
            <form action="proses_pengaturan.php" method="POST" class="space-y-4">
                <div>
                    <label for="total_donasi_disalurkan" class="form-label">Total Donasi yang Telah Disalurkan
                        (Rp)</label>
                    <input type="text" class="form-input" id="total_donasi_disalurkan" name="total_donasi_disalurkan"
                        value="<?php echo number_format($total_donasi_disalurkan, 0, ',', '.'); ?>">
                </div>
                <div>
                    <label for="admin_wa_number" class="form-label">Nomor WA Admin untuk Notifikasi</label>
                    <input type="text" class="form-input" id="admin_wa_number" name="admin_wa_number"
                        value="<?php echo htmlspecialchars($admin_wa_number); ?>" placeholder="Contoh: 6281234567890">
                    <p class="text-xs text-gray-500 mt-1">Gunakan format internasional (diawali 62).</p>
                </div>
                <button type="submit" name="simpan_umum" class="btn-primary">Simpan Pengaturan</button>
            </form>
        </div>

        <div class="content-card">
            <h3 class="card-title mb-4">Pengaturan Bot WhatsApp</h3>
            <p class="text-gray-600 mb-4">Jika bot WhatsApp Anda mengalami masalah koneksi atau Anda ingin menautkan ke
                nomor baru, gunakan tombol di bawah ini.</p>
            <form action="proses_pengaturan.php" method="POST"
                onsubmit="return confirm('Anda yakin ingin mereset sesi bot? Aplikasi bot akan berhenti dan perlu dijalankan ulang untuk memindai QR code baru.');">
                <button type="submit" name="reset_bot" class="btn-danger">Reset Sesi & Tautkan Ulang
                    Bot</button>
            </form>
        </div>
    </div>
</main>

<script>
document.getElementById('total_donasi_disalurkan').addEventListener('keyup', function(e) {
    let value = e.target.value.replace(/[^\d]/g, '');
    e.target.value = new Intl.NumberFormat('id-ID').format(value);
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>