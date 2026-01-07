<?php
require_once '../includes/config.php';

// Pengecekan login admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Konfirmasi Donasi";

// --- LOGIKA AKSI (ACC ATAU TOLAK) ---

// 1. Proses ACC Donasi
if (isset($_GET['acc']) && !empty($_GET['acc'])) {
    $id_donasi = (int)$_GET['acc'];
    
    $mysqli->begin_transaction();
    try {
        // Ambil detail donasi untuk notifikasi dan update program
        $stmt_get = $mysqli->prepare("SELECT nominal, id_program, nama_donatur, invoice_id FROM donasi WHERE id = ?");
        $stmt_get->bind_param("i", $id_donasi);
        $stmt_get->execute();
        $donasi = $stmt_get->get_result()->fetch_assoc();
        
        // Update donasi terkumpul di program jika ada
        if($donasi && $donasi['id_program']){
            $stmt_prog = $mysqli->prepare("UPDATE program SET donasi_terkumpul = donasi_terkumpul + ? WHERE id = ?");
            $stmt_prog->bind_param("di", $donasi['nominal'], $donasi['id_program']);
            $stmt_prog->execute();
        }

        // Update status donasi menjadi 'Selesai'
        $stmt_update = $mysqli->prepare("UPDATE donasi SET status = 'Selesai' WHERE id = ?");
        $stmt_update->bind_param("i", $id_donasi);
        $stmt_update->execute();
        
        $mysqli->commit();
        $_SESSION['success_message'] = "Donasi berhasil dikonfirmasi.";

        // Kirim Notifikasi WhatsApp ke Admin
        if ($donasi) {
            $pesan_notifikasi = "✅ *Donasi Dikonfirmasi*\n\n" .
                                "Anda telah berhasil mengonfirmasi donasi:\n\n" .
                                "*Invoice ID:* " . $donasi['invoice_id'] . "\n" .
                                "*Nama Donatur:* " . $donasi['nama_donatur'] . "\n" .
                                "*Nominal:* Rp " . number_format($donasi['nominal'], 0, ',', '.');
            kirimNotifikasiWA(ADMIN_WA_NUMBER, $pesan_notifikasi);
        }

    } catch (Exception $e) {
        $mysqli->rollback();
        $_SESSION['error_message'] = "Gagal mengonfirmasi donasi.";
    }
    header("Location: konfirmasi_donasi.php");
    exit();
}

// 2. Proses Tolak Donasi
if (isset($_GET['tolak']) && !empty($_GET['tolak'])) {
    $id_donasi = (int)$_GET['tolak'];
    $stmt = $mysqli->prepare("UPDATE donasi SET status = 'Dibatalkan' WHERE id = ?");
    $stmt->bind_param("i", $id_donasi);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Donasi telah ditolak/dibatalkan.";
    } else {
        $_SESSION['error_message'] = "Gagal menolak donasi.";
    }
    header("Location: konfirmasi_donasi.php");
    exit();
}


// Ambil data donasi yang menunggu konfirmasi
$result_pending = $mysqli->query("SELECT * FROM donasi WHERE status = 'Menunggu Konfirmasi' ORDER BY created_at ASC");

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

    <div class="content-card mt-6">
        <div class="table-wrapper">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Donatur</th>
                        <th scope="col" class="px-6 py-3 text-right">Nominal</th>
                        <th scope="col" class="px-6 py-3 text-center">Bukti</th>
                        <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_pending && $result_pending->num_rows > 0): ?>
                    <?php while($donasi = $result_pending->fetch_assoc()): ?>
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-dark-text">
                                <?php echo htmlspecialchars($donasi['nama_donatur']); ?></p>
                            <p class="text-xs">Inv: <?php echo htmlspecialchars($donasi['invoice_id']); ?> |
                                <?php echo date('d M Y, H:i', strtotime($donasi['created_at'])); ?></p>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-green-600">Rp
                            <?php echo number_format($donasi['nominal'], 0, ',', '.'); ?></td>
                        <td class="px-6 py-4 text-center">
                            <button type="button" class="btn-secondary" data-modal-toggle="buktiModal"
                                data-bukti-img="../assets/uploads/bukti/<?php echo htmlspecialchars($donasi['bukti_pembayaran']); ?>">
                                Lihat
                            </button>
                        </td>
                        <td class="px-6 py-4 text-center flex justify-center gap-2">
                            <a href="?acc=<?php echo $donasi['id']; ?>"
                                class="btn-icon bg-green-100 text-green-600 hover:bg-green-200"
                                onclick="return confirm('Anda yakin ingin menyetujui donasi ini?');" title="Setujui"><i
                                    class="bi bi-check-lg"></i></a>
                            <a href="?tolak=<?php echo $donasi['id']; ?>"
                                class="btn-icon bg-red-100 text-red-600 hover:bg-red-200"
                                onclick="return confirm('Anda yakin ingin menolak donasi ini?');" title="Tolak"><i
                                    class="bi bi-x-lg"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada donasi yang menunggu
                            konfirmasi.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- PENAMBAHAN: Modal untuk Menampilkan Bukti Pembayaran -->
<div class="modal fade" id="buktiModal" tabindex="-1" aria-labelledby="buktiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buktiModalLabel">Bukti Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="gambarBukti" src="" class="img-fluid" alt="Bukti Pembayaran">
            </div>
        </div>
    </div>
</div>


<script>
// Logika Modal Generik
document.addEventListener('DOMContentLoaded', function() {
    const modalToggles = document.querySelectorAll('[data-modal-toggle]');
    modalToggles.forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-modal-toggle');
            const modal = document.getElementById(modalId);
            if (modal) {
                // Mengisi data spesifik untuk modal bukti
                if (modalId === 'buktiModal') {
                    const imageUrl = button.getAttribute('data-bukti-img');
                    const modalImage = modal.querySelector('#gambarBukti');
                    if (modalImage && imageUrl) {
                        modalImage.src = imageUrl;
                    }
                }
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        });
    });

    const modalCloses = document.querySelectorAll('[data-modal-hide]');
    modalCloses.forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-modal-hide');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    });
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>