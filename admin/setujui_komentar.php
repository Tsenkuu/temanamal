<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $mysqli->prepare("UPDATE komentar SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        $_SESSION['message'] = 'Komentar berhasil disetujui.';
        $_SESSION['message_type'] = 'success';
    }
    $stmt->close();
}

header("Location: kelola_komentar.php");
exit();
?>
