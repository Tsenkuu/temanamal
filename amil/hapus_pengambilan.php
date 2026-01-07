<?php
require_once '../includes/config.php';

if (!isset($_SESSION['amil_id'])) {
    header('Location: ../login.php');
    exit();
}
$id_amil_login = $_SESSION['amil_id'];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_riwayat = (int)$_GET['id'];

    // Hapus riwayat HANYA jika milik amil yang sedang login
    $stmt = $mysqli->prepare("DELETE FROM riwayat_pengambilan WHERE id = ? AND id_amil = ?");
    $stmt->bind_param("ii", $id_riwayat, $id_amil_login);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success_message'] = "Riwayat pengambilan berhasil dihapus.";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus riwayat (mungkin bukan milik Anda).";
        }
    } else {
        $_SESSION['error_message'] = "Gagal menghapus riwayat.";
    }
    $stmt->close();
} else {
    $_SESSION['error_message'] = "Permintaan tidak valid.";
}

header("Location: riwayat_pengambilan.php");
exit();
?>