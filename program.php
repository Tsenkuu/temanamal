<?php
// Menetapkan judul halaman
$page_title = "Semua Program";

// Memuat file konfigurasi dan template header baru
require_once 'includes/config.php';
require_once 'includes/templates/header.php';

// Mengambil SEMUA data program dari database
$result_program = $mysqli->query("SELECT id, nama_program, deskripsi, gambar, target_donasi, donasi_terkumpul FROM program ORDER BY created_at DESC");
?>

<!-- Judul Halaman -->
<section class="bg-white py-12">
    <div class="container mx-auto px-6 text-center scroll-animate">
        <h1 class="text-4xl font-bold text-dark-text">Program Kebaikan Lazismu</h1>
        <p class="text-gray-600 mt-2">Pilih program yang ingin Anda bantu. Setiap donasi Anda adalah harapan bagi
            mereka.</p>
    </div>
</section>

<!-- Daftar Program -->
<section class="py-16 px-6 md:px-12 bg-light-bg">
    <div class="container mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if ($result_program && $result_program->num_rows > 0): ?>
            <?php while($program = $result_program->fetch_assoc()): 
                        // Hitung persentase donasi
                        $persentase = $program['target_donasi'] > 0 ? min(100, ($program['donasi_terkumpul'] / $program['target_donasi']) * 100) : 0;
                    ?>
            <div
                class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 ease-in-out scroll-animate flex flex-col">
                <a href="program/<?php echo $program['id']; ?>">
                    <img src="<?php echo BASE_URL . '/assets/uploads/program/' . htmlspecialchars($program['gambar']); ?>"
                        alt="<?php echo htmlspecialchars($program['nama_program']); ?>"
                        class="w-full h-56 object-cover">
                </a>
                <div class="p-6 flex flex-col flex-grow">
                    <h4 class="text-xl font-semibold text-dark-text mb-3">
                        <?php echo htmlspecialchars($program['nama_program']); ?></h4>
                    <p class="text-gray-600 text-sm mb-4 flex-grow">
                        <?php echo htmlspecialchars(substr($program['deskripsi'], 0, 120)); ?>...</p>

                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                        <div class="bg-primary-orange h-2.5 rounded-full" style="width: <?php echo $persentase; ?>%">
                        </div>
                    </div>

                    <div class="flex justify-between text-sm text-gray-600 mb-4">
                        <div>
                            <span class="font-semibold">Terkumpul</span>
                            <p class="font-bold text-dark-text">Rp
                                <?php echo number_format($program['donasi_terkumpul'], 0, ',', '.'); ?></p>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold">Target</span>
                            <p class="font-bold text-dark-text">Rp
                                <?php echo number_format($program['target_donasi'], 0, ',', '.'); ?></p>
                        </div>
                    </div>

                    <a href="program/<?php echo $program['id']; ?>"
                        class="block w-full text-center bg-primary-orange text-white px-6 py-3 rounded-full font-bold hover:bg-orange-600 transition duration-300 shadow-md mt-auto">
                        Donasi Sekarang
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <p class="text-center text-gray-500 col-span-3">Belum ada program yang tersedia saat ini.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
// Memuat footer baru
require_once 'includes/templates/footer.php';
?>