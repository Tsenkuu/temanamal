<?php
// 1. Memuat file konfigurasi & memulai session
require_once 'includes/config.php';

// 2. Validasi & Ambil SLUG Berita dari URL
$slug_berita = isset($_GET['slug']) ? trim(htmlspecialchars($_GET['slug'])) : '';
if (empty($slug_berita)) {
    header("Location: " . BASE_URL . "/berita");
    exit();
}

// 3. Ambil Data Utama Berita berdasarkan SLUG (Menggunakan Prepared Statement)
$stmt = $mysqli->prepare("SELECT *, CASE WHEN type = 'berita' THEN 'Berita' WHEN type = 'opini' THEN 'Opini' ELSE 'Artikel' END as type_label FROM berita WHERE slug = ? AND status = 'published'");
$stmt->bind_param("s", $slug_berita); // 's' untuk string (slug)
$stmt->execute();
$result = $stmt->get_result();
$berita = $result->fetch_assoc();
$stmt->close();

// 4. Handle Jika Berita Tidak Ditemukan
if (!$berita) {
    // Redirect ke halaman error 404 jika berita tidak ada
    header("Location: " . BASE_URL . "/error.php?code=404");
    exit();
}

// 5. Lacak Pengunjung (menggunakan ID yang didapat dari hasil query)
track_visitor($mysqli, 'berita', $berita['id']);

// 6. Ambil Data Pendukung (sertakan slug untuk program agar tautan benar)
// Program Unggulan (1 terbaru)
$result_unggulan = $mysqli->query("SELECT id, nama_program, deskripsi, gambar, slug FROM program ORDER BY created_at DESC LIMIT 1");
$program_unggulan = $result_unggulan->fetch_assoc();

// Program Lainnya (3 acak, selain unggulan)
$id_unggulan = $program_unggulan ? $program_unggulan['id'] : 0;
$stmt_lainnya = $mysqli->prepare("SELECT id, nama_program, gambar, slug FROM program WHERE id != ? ORDER BY RAND() LIMIT 3");
$stmt_lainnya->bind_param("i", $id_unggulan);
$stmt_lainnya->execute();
$result_lainnya = $stmt_lainnya->get_result();

// Komentar yang sudah disetujui
$stmt_komentar = $mysqli->prepare("SELECT nama_pengirim, isi_komentar, created_at FROM komentar WHERE id_berita = ? AND status = 'approved' ORDER BY created_at DESC");
$stmt_komentar->bind_param("i", $berita['id']);
$stmt_komentar->execute();
$result_komentar = $stmt_komentar->get_result();
$jumlah_komentar = $result_komentar->num_rows;

// 7. Siapkan Variabel untuk SEO (Meta Tags & Structured Data)
$page_title = htmlspecialchars($berita['judul']);
$og_title = htmlspecialchars($berita['judul']);
$og_description = htmlspecialchars(strip_tags($berita['teras_berita']));
$og_image = BASE_URL . '/assets/uploads/berita/' . htmlspecialchars($berita['gambar']);
$og_url = BASE_URL . '/berita/' . $berita['slug']; // URL canonical menggunakan slug
$og_site_name = "Teman Amal Lazismu";

// Menyiapkan Structured Data JSON-LD untuk SEO
$structured_data = [
    "@context" => "https://schema.org",
    "@type" => "NewsArticle",
    "mainEntityOfPage" => ["@type" => "WebPage", "@id" => $og_url],
    "headline" => $berita['judul'],
    "image" => ["@type" => "ImageObject", "url" => $og_image],
    "datePublished" => date('c', strtotime($berita['created_at'])),
    "dateModified" => date('c', strtotime($berita['updated_at'] ?? $berita['created_at'])),
    "author" => ["@type" => "Person", "name" => htmlspecialchars($berita['penulis'])],
    "publisher" => ["@type" => "Organization", "name" => $og_site_name, "logo" => ["@type" => "ImageObject", "url" => BASE_URL . "/assets/images/logo.png"]],
    "description" => $og_description
];

// 8. Memuat Header
require_once 'includes/templates/header.php';
?>
<!-- Link CSS dan style lainnya -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
.prose {
    text-align: justify;
    hyphens: auto;
}

.prose img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin-top: 1rem;
    margin-bottom: 1rem;
}

.prose p,
.prose ul,
.prose ol {
    margin-bottom: 1.25rem;
}
</style>

