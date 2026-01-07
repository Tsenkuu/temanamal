<?php
session_start();
require_once '../includes/config.php'; // Sesuaikan path

// Proteksi halaman, hanya untuk admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Validasi input
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header('Location: kelola_berita.php');
    exit();
}

$id_berita = (int)$_GET['id'];
$action = $_GET['action'];
$new_status = '';

// Tentukan status baru berdasarkan aksi
if ($action === 'setujui') {
    $new_status = 'published';
} elseif ($action === 'tolak') {
    $new_status = 'rejected';
} else {
    // Aksi tidak valid, kembali ke halaman kelola
    header('Location: kelola_berita.php');
    exit();
}

// Update status di database menggunakan prepared statement
$stmt = $mysqli->prepare("UPDATE berita SET status = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $id_berita);
$stmt->execute();
$stmt->close();

// Redirect kembali ke halaman manajemen berita
header('Location: kelola_berita.php');
exit();