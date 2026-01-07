<?php
// Memuat file konfigurasi
require_once '../includes/config.php';

// Pengecekan login user
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = "Dashboard Saya";
$user_id = $_SESSION['user_id'];

// Ambil data user
$stmt_user = $mysqli->prepare("SELECT email, no_telepon FROM user WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

// [FIX] Ambil riwayat donasi HANYA berdasarkan kontak (email/telepon) karena kolom id_user tidak ada.
$sql_donasi = "SELECT d.created_at, d.nominal, d.status, p.nama_program 
               FROM donasi d 
               LEFT JOIN program p ON d.id_program = p.id 
               WHERE (d.kontak_donatur = ? OR d.kontak_donatur = ?)
               ORDER BY d.created_at DESC";
$stmt_donasi = $mysqli->prepare($sql_donasi);
// Sesuaikan binding parameter karena kondisi WHERE berubah
$stmt_donasi->bind_param("ss", $user_data['email'], $user_data['no_telepon']);
$stmt_donasi->execute();
$result_donasi = $stmt_donasi->get_result();

$total_donasi = 0;
$jumlah_transaksi = 0;
$donasi_list = [];

if ($result_donasi) {
    $jumlah_transaksi = $result_donasi->num_rows;
    while($donasi_row = $result_donasi->fetch_assoc()){
        $donasi_list[] = $donasi_row; // Simpan ke array dulu
        if($donasi_row['status'] == 'Selesai'){
            $total_donasi += $donasi_row['nominal'];
        }
    }
}


require_once 'templates/header_user.php';
?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Greeting and Profile Card -->
    <div class="bg-gradient-to-br from-orange-400 to-primary-orange text-white rounded-2xl shadow-lg p-6 mb-8 flex items-center justify-between">
        <div>
            <p class="font-medium">Selamat Datang,</p>
            <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($user_nama); ?>!</h1>
        </div>
        <a href="edit_profil.php" class="flex-shrink-0">
            <img class="h-16 w-16 rounded-full object-cover border-4 border-white/50" src="<?php echo BASE_URL; ?>assets/uploads/user/<?php echo htmlspecialchars($user_foto); ?>" alt="Foto Profil">
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-md flex items-center gap-4">
            <div class="bg-green-100 text-green-600 p-3 rounded-full">
                <i class="bi bi-wallet2 text-2xl"></i>
            </div>
            <div>
                <p class="text-sm text-text-muted">Total Donasi Anda</p>
                <p class="text-xl font-bold">Rp <?php echo number_format($total_donasi, 0, ',', '.'); ?></p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-md flex items-center gap-4">
            <div class="bg-blue-100 text-blue-600 p-3 rounded-full">
                <i class="bi bi-arrow-repeat text-2xl"></i>
            </div>
            <div>
                <p class="text-sm text-text-muted">Jumlah Transaksi</p>
                <p class="text-xl font-bold"><?php echo $jumlah_transaksi; ?> Kali</p>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8 text-center">
        <a href="kalkulator_zakat.php" class="bg-white p-4 rounded-2xl shadow-md hover:shadow-lg transition-shadow">
            <i class="bi bi-calculator-fill text-3xl text-primary-orange"></i>
            <p class="text-sm font-semibold mt-2">Hitung Zakat</p>
        </a>
        <a href="edit_profil.php" class="bg-white p-4 rounded-2xl shadow-md hover:shadow-lg transition-shadow">
            <i class="bi bi-person-circle text-3xl text-primary-orange"></i>
            <p class="text-sm font-semibold mt-2">Edit Profil</p>
        </a>
        <a href="ganti_sandi.php" class="bg-white p-4 rounded-2xl shadow-md hover:shadow-lg transition-shadow">
            <i class="bi bi-key-fill text-3xl text-primary-orange"></i>
            <p class="text-sm font-semibold mt-2">Ganti Sandi</p>
        </a>
         <a href="program.php" class="bg-white p-4 rounded-2xl shadow-md hover:shadow-lg transition-shadow">
            <i class="bi bi-heart-fill text-3xl text-primary-orange"></i>
            <p class="text-sm font-semibold mt-2">Donasi Lagi</p>
        </a>
    </div>

    <!-- Donation History -->
    <div class="bg-white p-6 rounded-2xl shadow-md">
        <h3 class="text-xl font-bold mb-4">Riwayat Donasi</h3>
        <div class="space-y-4">
            <?php if (!empty($donasi_list)): ?>
                <?php foreach($donasi_list as $donasi): 
                    $status = $donasi['status'];
                    $status_color = 'bg-gray-400'; // default
                    if ($status == 'Selesai') $status_color = 'bg-green-500';
                    elseif (str_contains($status, 'Menunggu')) $status_color = 'bg-yellow-500';
                    elseif ($status == 'Dibatalkan') $status_color = 'bg-red-500';
                ?>
                <div class="flex items-center justify-between p-4 rounded-lg border">
                    <div>
                        <p class="font-semibold"><?php echo htmlspecialchars($donasi['nama_program'] ?: 'Donasi Umum'); ?></p>
                        <p class="text-sm text-text-muted"><?php echo date('d F Y, H:i', strtotime($donasi['created_at'])); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold">Rp <?php echo number_format($donasi['nominal'], 0, ',', '.'); ?></p>
                         <span class="px-2 py-1 text-xs text-white font-semibold rounded-full <?php echo $status_color; ?>">
                            <?php echo htmlspecialchars($status); ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-gray-500 py-8">
                    <i class="bi bi-folder2-open text-4xl"></i>
                    <p class="mt-2">Anda belum memiliki riwayat donasi.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer_user.php';
?>

