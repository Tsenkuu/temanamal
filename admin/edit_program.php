<?php
// Memuat file konfigurasi, yang seharusnya sudah memanggil session_start()
require_once '../includes/config.php';

// Pengecekan login admin (sangat direkomendasikan)
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php'); // Arahkan ke halaman login utama jika belum login
    exit();
}

$page_title = "Edit Program Donasi";
$id_program = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_program === 0) {
    header("Location: kelola_program.php");
    exit;
}

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_program = trim($_POST['nama_program']);
    $deskripsi = trim($_POST['deskripsi']);
    $target_donasi = preg_replace('/[^\d]/', '', $_POST['target_donasi']);
    // PENAMBAHAN: Mengambil dan membersihkan data donasi terkumpul
    $donasi_terkumpul = preg_replace('/[^\d]/', '', $_POST['donasi_terkumpul']);
    $gambar_lama = $_POST['gambar_lama'];
    $nama_gambar_baru = $gambar_lama;

    // Logika upload gambar baru jika ada
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../assets/uploads/program/";
        $nama_gambar_baru = time() . '_' . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $nama_gambar_baru;
        
        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            if ($gambar_lama != 'placeholder.png' && file_exists($target_dir . $gambar_lama)) {
                unlink($target_dir . $gambar_lama);
            }
        } else {
            $nama_gambar_baru = $gambar_lama;
        }
    }

    // PERBAIKAN: Memperbarui query UPDATE untuk menyertakan donasi_terkumpul dan metode_pembayaran_ids
    $metode_pembayaran_ids = isset($_POST['metode_pembayaran_ids']) ? implode(',', $_POST['metode_pembayaran_ids']) : '';
    $stmt = $mysqli->prepare("UPDATE program SET nama_program = ?, deskripsi = ?, target_donasi = ?, donasi_terkumpul = ?, metode_pembayaran_ids = ?, gambar = ? WHERE id = ?");
    $stmt->bind_param("ssddssi", $nama_program, $deskripsi, $target_donasi, $donasi_terkumpul, $metode_pembayaran_ids, $nama_gambar_baru, $id_program);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Program berhasil diperbarui.";
        header("Location: kelola_program.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui program.";
    }
    $stmt->close();
}

// PERBAIKAN: Mengambil data donasi_terkumpul dan metode_pembayaran_ids dari database
$stmt_select = $mysqli->prepare("SELECT nama_program, deskripsi, gambar, target_donasi, donasi_terkumpul, metode_pembayaran_ids FROM program WHERE id = ?");
$stmt_select->bind_param("i", $id_program);
$stmt_select->execute();
$result = $stmt_select->get_result();
$program = $result->fetch_assoc();
$stmt_select->close();

if (!$program) {
    header("Location: kelola_program.php");
    exit;
}

require_once 'templates/header_admin.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_admin.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
            </div>

            <?php
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']);
            }
            ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="edit_program.php?id=<?php echo $id_program; ?>" method="POST"
                        enctype="multipart/form-data">
                        <input type="hidden" name="gambar_lama"
                            value="<?php echo htmlspecialchars($program['gambar']); ?>">

                        <div class="mb-3">
                            <label for="nama_program" class="form-label">Nama Program</label>
                            <input type="text" class="form-control" id="nama_program" name="nama_program"
                                value="<?php echo htmlspecialchars($program['nama_program']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5"
                                required><?php echo htmlspecialchars($program['deskripsi']); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="target_donasi" class="form-label">Target Donasi (Rp)</label>
                                <input type="text" class="form-control" id="target_donasi" name="target_donasi"
                                    value="<?php echo number_format($program['target_donasi'], 0, ',', '.'); ?>"
                                    required>
                            </div>
                            <!-- PENAMBAHAN: Kolom untuk mengedit donasi terkumpul -->
                            <div class="col-md-6 mb-3">
                                <label for="donasi_terkumpul" class="form-label">Donasi Terkumpul (Rp)</label>
                                <input type="text" class="form-control" id="donasi_terkumpul" name="donasi_terkumpul"
                                    value="<?php echo number_format($program['donasi_terkumpul'], 0, ',', '.'); ?>"
                                    required>
                                <small class="form-text text-muted">Nilai ini akan bertambah otomatis dari donasi
                                    online, namun bisa disesuaikan manual di sini.</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="metode_pembayaran_ids" class="form-label">Metode Pembayaran yang Ditampilkan</label>
                            <div>
                                <?php
                                $selected_metode = explode(',', $program['metode_pembayaran_ids']);
                                $metode_stmt = $mysqli->prepare("SELECT id, nama_metode FROM metode_pembayaran WHERE status = 'Aktif'");
                                $metode_stmt->execute();
                                $metode_result = $metode_stmt->get_result();
                                while ($metode = $metode_result->fetch_assoc()) {
                                    $checked = in_array($metode['id'], $selected_metode) ? 'checked' : '';
                                    echo '<div class="form-check">';
                                    echo '<input class="form-check-input" type="checkbox" name="metode_pembayaran_ids[]" value="' . $metode['id'] . '" id="metode_' . $metode['id'] . '" ' . $checked . '>';
                                    echo '<label class="form-check-label" for="metode_' . $metode['id'] . '">';
                                    echo htmlspecialchars($metode['nama_metode']);
                                    echo '</label>';
                                    echo '</div>';
                                }
                                $metode_stmt->close();
                                ?>
                            </div>
                            <small class="form-text text-muted">Pilih metode pembayaran yang akan ditampilkan untuk program ini. Jika tidak dipilih, semua metode aktif akan ditampilkan.</small>
                        </div>
                        <div class="mb-3">
                            <label for="gambar" class="form-label">Ganti Gambar (Opsional)</label>
                            <input class="form-control" type="file" id="gambar" name="gambar">
                            <small class="form-text text-muted">Gambar saat ini:</small><br>
                            <img src="../assets/uploads/program/<?php echo htmlspecialchars($program['gambar']); ?>"
                                class="mt-2" style="max-width: 200px; border-radius: 0.5rem;">
                        </div>
                        <hr>
                        <a href="kelola_program.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Script untuk memformat input angka dengan titik ribuan
document.getElementById('target_donasi').addEventListener('keyup', function(e) {
    let value = e.target.value.replace(/[^\d]/g, '');
    e.target.value = new Intl.NumberFormat('id-ID').format(value);
});

// PENAMBAHAN: Script untuk memformat input donasi terkumpul
document.getElementById('donasi_terkumpul').addEventListener('keyup', function(e) {
    let value = e.target.value.replace(/[^\d]/g, '');
    e.target.value = new Intl.NumberFormat('id-ID').format(value);
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>
