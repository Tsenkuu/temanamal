<?php
require_once 'includes/config.php';

$errors = [];
$page_title = "Login";

// Jika sudah login, redirect ke dashboard masing-masing
if (isset($_SESSION['admin_id'])) {
    header('Location: admin/index.php');
    exit();
}
if (isset($_SESSION['amil_id'])) {
    header('Location: amil/dashboard.php');
    exit();
}
if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Arahkan ke halaman utama jika user biasa sudah login
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username']; // Bisa username atau email
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $errors[] = "Username/Email dan password wajib diisi.";
    } else {
        $login_berhasil = false;

        // 1. Cek di tabel admin
        $stmt = $mysqli->prepare("SELECT id, nama_lengkap, password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_nama_lengkap'] = $admin['nama_lengkap'];
                $login_berhasil = true;
                header('Location: admin/dashboard.php');
                exit();
            }
        }

        // 2. Jika bukan admin, cek di tabel amil
        if (!$login_berhasil) {
            $stmt = $mysqli->prepare("SELECT id, nama_lengkap, password FROM amil WHERE username = ? AND status = 'Aktif'");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result_amil = $stmt->get_result();

            if ($result_amil && $result_amil->num_rows == 1) {
                $amil = $result_amil->fetch_assoc();
                if (password_verify($password, $amil['password'])) {
                    $_SESSION['amil_id'] = $amil['id'];
                    $_SESSION['amil_username'] = $username;
                    $_SESSION['amil_nama_lengkap'] = $amil['nama_lengkap'];
                    $login_berhasil = true;
                    header('Location: amil/dashboard.php');
                    exit();
                }
            }
        }

        // 3. Jika bukan amil, cek di tabel user (login dengan email)
        if (!$login_berhasil) {
            $stmt = $mysqli->prepare("SELECT id, nama_lengkap, password FROM user WHERE email = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result_user = $stmt->get_result();

            if ($result_user && $result_user->num_rows == 1) {
                $user = $result_user->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nama_lengkap'] = $user['nama_lengkap'];
                    $login_berhasil = true;
                    header('Location: index.php');
                    exit();
                }
            }
        }
        
        // Jika setelah semua pengecekan login tetap gagal
        if (!$login_berhasil) {
            $errors[] = "Akun tidak ditemukan atau password salah.";
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
                <img src="assets/images/logo.png" alt="Logo Lazismu" class="h-16 w-auto mx-auto mb-4">
                <h1 class="text-3xl font-bold text-dark-text">Selamat Datang</h1>
                <p class="text-gray-500 mt-2">Masuk untuk melanjutkan</p>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
                <p><?php echo $_SESSION['success_message']; ?></p>
            </div>
            <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username atau Email</label>
                    <input type="text" id="username" name="username" required
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                </div>
                <div>
                    <div class="flex justify-between items-center">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <a href="lupa_sandi.php" class="text-sm text-primary-orange hover:underline">Lupa Kata
                            Sandi?</a>
                    </div>
                    <input type="password" id="password" name="password" required
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                </div>
                <button type="submit"
                    class="w-full bg-primary-orange text-white text-lg font-bold py-3 rounded-full hover:bg-orange-600 transition duration-300 shadow-lg">
                    Login
                </button>
            </form>
        </div>
        <p class="text-center text-gray-500 mt-6">
            Belum punya akun? <a href="register.php" class="font-semibold text-primary-orange hover:underline">Daftar di
                sini</a>
        </p>
        <p class="text-center text-gray-500 mt-2">
            <a href="index.php" class="hover:text-primary-orange transition">&larr; Kembali ke Beranda</a>
        </p>
    </div>
</main>

<?php
// Memuat footer. Kita buat footer tidak terlihat di halaman login.
?>
<script>
// Sembunyikan footer di halaman login
document.addEventListener('DOMContentLoaded', function() {
    const footer = document.getElementById('kontak');
    if (footer) {
        footer.style.display = 'none';
    }
});
</script>
</body>

</html>