<?php
// 1. Memuat file konfigurasi
require_once 'includes/config.php';

// 2. Validasi & Ambil ID Program dari URL
$id_program = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_program === 0) {
    // Redirect ke halaman daftar program jika ID tidak valid
    header("Location: " . BASE_URL . "/program");
    exit();
}

// 3. Ambil Data Program (Menggunakan Prepared Statement untuk Keamanan)
$stmt = $mysqli->prepare("SELECT * FROM program WHERE id = ?");
$stmt->bind_param("i", $id_program);
$stmt->execute();
$result = $stmt->get_result();
$program = $result->fetch_assoc();
$stmt->close();

// 4. Handle Jika Program Tidak Ditemukan
if (!$program) {
    // Redirect ke halaman error 404 jika program tidak ada
    header("Location: " . BASE_URL . "/error.php?code=404");
    exit();
}

// 5. Lacak Pengunjung
track_visitor($mysqli, 'program', $id_program);
// 6. Siapkan Data Pendukung
// Hitung persentase donasi
$persentase = $program['target_donasi'] > 0 ? min(100, ($program['donasi_terkumpul'] / $program['target_donasi']) * 100) : 0;
$is_zakat_program = (isset($program['kategori']) && $program['kategori'] === 'Zakat');
$metode_pembayaran = ['Zakat' => [], 'Infak' => [], 'Qurban' => [], 'Umum' => []];

// [PERUBAIKAN] Logika pengambilan metode pembayaran
$metode_query_sql = "";
if (!empty($program['metode_pembayaran_ids'])) {
    // Jika ada metode yang dipilih, ambil hanya metode tersebut
    $metode_ids = explode(',', $program['metode_pembayaran_ids']);
    $placeholders = implode(',', array_fill(0, count($metode_ids), '?'));
    $metode_query_sql = "SELECT id, nama_metode, tipe, kategori FROM metode_pembayaran WHERE status = 'Aktif' AND id IN ($placeholders)";
    $stmt_metode = $mysqli->prepare($metode_query_sql);
    // Dynamically bind parameters
    $types = str_repeat('i', count($metode_ids));
    $stmt_metode->bind_param($types, ...$metode_ids);
    $stmt_metode->execute();
    $result_metode = $stmt_metode->get_result();
} else {
    // Jika tidak ada yang dipilih, ambil semua metode yang relevan (fallback)
    $metode_query_sql = "SELECT id, nama_metode, tipe, kategori FROM metode_pembayaran WHERE status = 'Aktif'";
    $result_metode = $mysqli->query($metode_query_sql);
}

// Kelompokkan metode pembayaran yang didapat
if ($result_metode) {
    while($metode = $result_metode->fetch_assoc()) {
        $metode_pembayaran[$metode['kategori']][] = $metode;
    }
}

// 7. Siapkan Variabel untuk SEO (Meta Tags & Structured Data)
$page_title = htmlspecialchars($program['nama_program']);
$og_title = htmlspecialchars($program['nama_program']);
$og_description = htmlspecialchars(substr(strip_tags($program['deskripsi']), 0, 155)); // Ambil 155 karakter pertama
$og_image = BASE_URL . '/assets/uploads/program/' . htmlspecialchars($program['gambar']);
$og_url = BASE_URL . '/program/' . $id_program; // URL canonical menggunakan id
$og_site_name = "Teman Amal Lazismu";

// [BARU] Menyiapkan Structured Data JSON-LD untuk SEO
$structured_data = [
    "@context" => "https://schema.org",
    "@type" => "DonateAction",
    "name" => $program['nama_program'],
    "description" => $og_description,
    "image" => $og_image,
    "url" => $og_url,
    "recipient" => [
        "@type" => "Organization",
        "name" => $og_site_name,
        "url" => BASE_URL,
        "logo" => BASE_URL . "/assets/images/logo.png"
    ]
];

