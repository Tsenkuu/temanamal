<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_berita = isset($_POST['id_berita']) ? (int)$_POST['id_berita'] : 0;
    $nama_pengirim = trim($_POST['nama_pengirim']);
    $isi_komentar = trim($_POST['isi_komentar']);
    
    // Validasi dasar
    if ($id_berita > 0 && !empty($nama_pengirim) && !empty($isi_komentar)) {
        $stmt = $mysqli->prepare("INSERT INTO komentar (id_berita, nama_pengirim, isi_komentar, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("iss", $id_berita, $nama_pengirim, $isi_komentar);
        
        if ($stmt->execute()) {
            $_SESSION['comment_message'] = 'Terima kasih! Komentar Anda telah dikirim dan akan ditampilkan setelah disetujui oleh admin.';
            $_SESSION['comment_status'] = 'success';
        } else {
            $_SESSION['comment_message'] = 'Maaf, terjadi kesalahan saat mengirim komentar.';
            $_SESSION['comment_status'] = 'error';
        }
        $stmt->close();
    } else {
        $_SESSION['comment_message'] = 'Nama dan isi komentar tidak boleh kosong.';
        $_SESSION['comment_status'] = 'error';
    }
    
    // Alihkan kembali ke halaman berita
    header("Location: " . BASE_URL . "/berita/" . $id_berita . "#komentar");
    exit();
    
} else {
    // Jika diakses langsung, alihkan ke halaman utama
    header("Location: " . BASE_URL);
    exit();
}
?>
