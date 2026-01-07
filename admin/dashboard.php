<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Dashboard Admin";
$admin_nama = $_SESSION['admin_nama_lengkap'] ?? 'Admin';

// --- Query Komprehensif untuk Dasbor ---

// 1. Statistik Utama
$total_donasi_online = $mysqli->query("SELECT SUM(nominal) as total FROM donasi WHERE status = 'Selesai'")->fetch_assoc()['total'] ?? 0;
$total_donasi_laporan = $mysqli->query("SELECT SUM(nominal) as total FROM laporan_transaksi")->fetch_assoc()['total'] ?? 0;
$total_donasi_kotak = $mysqli->query("SELECT SUM(jumlah_terkumpul) as total FROM riwayat_pengambilan")->fetch_assoc()['total'] ?? 0;
$total_donasi_keseluruhan = $total_donasi_online + $total_donasi_laporan + $total_donasi_kotak;
$total_program_aktif = $mysqli->query("SELECT COUNT(id) as total FROM program")->fetch_assoc()['total'] ?? 0;
$donasi_perlu_konfirmasi = $mysqli->query("SELECT COUNT(id) as total FROM donasi WHERE status = 'Menunggu Konfirmasi'")->fetch_assoc()['total'] ?? 0;
$berita_perlu_persetujuan = $mysqli->query("SELECT COUNT(id) as total FROM berita WHERE status = 'pending'")->fetch_assoc()['total'] ?? 0;

// 2. Data untuk Grafik Donasi 7 Hari Terakhir (Online)
$chart_labels = [];
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('d M', strtotime($date));
    $stmt_chart = $mysqli->prepare("SELECT SUM(nominal) as total FROM donasi WHERE status = 'Selesai' AND DATE(created_at) = ?");
    $stmt_chart->bind_param("s", $date);
    $stmt_chart->execute();
    $daily_total = $stmt_chart->get_result()->fetch_assoc()['total'] ?? 0;
    $chart_data[] = $daily_total;
    $stmt_chart->close();
}

// 3. Tugas Pengambilan Kotak Infak Hari Ini
$today = date('Y-m-d');
$result_tugas_hari_ini = $mysqli->query("SELECT t.id, a.nama_lengkap, k.nama_lokasi, k.alamat FROM tugas_pengambilan t JOIN amil a ON t.id_amil = a.id JOIN kotak_infak k ON t.id_kotak_infak = k.id WHERE t.tanggal_tugas = '$today' AND t.status = 'Ditugaskan' ORDER BY a.nama_lengkap");

// 4. Donasi Terbaru yang Perlu Dikonfirmasi
$result_konfirmasi_terbaru = $mysqli->query("SELECT id, nama_donatur, nominal, created_at FROM donasi WHERE status = 'Menunggu Konfirmasi' ORDER BY created_at DESC LIMIT 5");


require_once 'templates/header_admin.php';
?>

