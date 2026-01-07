<?php
session_start();
require_once 'includes/config.php'; // Pastikan ada $mysqli

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['bukti_pembayaran']) && isset($_POST['invoice_id'])) {
    $invoice_id = $_POST['invoice_id'];
    $file = $_FILES['bukti_pembayaran'];

    // Validasi dasar file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Terjadi error saat mengupload file. Kode Error: " . $file['error']);
    }
    $nama_file = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($file["name"]));
    $target_dir = "assets/uploads/bukti/";
    $target_file = $target_dir . $nama_file;
// Pastikan folder ada
// Pastikan folder ada
if (!is_dir($target_dir)) {
    if (!mkdir($target_dir, 0755, true)) {
        die("Error: Gagal membuat folder upload. Cek izin parent folder.");
    }
}


    // Pastikan folder bisa ditulis
    if (!is_writable($target_dir)) {
        die("Error: Direktori upload tidak bisa ditulisi meski 755. 
             Pastikan owner folder sama dengan user PHP/webserver.");
    }

    // Pindahkan file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // Simpan ke database
        $stmt = $mysqli->prepare("UPDATE donasi SET bukti_pembayaran = ?, status = 'Menunggu Konfirmasi' WHERE invoice_id = ?");
        if (!$stmt) {
            die("Query error: " . $mysqli->error);
        }
        $stmt->bind_param("ss", $nama_file, $invoice_id);
        $stmt->execute();
        $stmt->close();

        // Ambil detail donasi
        $stmt_get = $mysqli->prepare("SELECT nama_donatur, nominal FROM donasi WHERE invoice_id = ?");
        $stmt_get->bind_param("s", $invoice_id);
        $stmt_get->execute();
        $donasi = $stmt_get->get_result()->fetch_assoc();
        $stmt_get->close();

        if ($donasi) {
            $pesan_notifikasi = "🔔 *Konfirmasi Donasi Baru*\n\n" .
                "*Invoice ID:* {$invoice_id}\n" .
                "*Nama Donatur:* {$donasi['nama_donatur']}\n" .
                "*Nominal:* Rp " . number_format($donasi['nominal'], 0, ',', '.') . "\n\n" .
                "Silakan cek bukti pembayaran di halaman admin.";
            kirimNotifikasiWA(ADMIN_WA_NUMBER, $pesan_notifikasi);
        }

        unset($_SESSION['last_invoice_id']);
        header('Location: terima_kasih.php');
        exit();
    } else {
        error_log("Upload gagal: dari " . $file["tmp_name"] . " ke " . $target_file);
        die("Maaf, terjadi kesalahan saat menyimpan bukti pembayaran Anda. Coba cek log error.");
    }
} else {
    header('Location: donasi.php');
    exit();
}