<main class="container mx-auto my-8 px-4 md:my-12 md:px-6">
    <div class="max-w-4xl mx-auto">
        <article class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <figure class="relative">
                <img src="<?php echo BASE_URL; ?>/assets/uploads/berita/<?php echo htmlspecialchars($berita['gambar']); ?>"
                    alt="<?php echo htmlspecialchars($berita['judul']); ?>" class="w-full h-auto md:h-96 object-cover">
                <?php if (!empty($berita['sumber_gambar'])): ?>
                <figcaption class="absolute bottom-0 right-0 bg-black bg-opacity-50 text-white text-xs px-2 py-1">
                    Sumber: <?php echo htmlspecialchars($berita['sumber_gambar']); ?>
                </figcaption>
                <?php endif; ?>
            </figure>

            <div class="p-6 md:p-10">

                <h1 class="text-3xl md:text-4xl font-bold text-dark-text mb-4 leading-tight">
                    <?php echo htmlspecialchars($berita['judul']); ?></h1>
                <div class="flex items-center justify-between text-gray-500 text-sm mb-6">
                    <div class="flex items-center">
                        <span>Oleh: <span
                                class="font-semibold"><?php echo htmlspecialchars($berita['penulis']); ?></span></span>
                        <span class="mx-2">&bull;</span>
                        <span><?php echo date('d F Y', strtotime($berita['created_at'])); ?></span>
                    </div>
                    <span
                        class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $berita['type'] == 'berita' ? 'text-blue-800 bg-blue-100' : 'text-purple-800 bg-purple-100'; ?>">
                        <?php echo htmlspecialchars($berita['type_label']); ?>
                    </span>
                </div>
                <p
                    class="text-lg text-gray-600 font-semibold leading-relaxed mb-6 border-l-4 border-primary-orange pl-4">
                    <?php echo nl2br(htmlspecialchars($berita['teras_berita'])); ?>
                </p>

                <div class="prose max-w-none text-gray-700 leading-relaxed break-words">
                    <?php echo nl2br($berita['tubuh_berita']); ?>
                </div>

                <?php if (!empty($berita['tags'])): ?>
                <div class="mt-8 pt-6 border-t">
                    <h4 class="font-semibold mb-2">Tags:</h4>
                    <div class="flex flex-wrap gap-2">
                        <?php
                        $tags = explode(',', $berita['tags']);
                        foreach ($tags as $tag):
                        ?>
                        <span class="bg-gray-200 text-gray-700 text-sm font-medium px-3 py-1 rounded-full">
                            <?php echo htmlspecialchars(trim($tag)); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="mt-8 pt-6 border-t">
                    <h4 class="font-semibold mb-3">Bagikan Berita Ini:</h4>
                    <div class="flex items-center gap-4">
                        <?php $share_url = urlencode($og_url); ?>
                        <?php $share_title = urlencode($berita['judul']); ?>
                        <a href="https://api.whatsapp.com/send?text=<?php echo $share_title . '%0A' . $share_url; ?>"
                            target="_blank" class="text-green-500 hover:text-green-600 transition-colors"
                            title="Bagikan ke WhatsApp"><i class="bi bi-whatsapp" style="font-size: 2rem;"></i></a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" target="_blank"
                            class="text-blue-600 hover:text-blue-700 transition-colors" title="Bagikan ke Facebook"><i
                                class="bi bi-facebook" style="font-size: 2rem;"></i></a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>"
                            target="_blank" class="text-sky-500 hover:text-sky-600 transition-colors"
                            title="Bagikan ke Twitter/X"><i class="bi bi-twitter-x" style="font-size: 2rem;"></i></a>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t">
                    <a href="<?php echo BASE_URL; ?>/berita"
                        class="text-primary-orange hover:underline font-semibold group">
                        <span class="group-hover:-ml-1 transition-all">&larr;</span> Kembali ke Semua Berita
                    </a>
                </div>
            </div>
        </article>

        <section id="komentar" class="mt-12 bg-white rounded-2xl shadow-lg p-6 md:p-8">
            <h3 class="text-2xl font-bold text-dark-text mb-6 pb-4 border-b">Komentar (<?php echo $jumlah_komentar; ?>)
            </h3>

            <div class="space-y-6">
                <?php if ($jumlah_komentar > 0): ?>
                <?php while($komentar = $result_komentar->fetch_assoc()): ?>
                <div class="flex space-x-4 border-b pb-4">
                    <div class="flex-shrink-0 bg-gray-200 rounded-full h-12 w-12 flex items-center justify-center">
                        <span
                            class="text-xl font-bold text-gray-600"><?php echo strtoupper(substr($komentar['nama_pengirim'], 0, 1)); ?></span>
                    </div>
                    <div>
                        <p class="font-semibold text-dark-text">
                            <?php echo htmlspecialchars($komentar['nama_pengirim']); ?></p>
                        <p class="text-xs text-gray-500 mb-2">
                            <?php echo date('d F Y, H:i', strtotime($komentar['created_at'])); ?></p>
                        <p class="text-gray-700 leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($komentar['isi_komentar'])); ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <p class="text-center text-gray-500">Belum ada komentar. Jadilah yang pertama berkomentar!</p>
                <?php endif; ?>
            </div>

            <div class="mt-10 pt-8 border-t">
                <h4 class="text-xl font-semibold mb-4">Tinggalkan Komentar</h4>
                <?php
                if (isset($_SESSION['comment_message'])) {
                    $alert_class = $_SESSION['comment_status'] === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700';
                    echo '<div class="border-l-4 p-4 rounded-lg mb-4 ' . $alert_class . '" role="alert"><p>' . htmlspecialchars($_SESSION['comment_message']) . '</p></div>';
                    unset($_SESSION['comment_message'], $_SESSION['comment_status']);
                }
                ?>
                <form action="<?php echo BASE_URL; ?>/proses_komentar.php" method="POST">
                    <input type="hidden" name="id_berita" value="<?php echo $berita['id']; ?>">
                    <div class="mb-4">
                        <label for="nama_pengirim" class="block text-gray-700 text-sm font-bold mb-2">Nama Anda</label>
                        <input type="text" id="nama_pengirim" name="nama_pengirim"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="isi_komentar" class="block text-gray-700 text-sm font-bold mb-2">Komentar
                            Anda</label>
                        <textarea id="isi_komentar" name="isi_komentar" rows="4"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            required></textarea>
                    </div>
                    <button type="submit"
                        class="bg-primary-orange hover:bg-orange-600 text-white font-bold py-2 px-4 rounded-full transition duration-300">
                        Kirim Komentar
                    </button>
                </form>
            </div>
        </section>

        <?php if ($program_unggulan): ?>
        <section
            class="mt-12 bg-gradient-to-r from-primary-orange to-secondary-orange text-white p-8 rounded-2xl shadow-lg flex flex-col md:flex-row items-center gap-8">
            <div class="md:w-1/3 flex-shrink-0">
                <img src="<?php echo BASE_URL . '/assets/uploads/program/' . htmlspecialchars($program_unggulan['gambar']); ?>"
                    alt="<?php echo htmlspecialchars($program_unggulan['nama_program']); ?>"
                    class="w-full h-auto rounded-xl object-cover shadow-md">
            </div>
            <div class="text-center md:text-left">
                <h3 class="text-2xl font-bold mb-2">Program Unggulan</h3>
                <h4 class="text-3xl font-extrabold mb-4">
                    <?php echo htmlspecialchars($program_unggulan['nama_program']); ?></h4>
                <p class="mb-6 opacity-90">
                    <?php echo htmlspecialchars(substr(strip_tags($program_unggulan['deskripsi']), 0, 150)) . '...'; ?>
                </p>
                <a href="<?php echo BASE_URL; ?>/program/<?php echo $program_unggulan['slug']; ?>"
                    class="bg-white text-primary-orange px-8 py-3 rounded-full font-bold text-lg hover:bg-gray-100 transition duration-300 ease-in-out shadow-xl transform hover:scale-105">
                    Donasi Sekarang
                </a>
            </div>
        </section>
        <?php endif; ?>

        <section class="mt-12">
            <h3 class="text-2xl font-bold text-dark-text mb-6 text-center">Lihat Program Kebaikan Lainnya</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php if ($result_lainnya && $result_lainnya->num_rows > 0): ?>
                <?php while($program = $result_lainnya->fetch_assoc()): ?>
                <div
                    class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col hover:shadow-xl transition-shadow">
                    <a href="<?php echo BASE_URL; ?>/program/<?php echo $program['slug']; ?>">
                        <img src="<?php echo BASE_URL . '/assets/uploads/program/' . htmlspecialchars($program['gambar']); ?>"
                            alt="<?php echo htmlspecialchars($program['nama_program']); ?>"
                            class="w-full h-48 object-cover">
                    </a>
                    <div class="p-4 flex flex-col flex-grow">
                        <h5 class="text-lg font-semibold text-dark-text mb-3 flex-grow">
                            <?php echo htmlspecialchars($program['nama_program']); ?></h5>
                        <a href="<?php echo BASE_URL; ?>/program/<?php echo $program['slug']; ?>"
                            class="w-full bg-primary-orange text-white text-center px-4 py-2 rounded-lg font-bold hover:bg-orange-600 transition mt-auto">
                            Lihat Detail
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<?php
// Membersihkan hasil statement
$stmt_lainnya->close();
$stmt_komentar->close();
require_once 'includes/templates/footer.php';
?>