<?php
// Memuat file konfigurasi untuk koneksi database dan BASE_URL
require_once 'includes/config.php';

// Fungsi untuk membuat slug yang aman untuk URL
function create_slug($string) {
    $string = strtolower(trim($string));
    // Hapus karakter non-alfanumerik kecuali spasi dan strip
    $string = preg_replace('/[^a-z0-9 -]/', '', $string);
    // Ganti spasi dengan strip
    $string = str_replace(' ', '-', $string);
    // Hapus strip ganda
    return preg_replace('/-+/', '-', $string);
}

// Mengatur header sebagai XML
header("Content-Type: application/xml; charset=utf-8");

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// 1. URL Statis (Halaman utama, tentang kami, dll.)
$static_pages = [
    '/',
    '/program',
    '/berita',
    '/laporan',
    '/tentang-kami',
    '/personalia',
    '/kalkulator-zakat',
    '/donasi',
    '/login'
];

foreach ($static_pages as $page) {
    echo "  <url>\n";
    echo "    <loc>" . BASE_URL . $page . "</loc>\n";
    echo "    <priority>0.8</priority>\n"; // Prioritas lebih tinggi untuk halaman utama
    echo "  </url>\n";
}

// 2. URL Dinamis dari Database (Berita)
$result_berita = $mysqli->query("SELECT slug, updated_at, created_at FROM berita WHERE status = 'published' ORDER BY created_at DESC");
if ($result_berita) {
    while ($berita = $result_berita->fetch_assoc()) {
        $last_modified = !empty($berita['updated_at']) ? $berita['updated_at'] : $berita['created_at'];
        echo "  <url>\n";
        echo "    <loc>" . BASE_URL . "/berita/" . htmlspecialchars($berita['slug']) . "</loc>\n";
        echo "    <lastmod>" . date('c', strtotime($last_modified)) . "</lastmod>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.9</priority>\n"; // Prioritas tinggi untuk konten dinamis
        echo "  </url>\n";
    }
    $result_berita->close();
}

// 3. URL Dinamis dari Database (Program)
$result_program = $mysqli->query("SELECT slug, created_at FROM program ORDER BY created_at DESC");
if ($result_program) {
    while ($program = $result_program->fetch_assoc()) {
        echo "  <url>\n";
        echo "    <loc>" . BASE_URL . "/program/" . htmlspecialchars($program['slug']) . "</loc>\n";
        echo "    <lastmod>" . date('c', strtotime($program['created_at'])) . "</lastmod>\n";
        echo "    <changefreq>monthly</changefreq>\n";
        echo "    <priority>0.9</priority>\n";
        echo "  </url>\n";
    }
    $result_program->close();
}

echo '</urlset>';

$mysqli->close();
?>