// 8. Memuat Header (setelah semua variabel SEO siap)
require_once 'includes/templates/header.php';
?>
<!-- Menambahkan link CSS untuk Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<main class="container mx-auto my-12 px-6">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

        <!-- Kolom Kiri: Detail Program -->
        <div class="lg:col-span-7">
            <article class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <figure class="w-full aspect-w-16 aspect-h-9">
                    <img src="<?php echo BASE_URL; ?>/assets/uploads/program/<?php echo htmlspecialchars($program['gambar']); ?>"
                         alt="<?php echo htmlspecialchars($program['nama_program']); ?>"
                         class="w-full h-full object-cover">
                </figure>
                <div class="p-6 md:p-8">
                    <h1 class="text-3xl md:text-4xl font-bold text-dark-text mb-4 leading-tight">
                        <?php echo htmlspecialchars($program['nama_program']); ?></h1>
                    
                    <!-- [PERBAIKAN] Logika "Lihat Selengkapnya" disederhanakan di PHP dan dikelola oleh JS -->
                    <div id="deskripsi-konten" class="prose max-w-none text-gray-700 leading-relaxed break-words">
                        <?php
                            // Tampilkan deskripsi pendek jika terlalu panjang, sisanya dihandle JS
                            $deskripsi_plain = strip_tags($program['deskripsi']);
                            if (str_word_count($deskripsi_plain) > 70) {
                                echo substr($deskripsi_plain, 0, 400) . '...';
                            } else {
                                echo nl2br($program['deskripsi']);
                            }
                        ?>
                    </div>
                    <button id="lihat-selengkapnya" class="hidden text-primary-orange font-semibold hover:underline mt-2">Lihat Selengkapnya</button>
                    
                    <!-- [BARU] Tombol Berbagi Sosial -->
                    <div class="mt-8 pt-6 border-t">
                         <h4 class="font-semibold mb-3">Bagikan Program Ini:</h4>
                         <div class="flex items-center gap-4">
                            <?php $share_url = urlencode($og_url); ?>
                            <?php $share_title = urlencode($program['nama_program']); ?>
                            <a href="https://api.whatsapp.com/send?text=<?php echo $share_title . '%0A' . $share_url; ?>" target="_blank" class="text-green-500 hover:text-green-600 transition-colors" title="Bagikan ke WhatsApp"><i class="bi bi-whatsapp" style="font-size: 2rem;"></i></a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" target="_blank" class="text-blue-600 hover:text-blue-700 transition-colors" title="Bagikan ke Facebook"><i class="bi bi-facebook" style="font-size: 2rem;"></i></a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" class="text-sky-500 hover:text-sky-600 transition-colors" title="Bagikan ke Twitter/X"><i class="bi bi-twitter-x" style="font-size: 2rem;"></i></a>
                         </div>
                    </div>
                </div>
            </article>
        </div>

        <!-- Kolom Kanan: Form Donasi -->
        <div class="lg:col-span-5">
            <div class="sticky top-28">
                <div class="bg-white p-6 md:p-8 rounded-2xl shadow-lg">
                    <h3 class="text-2xl font-bold text-dark-text mb-4">Donasi untuk Program Ini</h3>

                    <!-- Progress Bar -->
                    <div class="mb-6">
                        <div class="flex justify-between text-sm text-gray-600 mb-2">
                            <span>Terkumpul: <span class="font-bold text-dark-text">Rp
                                    <?php echo number_format($program['donasi_terkumpul'], 0, ',', '.'); ?></span></span>
                            <span>Target: Rp <?php echo number_format($program['target_donasi'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-primary-orange h-2.5 rounded-full"
                                 style="width: <?php echo $persentase; ?>%"></div>
                        </div>
                    </div>

                    <!-- Form Donasi -->
                    <form action="<?php echo BASE_URL; ?>/proses_donasi.php" method="POST">
                        <input type="hidden" name="id_program" value="<?php echo $id_program; ?>">

                        <div class="space-y-4">
                            <div>
                                <label for="nominal_custom" class="block text-sm font-medium text-gray-700">Masukkan Nominal Donasi</label>
                                <div class="relative mt-1">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                                    <input type="text" id="nominal_custom" name="nominal" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange" placeholder="Contoh: 50000" required>
                                </div>
                            </div>
                            <div>
                                <label for="sapaan" class="block text-sm font-medium text-gray-700">Sapaan</label>
                                <select id="sapaan" name="sapaan"
                                    class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                                    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_sapaan'])): ?>
                                        <option <?php if ($_SESSION['user_sapaan'] == 'Bapak') echo 'selected'; ?>>Bapak</option>
                                        <option <?php if ($_SESSION['user_sapaan'] == 'Ibu') echo 'selected'; ?>>Ibu</option>
                                        <option <?php if ($_SESSION['user_sapaan'] == 'Kak') echo 'selected'; ?>>Kak</option>
                                    <?php else: ?>
                                        <option>Bapak</option>
                                        <option>Ibu</option>
                                        <option>Kak</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div>
                                <label for="nama_donatur" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                <input type="text" id="nama_donatur" name="nama_donatur" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange" value="<?php echo isset($_SESSION['user_nama_lengkap']) ? htmlspecialchars($_SESSION['user_nama_lengkap']) : ''; ?>" required>
                            </div>
                            <div>
                                <label for="kontak_donatur" class="block text-sm font-medium text-gray-700">No. WhatsApp</label>
                                <input type="text" id="kontak_donatur" name="kontak_donatur" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange" value="<?php echo isset($_SESSION['user_no_telepon']) ? htmlspecialchars($_SESSION['user_no_telepon']) : ''; ?>" required>
                            </div>
                            <div class="flex items-center">
                                <input id="is_anonim" name="is_anonim" type="checkbox" class="h-4 w-4 text-primary-orange border-gray-300 rounded focus:ring-primary-orange">
                                <label for="is_anonim" class="ml-2 block text-sm text-gray-900">Sembunyikan nama saya</label>
                            </div>

                            <!-- Pilihan Metode Pembayaran -->
                            <div class="pt-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Metode Pembayaran</label>
                                <div class="space-y-2">
                                    <?php
                                        // [PERBAIKAN] Langsung tampilkan semua metode yang sudah difilter sebelumnya
                                        foreach (array_merge(...array_values($metode_pembayaran)) as $metode):
                                    ?>
                                    <label class="flex items-center p-3 border rounded-lg has-[:checked]:bg-orange-50 has-[:checked]:border-primary-orange transition cursor-pointer">
                                        <input type="radio" name="metode_pembayaran_id" value="<?php echo $metode['id']; ?>" class="h-4 w-4 text-primary-orange focus:ring-primary-orange" required>
                                        <span class="ml-3 font-medium text-sm"><?php echo htmlspecialchars($metode['nama_metode']); ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full mt-6 bg-primary-orange text-white text-lg font-bold py-3 rounded-full hover:bg-orange-600 transition duration-300 shadow-lg">
                            Lanjutkan Donasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // [PERBAIKAN] Logika "Lihat Selengkapnya"
    const deskripsiLengkap = <?php echo json_encode(nl2br($program['deskripsi'])); ?>;
    const deskripsiPlain = <?php echo json_encode(strip_tags($program['deskripsi'])); ?>;
    
    const btn = document.getElementById('lihat-selengkapnya');
    const kontenDeskripsi = document.getElementById('deskripsi-konten');

    // Tampilkan tombol hanya jika deskripsi memang panjang
    if (deskripsiPlain.split(/\s+/).length > 70) {
        btn.classList.remove('hidden');
        btn.addEventListener('click', function() {
            kontenDeskripsi.innerHTML = deskripsiLengkap;
            btn.style.display = 'none';
        });
    }

    // Script untuk mengambil nominal dari parameter URL
    const params = new URLSearchParams(window.location.search);
    const nominalZakat = params.get('nominal');
    const donationInput = document.getElementById('nominal_custom');

    if (nominalZakat && donationInput) {
        donationInput.value = new Intl.NumberFormat('id-ID').format(nominalZakat);
    }

    // Script untuk memformat input angka dengan titik ribuan
    donationInput.addEventListener('keyup', function(e) {
        let value = e.target.value.replace(/[^\d]/g, '');
        e.target.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
    });
});
</script>

<?php
require_once 'includes/templates/footer.php';
?>
