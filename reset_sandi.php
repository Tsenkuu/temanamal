<?php
require_once 'includes/config.php';

if (!isset($_SESSION['reset_user_id'])) {
    header('Location: lupa_sandi.php');
    exit();
}

$page_title = "Reset Kata Sandi";
$user_id = $_SESSION['reset_user_id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = trim($_POST['token']);
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    if (empty($token) || empty($password_baru) || empty($konfirmasi_password)) {
        $errors[] = "Semua kolom wajib diisi.";
    } elseif ($password_baru !== $konfirmasi_password) {
        $errors[] = "Password baru dan konfirmasi tidak cocok.";
    } elseif (strlen($password_baru) < 6) {
        $errors[] = "Password baru minimal harus 6 karakter.";
    } else {
        // Cek token di database
        $stmt = $mysqli->prepare("SELECT reset_token, reset_token_expiry FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($db_token, $db_expiry);
        $stmt->fetch();

        if ($stmt->num_rows > 0 && $db_token == $token && strtotime($db_expiry) > time()) {
            // Token valid, update password
            $password_hashed = password_hash($password_baru, PASSWORD_DEFAULT);
            $stmt_update = $mysqli->prepare("UPDATE user SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            $stmt_update->bind_param("si", $password_hashed, $user_id);
            
            if ($stmt_update->execute()) {
                unset($_SESSION['reset_user_id']);
                $_SESSION['success_message'] = "Password Anda telah berhasil direset. Silakan login kembali.";
                header('Location: login.php');
                exit();
            } else {
                $errors[] = "Gagal memperbarui password.";
            }
        } else {
            $errors[] = "Kode verifikasi tidak valid atau sudah kedaluwarsa.";
        }
        $stmt->close();
    }
}

require_once 'includes/templates/header.php';
?>
<main class="flex items-center justify-center min-h-screen bg-light-bg px-6">
    <div class="w-full max-w-md">
        <div class="bg-white p-8 md:p-10 rounded-2xl shadow-lg">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-dark-text">Atur Ulang Kata Sandi</h1>
                <p class="text-gray-500 mt-2">Masukkan kode verifikasi yang kami kirim ke WhatsApp Anda dan atur kata
                    sandi baru.</p>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                <?php foreach ($errors as $error): ?><p><?php echo $error; ?></p><?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form action="reset_sandi.php" method="POST" class="space-y-6">
                <div>
                    <label for="token" class="block text-sm font-medium text-gray-700">Kode Verifikasi (6 Digit)</label>
                    <input type="text" id="token" name="token" required
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                </div>
                <div>
                    <label for="password_baru" class="block text-sm font-medium text-gray-700">Password Baru</label>
                    <input type="password" id="password_baru" name="password_baru" required
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                </div>
                <div>
                    <label for="konfirmasi_password" class="block text-sm font-medium text-gray-700">Konfirmasi Password
                        Baru</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" required
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                </div>
                <button type="submit"
                    class="w-full bg-primary-orange text-white text-lg font-bold py-3 rounded-full hover:bg-orange-600 transition duration-300 shadow-lg">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</main>
<?php require_once 'includes/templates/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const footer = document.getElementById('kontak');
    if (footer) {
        footer.style.display = 'none';
    }
});
</script>
</body>

</html>