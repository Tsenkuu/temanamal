<?php
// Memuat file konfigurasi
require_once 'includes/config.php';

try {
    // Membuat instance dari Midtrans Notification
    $notif = new \Midtrans\Notification();
} catch (Exception $e) {
    // Hentikan eksekusi jika terjadi error saat membuat instance
    // (misalnya, karena Server Key tidak valid)
    http_response_code(500);
    exit('Error: ' . $e->getMessage());
}

// Ambil detail notifikasi dari Midtrans
$transaction_status = $notif->transaction_status;
$payment_type = $notif->payment_type;
$order_id = $notif->order_id; // Ini adalah invoice_id kita
$fraud_status = $notif->fraud_status;

// Tentukan status default
$status_to_update = 'Menunggu Pembayaran';

// Logika untuk menentukan status donasi berdasarkan notifikasi Midtrans
if ($transaction_status == 'capture' || $transaction_status == 'settlement') {
    // 'settlement' (untuk transfer bank, e-wallet, dll) dan 'capture' (untuk kartu kredit)
    // berarti pembayaran telah berhasil dan dana sudah diterima.
    $status_to_update = 'Selesai';
} else if ($transaction_status == 'pending') {
    // 'pending' berarti donatur sudah memilih metode pembayaran
    // tapi belum menyelesaikan pembayaran (misal, belum transfer).
    $status_to_update = 'Menunggu Pembayaran';
} else if ($transaction_status == 'deny' || $transaction_status == 'expire' || $transaction_status == 'cancel') {
    // 'deny' (ditolak), 'expire' (kadaluarsa), atau 'cancel' (dibatalkan)
    // berarti donasi gagal.
    $status_to_update = 'Dibatalkan';
}

// Mulai transaksi database untuk menjaga integritas data
$mysqli->begin_transaction();

try {
    // 1. Update status donasi di tabel 'donasi'
    $stmt_update_donasi = $mysqli->prepare("UPDATE donasi SET status = ? WHERE invoice_id = ?");
    $stmt_update_donasi->bind_param("ss", $status_to_update, $order_id);
    $stmt_update_donasi->execute();
    $stmt_update_donasi->close();

    // 2. Jika pembayaran berhasil, update nominal terkumpul di tabel 'program'
    if ($status_to_update == 'Selesai') {
        // Ambil detail donasi (nominal dan id_program) dari database
        $stmt_get_donasi = $mysqli->prepare("SELECT nominal, id_program FROM donasi WHERE invoice_id = ?");
        $stmt_get_donasi->bind_param("s", $order_id);
        $stmt_get_donasi->execute();
        $result_donasi = $stmt_get_donasi->get_result();
        
        if ($result_donasi->num_rows > 0) {
            $donasi = $result_donasi->fetch_assoc();
            $nominal_donasi = $donasi['nominal'];
            $id_program_donasi = $donasi['id_program'];

            // Jika donasi ini ditujukan untuk program spesifik (id_program tidak null)
            if ($id_program_donasi !== NULL) {
                // Update kolom 'donasi_terkumpul' di tabel 'program'
                $stmt_update_program = $mysqli->prepare("UPDATE program SET donasi_terkumpul = donasi_terkumpul + ? WHERE id = ?");
                $stmt_update_program->bind_param("di", $nominal_donasi, $id_program_donasi);
                $stmt_update_program->execute();
                $stmt_update_program->close();
            }
        }
        $stmt_get_donasi->close();
    }
    
    // Jika semua query berhasil, commit transaksi
    $mysqli->commit();

} catch (mysqli_sql_exception $exception) {
    // Jika ada error, batalkan semua perubahan
    $mysqli->rollback();
    // (Opsional) Catat error ke log untuk debugging
    // file_put_contents('db_error_log.txt', $exception->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    exit();
}

// Kirim respons OK (HTTP 200) ke Midtrans untuk memberitahu bahwa notifikasi sudah diterima
http_response_code(200);
?>