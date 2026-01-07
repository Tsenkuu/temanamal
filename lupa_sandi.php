<?php
require_once 'includes/config.php';
$page_title = "Lupa Kata Sandi";
require_once 'includes/templates/header.php';

$errors = [];
$success_message = '';
$show_form = true; // Variabel untuk mengontrol tampilan form

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil dan bersihkan nomor yang diinput pengguna
    $input_nomor = trim($_POST['no_telepon']);
    $nomor_bersih = preg_replace('/[^\d]/', '', $input_nomor);

    // 2. Siapkan dua format nomor: dengan awalan '0' dan '62'
    $nomor_format_0 = '';
    $nomor_format_62 = '';

    if (substr($nomor_bersih, 0, 2) == '62') {
        $nomor_format_62 = $nomor_bersih;
        $nomor_format_0 = '0' . substr($nomor_bersih, 2);
    } else {
        if (substr($nomor_bersih, 0, 1) == '0') {
            $nomor_format_0 = $nomor_bersih;
            $nomor_format_62 = '62' . substr($nomor_bersih, 1);
        } else {
            $nomor_format_0 = '0' . $nomor_bersih;
            $nomor_format_62 = '62' . $nomor_bersih;
        }
    }
    
    // 3. Cari di database menggunakan kedua format
    $stmt = $mysqli->prepare("SELECT id FROM user WHERE no_telepon = ? OR no_telepon = ?");
    $stmt->bind_param("ss", $nomor_format_0, $nomor_format_62);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id);
        $stmt->fetch();

        $token = rand(100000, 999999); // 6 digit kode verifikasi
        $expiry = date("Y-m-d H:i:s", strtotime('+15 minutes')); // Token berlaku 15 menit

        $stmt_update = $mysqli->prepare("UPDATE user SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        $stmt_update->bind_param("ssi", $token, $expiry, $user_id);
        $stmt_update->execute();

        $pesan = "Kode verifikasi Lazismu Anda adalah: *$token*. Jangan berikan kode ini kepada siapapun. Kode berlaku selama 15 menit.";
        kirimNotifikasiWA($nomor_format_62, $pesan);

        $_SESSION['reset_user_id'] = $user_id;
        
        // Tampilkan pesan sukses dan sembunyikan form
        $success_message = "Kode verifikasi telah berhasil dikirim ke nomor WhatsApp Anda.";
        $show_form = false;

    } else {
        $errors[] = "Nomor WhatsApp tidak terdaftar di sistem kami.";
    }
    $stmt->close();
}
?>
<main class="flex items-center justify-center min-h-screen bg-light-bg px-6">
    <div class="w-full max-w-md">
        <div class="bg-white p-8 md:p-10 rounded-2xl shadow-lg">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-dark-text">Lupa Kata Sandi</h1>
                <?php if ($show_form): ?>
                <p class="text-gray-500 mt-2">Masukkan nomor WhatsApp Anda yang terdaftar. Kami akan mengirimkan kode
                    verifikasi.</p>
                <?php endif; ?>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                <?php foreach ($errors as $error): ?><p><?php echo $error; ?></p><?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg text-center"
                role="alert">
                <p class="font-bold">Berhasil!</p>
                <p><?php echo $success_message; ?></p>
                <a href="reset_sandi.php"
                    class="mt-4 inline-block w-full bg-primary-orange text-white font-bold py-3 rounded-full hover:bg-orange-600 transition duration-300 shadow-lg">
                    Lanjutkan
                </a>
            </div>
            <?php endif; ?>

            <?php if ($show_form): ?>
            <form action="lupa_sandi.php" method="POST" class="space-y-6">
                <div>
                    <label for="no_telepon" class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
                    <input type="tel" id="no_telepon" name="no_telepon" required
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange"
                        placeholder="Contoh: 081234567890">
                </div>
                <button type="submit"
                    class="w-full bg-primary-orange text-white text-lg font-bold py-3 rounded-full hover:bg-orange-600 transition duration-300 shadow-lg">
                    Kirim Kode Verifikasi
                </button>
            </form>
            <?php endif; ?>
        </div>
        <p class="text-center text-gray-500 mt-6">
            <a href="login.php" class="hover:text-primary-orange transition">&larr; Kembali ke Halaman Login</a>
        </p>
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