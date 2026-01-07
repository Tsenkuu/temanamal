<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data sapaan dari form
    $sapaan = trim($_POST['sapaan']);
    $nama_donatur = trim($_POST['nama_donatur']);
    // Gabungkan sapaan dan nama
    $nama_lengkap_donatur = $sapaan . ' ' . $nama_donatur;

    $nominal = (float) preg_replace('/[^\d]/', '', $_POST['nominal']);
    $id_program = !empty($_POST['id_program']) && is_numeric($_POST['id_program']) ? (int)$_POST['id_program'] : NULL;
    $kontak_donatur = trim($_POST['kontak_donatur']);
    $metode_pembayaran_id = (int)$_POST['metode_pembayaran_id'];
    
    $kode_unik = rand(100, 999);
    $total_transfer = $nominal;
    $invoice_id = 'LZM' . time() . rand(10, 99);

    $stmt = $mysqli->prepare("INSERT INTO donasi (invoice_id, nama_donatur, sapaan, kontak_donatur, id_program, nominal, kode_unik, total_transfer, metode_pembayaran_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    // Perbarui bind_param untuk menyertakan sapaan
    $stmt->bind_param("ssssiidis", $invoice_id, $nama_lengkap_donatur, $sapaan, $kontak_donatur, $id_program, $nominal, $kode_unik, $total_transfer, $metode_pembayaran_id);
    
    if ($stmt->execute()) {
        $_SESSION['last_invoice_id'] = $invoice_id;
        header('Location: konfirmasi_pembayaran.php');
        exit();
    } else {
        die("Error: " . $stmt->error);
    }
}
?>
