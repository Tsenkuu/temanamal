<?php
require_once 'includes/config.php';

$errors = [];
$page_title = "Registrasi Akun";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sapaan = trim($_POST['sapaan']); // Mengambil data sapaan
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    $no_telepon = trim($_POST['no_telepon']);

    // Validasi
    if (empty($nama_lengkap) || empty($email) || empty($password)) {
        $errors[] = "Nama, Email, dan Password tidak boleh kosong.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password minimal harus 6 karakter.";
    }
    if ($password !== $konfirmasi_password) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }

    // Cek apakah email sudah terdaftar
    $stmt_email = $mysqli->prepare("SELECT id FROM user WHERE email = ?");
    $stmt_email->bind_param("s", $email);
    $stmt_email->execute();
    $stmt_email->store_result();
    if ($stmt_email->num_rows > 0) {
        $errors[] = "Email sudah terdaftar. Silakan gunakan email lain.";
    }
    $stmt_email->close();

    // Cek apakah nomor telepon sudah terdaftar (jika diisi)
    if (!empty($no_telepon)) {
        $nomor_bersih = preg_replace('/[^\d]/', '', $no_telepon);
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

        $stmt_phone = $mysqli->prepare("SELECT id FROM user WHERE no_telepon = ? OR no_telepon = ?");
        $stmt_phone->bind_param("ss", $nomor_format_0, $nomor_format_62);
        $stmt_phone->execute();
        $stmt_phone->store_result();
        if ($stmt_phone->num_rows > 0) {
            $errors[] = "Nomor telepon sudah digunakan untuk akun lain.";
        }
        $stmt_phone->close();
    }


    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        
        // Menambahkan sapaan ke query INSERT
        $stmt_insert = $mysqli->prepare("INSERT INTO user (nama_lengkap, sapaan, email, password, no_telepon) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sssss", $nama_lengkap, $sapaan, $email, $password_hashed, $no_telepon);

        if ($stmt_insert->execute()) {
            $_SESSION['success_message'] = "Registrasi berhasil! Silakan login dengan akun Anda.";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Registrasi gagal. Silakan coba lagi.";
        }
        $stmt_insert->close();
    }
}

require_once 'includes/templates/header.php';
?>

<main class="flex items-center justify-center min-h-screen bg-light-bg px-6 py-12">
    <div class="w-full max-w-md">
        <div class="bg-white p-8 md:p-10 rounded-2xl shadow-lg">
            <div class="text-center mb-8">
                <img src="assets/images/logo.png" alt="Logo Lazismu" class="h-16 w-auto mx-auto mb-4">
                <h1 class="text-3xl font-bold text-dark-text">Buat Akun Baru</h1>
                <p class="text-gray-500 mt-2">Daftar untuk mulai berdonasi.</p>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- PENAMBAHAN: Kolom Sapaan -->
                    <div class="md:col-span-1">
                        <label for="sapaan" class="block text-sm font-medium text-gray-700">Sapaan</label>
                        <select id="sapaan" name="sapaan"
                            class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                            <option>Bapak</option>
                            <option>Ibu</option>
                            <option>Kak</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label for="nama_lengkap" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" required
                            class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                    </div>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" required
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                </div>
                <div>
                    <label for="no_telepon" class="block text-sm font-medium text-gray-700">No. Telepon
                        (WhatsApp)</label>
                    <input type="tel" id="no_telepon" name="no_telepon"
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange"
                        required>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password" name="password" required
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                </div>
                <div>
                    <label for="konfirmasi_password" class="block text-sm font-medium text-gray-700">Konfirmasi
                        Password</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" required
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                </div>
                <button type="submit"
                    class="w-full bg-primary-orange text-white text-lg font-bold py-3 rounded-full hover:bg-orange-600 transition duration-300 shadow-lg">
                    Daftar
                </button>
            </form>
        </div>
        <p class="text-center text-gray-500 mt-6">
            Sudah punya akun? <a href="login.php" class="font-semibold text-primary-orange hover:underline">Login di
                sini</a>
        </p>
    </div>
</main>

<?php
?>
<script>
// Sembunyikan footer di halaman registrasi
document.addEventListener('DOMContentLoaded', function() {
    const footer = document.getElementById('kontak');
    if (footer) {
        footer.style.display = 'none';
    }
});
</script>
</body>

</html>