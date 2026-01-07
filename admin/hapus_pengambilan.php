<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_riwayat = (int)$_GET['id'];
    $stmt = $mysqli->prepare("DELETE FROM riwayat_pengambilan WHERE id = ?");
    $stmt->bind_param("i", $id_riwayat);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Riwayat pengambilan berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus riwayat.";
    }
    $stmt->close();
} 
elseif (isset($_GET['hapus']) && $_GET['hapus'] == 'semua') {
    if ($mysqli->query("TRUNCATE TABLE riwayat_pengambilan")) {
        $_SESSION['success_message'] = "Semua riwayat pengambilan berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus semua riwayat.";
    }
}
else {
    $_SESSION['error_message'] = "Permintaan tidak valid.";
}

header("Location: riwayat_pengambilan.php");
exit();
?>