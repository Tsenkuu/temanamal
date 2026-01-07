<?php
// Menetapkan judul halaman
$page_title = "Semua Berita";

// Memuat file konfigurasi dan template header
require_once 'includes/config.php';
require_once 'includes/templates/header.php';

// Mengambil HANYA berita dan opini yang sudah 'published' dari database
$result_berita = $mysqli->query("SELECT id, judul, slug, teras_berita, gambar, created_at, type FROM berita WHERE status = 'published' ORDER BY created_at DESC");
?>

<!-- Judul Halaman -->
<section class="bg-white py-12">
    <div class="container mx-auto px-6 text-center scroll-animate">
        <h1 class="text-4xl font-bold text-dark-text">Berita & Artikel Lazismu</h1>
        <p class="text-gray-600 mt-2">Ikuti kabar terbaru dari setiap langkah kebaikan yang kita ciptakan bersama.</p>
    </div>
</section>

<!-- Daftar Berita -->
<section class="py-16 px-6 md:px-12 bg-light-bg">
    <div class="container mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 lg:gap-8">
            <?php if ($result_berita && $result_berita->num_rows > 0): ?>
            <?php while($berita = $result_berita->fetch_assoc()): ?>
            <div
                class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 ease-in-out scroll-animate">
                <a href="berita/<?php echo $berita['id']; ?>">
                    <img src="<?php echo BASE_URL . '/assets/uploads/berita/' . htmlspecialchars($berita['gambar']); ?>"
                        alt="<?php echo htmlspecialchars($berita['judul']); ?>" class="w-full h-56 object-cover">
                </a>
                <div class="p-4 md:p-6 flex flex-col flex-grow">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-gray-500 text-sm">
                            <span
                                class="font-semibold text-primary-orange"><?php echo ucfirst($berita['type']); ?></span>
                            &bull;
                            <span><?php echo date('d F Y', strtotime($berita['created_at'])); ?></span>
                        </p>
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $berita['type'] == 'berita' ? 'text-blue-800 bg-blue-100' : 'text-purple-800 bg-purple-100'; ?>">
                            <?php echo ucfirst($berita['type']); ?>
                        </span>
                    </div>
                    <h4 class="text-xl font-semibold text-dark-text mb-3">
                        <?php echo htmlspecialchars($berita['judul']); ?>
                    </h4>
                    <p class="text-gray-600 text-sm mb-4 flex-grow">
                        <?php
                        // Menampilkan teras berita sebagai ringkasan (maksimal 10 kata)
                        $words = explode(' ', strip_tags($berita['teras_berita']));
                        $excerpt = implode(' ', array_slice($words, 0, 10));
                        if (count($words) > 10) {
                            $excerpt .= '... Selengkapnya';
                        }
                        echo htmlspecialchars($excerpt);
                        ?>
                    </p>
                    <a href="/berita/<?php echo $berita['slug']; ?>"
                        class="text-primary-orange hover:underline font-semibold group mt-auto">
                        Baca Selengkapnya <span class="group-hover:ml-1 transition-all">&rarr;</span>
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <p class="text-center text-gray-500 col-span-3">Belum ada berita yang dipublikasikan.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
// Memuat footer
require_once 'includes/templates/footer.php';
?>