<main class="main-content">
    <!-- Header Halaman -->
    <div class="header-welcome">
        <h1 class="text-2xl md:text-3xl font-bold text-dark-text">Selamat Datang,
            <?php echo htmlspecialchars($admin_nama); ?>!</h1>
        <p class="text-gray-500 mt-1">Berikut adalah ringkasan aktivitas di Lazismu Tulungagung hari ini.</p>
    </div>

    <!-- Kartu Statistik Utama -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
        <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600">
            <div class="stat-icon bg-blue-100 text-blue-600">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <p class="stat-label">Total Donasi Terkumpul</p>
                <p class="stat-value">Rp <?php echo number_format($total_donasi_keseluruhan, 0, ',', '.'); ?></p>
            </div>
        </div>
        <div class="stat-card bg-gradient-to-br from-green-500 to-green-600">
            <div class="stat-icon bg-green-100 text-green-600">
                <i class="bi bi-patch-check-fill"></i>
            </div>
            <div>
                <p class="stat-label">Perlu Dikonfirmasi</p>
                <p class="stat-value"><?php echo number_format($donasi_perlu_konfirmasi); ?> Donasi</p>
            </div>
        </div>
        <div class="stat-card bg-gradient-to-br from-yellow-500 to-yellow-600">
            <div class="stat-icon bg-yellow-100 text-yellow-600">
                <i class="bi bi-heart-pulse-fill"></i>
            </div>
            <div>
                <p class="stat-label">Program Aktif</p>
                <p class="stat-value"><?php echo number_format($total_program_aktif); ?> Program</p>
            </div>
        </div>
        <div class="stat-card bg-gradient-to-br from-indigo-500 to-indigo-600">
            <div class="stat-icon bg-indigo-100 text-indigo-600">
                <i class="bi bi-newspaper"></i>
            </div>
            <div>
                <p class="stat-label">Berita Perlu Disetujui</p>
                <p class="stat-value"><?php echo number_format($berita_perlu_persetujuan); ?> Berita</p>
            </div>
        </div>
    </div>

    <!-- Aksi Cepat & Grafik -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <!-- Kolom Aksi Cepat -->
        <div class="lg:col-span-1 space-y-6">
            <div class="content-card">
                <h3 class="card-title">Aksi Cepat</h3>
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <a href="tambah_berita.php" class="quick-action-btn bg-blue-50 hover:bg-blue-100 text-blue-600">
                        <i class="bi bi-pencil-square text-2xl"></i><span>Tulis Berita</span>
                    </a>
                    <a href="tambah_program.php" class="quick-action-btn bg-green-50 hover:bg-green-100 text-green-600">
                        <i class="bi bi-plus-circle text-2xl"></i><span>Buat Program</span>
                    </a>
                    <a href="konfirmasi_donasi.php"
                        class="quick-action-btn bg-yellow-50 hover:bg-yellow-100 text-yellow-600">
                        <i class="bi bi-check2-square text-2xl"></i><span>Konfirmasi</span>
                    </a>
                    <a href="kelola_tugas.php"
                        class="quick-action-btn bg-indigo-50 hover:bg-indigo-100 text-indigo-600">
                        <i class="bi bi-card-checklist text-2xl"></i><span>Tugas Amil</span>
                    </a>
                </div>
            </div>
            <div class="content-card">
                <h3 class="card-title">Donasi Menunggu Konfirmasi</h3>
                <div class="divide-y divide-gray-200 mt-4">
                    <?php if ($result_konfirmasi_terbaru && $result_konfirmasi_terbaru->num_rows > 0): ?>
                    <?php while($donasi = $result_konfirmasi_terbaru->fetch_assoc()): ?>
                    <div class="py-3">
                        <div class="flex justify-between items-center">
                            <p class="font-semibold text-dark-text">
                                <?php echo htmlspecialchars($donasi['nama_donatur']); ?></p>
                            <p class="font-bold text-green-600">Rp
                                <?php echo number_format($donasi['nominal'], 0, ',', '.'); ?></p>
                        </div>
                        <small
                            class="text-gray-500"><?php echo date('d M Y, H:i', strtotime($donasi['created_at'])); ?></small>
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <p class="text-gray-500 text-center py-4">Tidak ada donasi yang perlu dikonfirmasi saat ini.</p>
                    <?php endif; ?>
                </div>
                <a href="konfirmasi_donasi.php"
                    class="block text-center mt-4 text-primary-orange font-semibold hover:underline">Lihat Semua</a>
            </div>
        </div>

        <!-- Kolom Grafik -->
        <div class="lg:col-span-2 content-card">
            <h3 class="card-title">Grafik Donasi Online (7 Hari Terakhir)</h3>
            <div class="mt-4 h-80">
                <canvas id="donationChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tugas Hari Ini -->
    <div class="content-card mt-6">
        <h3 class="card-title mb-4">Tugas Pengambilan Kotak Infak Hari Ini (<?php echo date('d M Y'); ?>)</h3>
        <div class="table-wrapper">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Amil Bertugas</th>
                        <th scope="col" class="px-6 py-3">Lokasi Kotak</th>
                        <th scope="col" class="px-6 py-3">Alamat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_tugas_hari_ini && $result_tugas_hari_ini->num_rows > 0): ?>
                    <?php while($tugas = $result_tugas_hari_ini->fetch_assoc()): ?>
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-semibold text-dark-text">
                            <?php echo htmlspecialchars($tugas['nama_lengkap']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($tugas['nama_lokasi']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($tugas['alamat']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr class="bg-white border-b">
                        <td colspan="3" class="px-6 py-4 text-center">Tidak ada tugas yang dijadwalkan untuk hari ini.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('donationChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Donasi Masuk (Rp)',
                data: <?php echo json_encode($chart_data); ?>,
                backgroundColor: 'rgba(251, 130, 1, 0.1)',
                borderColor: '#fb8201',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php 
require_once 'templates/footer_admin.php'; 
?>