<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Tambah Program Donasi Baru";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_program = trim($_POST['nama_program']);
    $deskripsi = trim($_POST['deskripsi']);
    $kategori = $_POST['kategori'];
    $target_donasi = preg_replace('/[^\d]/', '', $_POST['target_donasi']);
    $metode_pembayaran_ids = isset($_POST['metode_pembayaran_ids']) ? implode(',', $_POST['metode_pembayaran_ids']) : '';
    $nama_gambar = 'placeholder.png';

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../assets/uploads/program/";
        // Sanitize filename
        $image_name = basename($_FILES["gambar"]["name"]);
        $safe_image_name = preg_replace("/[^a-zA-Z0-9._-]/", "", $image_name);
        $nama_gambar = time() . '_' . $safe_image_name;
        $target_file = $target_dir . $nama_gambar;
        if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            // Handle upload error, maybe set a session error message
            $nama_gambar = 'placeholder.png';
        }
    }

    $stmt = $mysqli->prepare("INSERT INTO program (nama_program, deskripsi, kategori, metode_pembayaran_ids, target_donasi, gambar) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssds", $nama_program, $deskripsi, $kategori, $metode_pembayaran_ids, $target_donasi, $nama_gambar);
    if ($stmt->execute()) {
        // Optional: Set a success message to display on the next page
        // $_SESSION['success_message'] = "Program baru berhasil ditambahkan.";
        header("Location: kelola_program.php");
        exit();
    }
    $stmt->close();
}

require_once 'templates/header_admin.php';
?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="text-2xl font-bold text-dark-text"><?php echo $page_title; ?></h1>
            <p class="text-sm text-gray-500">Buat program donasi baru untuk ditampilkan kepada donatur.</p>
        </div>
    </div>

    <div class="content-card">
        <form action="tambah_program.php" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Kolom Kiri: Input Utama -->
                <div class="md:col-span-2 space-y-6">
                    <div>
                        <label for="nama_program" class="form-label">Nama Program</label>
                        <input type="text" class="form-input" id="nama_program" name="nama_program" required
                            placeholder="Contoh: Bantuan untuk Korban Banjir">
                    </div>
                    <div>
                        <label for="deskripsi" class="form-label">Deskripsi Program</label>
                        <textarea class="form-textarea" id="deskripsi" name="deskripsi" rows="8" required
                            placeholder="Jelaskan secara detail tujuan dan latar belakang program ini."></textarea>
                        <p class="text-xs text-gray-500 mt-1">Anda bisa menggunakan beberapa paragraf untuk deskripsi yang menarik.</p>
                    </div>
                </div>

                <!-- Kolom Kanan: Pengaturan & Gambar -->
                <div class="md:col-span-1 space-y-6">
                    <div>
                        <label for="kategori" class="form-label">Kategori Program</label>
                        <select class="form-select" id="kategori" name="kategori" required>
                            <option value="" disabled selected>Pilih Kategori</option>
                            <option value="Pendidikan">Pendidikan</option>
                            <option value="Kesehatan">Kesehatan</option>
                            <option value="Bencana Alam">Bencana Alam</option>
                            <option value="Sosial">Sosial</option>
                            <option value="Infrastruktur">Infrastruktur</option>
                            <option value="Zakat">Zakat</option>
                            <option value="Infak">Infak</option>
                        </select>
                    </div>
                    <div>
                        <label for="target_donasi" class="form-label">Target Donasi</label>
                        <div class="relative">
                             <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">Rp</span>
                            <input type="text" class="form-input pl-8" id="target_donasi" name="target_donasi" required placeholder="10.000.000">
                        </div>
                    </div>
                    <div>
                        <label for="gambar" class="form-label">Gambar Utama</label>
                        <input class="form-input-file" type="file" id="gambar" name="gambar">
                        <p class="text-xs text-gray-500 mt-1">Gunakan gambar yang relevan dengan program. Rasio 16:9 disarankan.</p>
                    </div>
                    <div>
                        <label class="form-label">Metode Pembayaran</label>
                        <div class="space-y-2 rounded-lg border border-gray-200 p-4 max-h-60 overflow-y-auto">
                            <?php
                            // Ambil semua metode pembayaran yang aktif
                            $metode_stmt = $mysqli->prepare("SELECT id, nama_metode FROM metode_pembayaran WHERE status = 'Aktif' ORDER BY nama_metode");
                            $metode_stmt->execute();
                            $metode_result = $metode_stmt->get_result();
                            if ($metode_result->num_rows > 0) {
                                while ($metode = $metode_result->fetch_assoc()) {
                                    echo '<div class="form-check">';
                                    echo '<input class="form-check-input" type="checkbox" name="metode_pembayaran_ids[]" value="' . $metode['id'] . '" id="metode_' . $metode['id'] . '">';
                                    echo '<label class="form-check-label" for="metode_' . $metode['id'] . '">' . htmlspecialchars($metode['nama_metode']) . '</label>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="text-sm text-gray-500">Tidak ada metode pembayaran aktif yang ditemukan.</p>';
                            }
                            $metode_stmt->close();
                            ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Pilih metode pembayaran yang akan ditampilkan untuk program ini. Jika tidak ada yang dipilih, semua metode pembayaran yang relevan akan ditampilkan secara default.</p>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
                <a href="kelola_program.php" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan Program</button>
            </div>
        </form>
    </div>
</main>

<script>
// Script untuk format angka rupiah pada input target donasi
document.getElementById('target_donasi').addEventListener('keyup', function(e) {
    // Hapus karakter selain angka
    let value = e.target.value.replace(/[^\d]/g, '');
    
    // Format sebagai mata uang IDR jika ada isinya
    if (value) {
        e.target.value = new Intl.NumberFormat('id-ID').format(value);
    }
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>
