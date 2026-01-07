<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Proses simpan pengaturan umum
if (isset($_POST['simpan_umum'])) {
    $total_disalurkan = preg_replace('/[^\d]/', '', $_POST['total_donasi_disalurkan']);
    $admin_wa_number = preg_replace('/[^\d]/', '', $_POST['admin_wa_number']);

    $stmt1 = $mysqli->prepare("INSERT INTO pengaturan (nama_pengaturan, nilai_pengaturan) VALUES ('total_donasi_disalurkan', ?) ON DUPLICATE KEY UPDATE nilai_pengaturan = ?");
    $stmt1->bind_param("ss", $total_disalurkan, $total_disalurkan);
    $stmt1->execute();

    $stmt2 = $mysqli->prepare("INSERT INTO pengaturan (nama_pengaturan, nilai_pengaturan) VALUES ('admin_wa_number', ?) ON DUPLICATE KEY UPDATE nilai_pengaturan = ?");
    $stmt2->bind_param("ss", $admin_wa_number, $admin_wa_number);
    $stmt2->execute();

    $_SESSION['success_message'] = "Pengaturan berhasil disimpan.";
    header("Location: pengaturan.php");
    exit();
}

// Proses reset bot
if (isset($_POST['reset_bot'])) {
    // URL ke endpoint reset di server bot Node.js Anda
    $reset_url = str_replace('kirim-pesan', 'reset-sesi', API_WA_URL);
    
    $data = ['token' => API_WA_TOKEN];

    $ch = curl_init($reset_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);
    if ($responseData && $responseData['success']) {
        $_SESSION['success_message'] = "Sesi bot berhasil direset. Silakan jalankan ulang aplikasi bot Anda dan pindai QR code baru.";
    } else {
        $_SESSION['error_message'] = "Gagal mereset sesi bot. Pastikan aplikasi bot sedang berjalan.";
    }
    header("Location: pengaturan.php");
    exit();
}
?> <?php require_once 'templates/footer_admin.php'; ?>