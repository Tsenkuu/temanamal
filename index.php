<?php
// Menetapkan judul halaman
$page_title = "Beranda";

// Memuat file konfigurasi untuk koneksi database dan pengaturan dasar
require_once 'includes/config.php';
// Memuat header baru dengan desain Tailwind CSS
require_once 'includes/templates/header.php';

// Mengambil data untuk Total Donasi Disalurkan
$result_total = $mysqli->query("SELECT nilai_pengaturan FROM pengaturan WHERE nama_pengaturan = 'total_donasi_disalurkan'");
$total_donasi_disalurkan = $result_total ? $result_total->fetch_assoc()['nilai_pengaturan'] ?? 0 : 0;

// Mengambil data untuk Slider Gambar Galeri
$result_slider = $mysqli->query("SELECT nama_file FROM slider_images ORDER BY urutan ASC, id DESC");

// Mengambil data untuk Program Unggulan (6 terbaru)
$result_program = $mysqli->query("SELECT id, nama_program, slug, gambar, target_donasi, donasi_terkumpul FROM program ORDER BY created_at DESC LIMIT 6");

// Mengambil data untuk Berita Terbaru (5 terbaru yang sudah 'published', berita dan opini)
$result_berita = $mysqli->query("SELECT id, judul, teras_berita, gambar, created_at, slug, type FROM berita WHERE status = 'published' ORDER BY created_at DESC LIMIT 5");

// Mengambil data untuk Tim Personalia, diurutkan berdasarkan kolom 'urutan'
$result_tim = $mysqli->query("SELECT nama_lengkap, jabatan, foto FROM amil WHERE tampilkan_di_beranda = 'Ya' ORDER BY urutan ASC");
?>

<style>
:root {
    --primary-orange: #fb8201;
    --primary-orange-dark: #f57400;
    --secondary-orange: #ffb253;
    --dark-text: #1f2937;
}

.hero-gradient {
    background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
}

.glass-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.card-hover {
    transition: all 0.3s ease;
}

.card-hover:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.scroll-animate {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.scroll-animate.animated {
    opacity: 1;
    transform: translateY(0);
}

.sticky-donate-button {
    transition: transform 0.3s ease-in-out, visibility 0.3s;
}

.sticky-donate-button.hidden {
    transform: translateY(150%);
}

.menu-open .sticky-donate-button {
    display: none;
}

.horizontal-scroll-container {
    display: flex;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    -ms-overflow-style: none;
    scrollbar-width: none;
    padding: 1rem 0;
}

.horizontal-scroll-container::-webkit-scrollbar {
    display: none;
}

.slider-item {
    scroll-snap-align: start;
    flex: 0 0 auto;
    margin-right: 1rem;
}

.slider-item.gallery {
    width: 85%;
    max-width: 500px;
}

.slider-item.program {
    width: 80%;
    max-width: 320px;
}

.slider-item.berita {
    width: 80%;
    max-width: 340px;
}

@media (min-width: 768px) {
    .slider-item {
        margin-right: 1.5rem;
    }
}
</style>

<main class="bg-gray-50 overflow-hidden">
    <section class="relative hero-gradient text-white pt-24 pb-28 md:pt-28 md:pb-32 px-4 text-center rounded-b-3xl">
        <div class="relative z-10 max-w-4xl mx-auto scroll-animate">
            <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-4">Bergerak Bersama, Tebar Kebaikan</h1>
            <p class="text-lg md:text-xl mb-8 opacity-95 max-w-2xl mx-auto">Salurkan Zakat, Infak, dan Sedekah Anda
                melalui Lazismu untuk membantu sesama dengan amanah dan transparan.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/donasi"
                    class="inline-block bg-white text-primary-orange font-bold py-3 px-8 rounded-full shadow-lg transform transition hover:scale-105 hover:shadow-xl">
                    Donasi Sekarang <span class="ml-1">✨</span>
                </a>
                <a href="#program"
                    class="inline-block bg-transparent border-2 border-white font-bold py-3 px-8 rounded-full transform transition hover:bg-white hover:text-primary-orange">
                    Lihat Program
                </a>
            </div>
        </div>
        <div class="absolute inset-0 overflow-hidden z-0">
            <div class="absolute -top-24 -right-24 w-80 h-80 bg-white opacity-10 rounded-full"></div>
            <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-white opacity-10 rounded-full"></div>
        </div>
    </section>

    <section class="container mx-auto px-4 -mt-20 relative z-10">
        <div class="glass-card max-w-3xl mx-auto rounded-2xl shadow-lg p-6 text-center">
            <h2 class="text-base font-semibold text-gray-600 mb-1">Total Donasi Telah Disalurkan</h2>
            <p class="text-3xl md:text-4xl font-extrabold text-primary-orange">Rp
                <?php echo number_format($total_donasi_disalurkan, 0, ',', '.'); ?></p>
        </div>
    </section>

    <section class="py-20 px-4" aria-label="Galeri Foto Kegiatan">
        <div class="container mx-auto">
            <div class="text-center mb-12 scroll-animate">
                <h2 class="text-3xl font-bold text-dark-text mb-3">Galeri Kegiatan</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Dokumentasi program kemanusiaan yang telah kami laksanakan
                    bersama donatur.</p>
            </div>
            <div class="horizontal-scroll-wrapper">
                <div id="imageSlider" class="horizontal-scroll-container pl-4 md:pl-0">
                    <?php if ($result_slider && $result_slider->num_rows > 0): ?>
                    <?php while($slide = $result_slider->fetch_assoc()): ?>
                    <div class="slider-item gallery aspect-video rounded-2xl overflow-hidden shadow-lg">
                        <img src="<?php echo BASE_URL . '/assets/images/' . htmlspecialchars($slide['nama_file']); ?>"
                            alt="Foto kegiatan Lazismu" class="w-full h-full object-cover">
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <div
                        class="slider-item gallery aspect-video rounded-2xl overflow-hidden shadow-lg bg-gray-200 flex items-center justify-center">
                        <p class="text-gray-500">Gambar tidak tersedia</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section id="program" class="py-20 px-4 bg-white">
        <div class="container mx-auto">
            <div class="text-center mb-12 scroll-animate">
                <h2 class="text-3xl md:text-4xl font-bold text-dark-text mb-3">Program Kebaikan Lazismu</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Pilih program yang paling menyentuh hati Anda dan jadilah
                    bagian dari perubahan.</p>
            </div>

            <div class="horizontal-scroll-wrapper">
                <div id="programSlider" class="horizontal-scroll-container pl-4 md:pl-0">
                    <?php if ($result_program && $result_program->num_rows > 0): ?>
                    <?php while($program = $result_program->fetch_assoc()): 
                            $persentase = $program['target_donasi'] > 0 ? min(100, ($program['donasi_terkumpul'] / $program['target_donasi']) * 100) : 0;
                        ?>
                    <div class="slider-item program">
                        <div
                            class="program-card bg-white rounded-2xl overflow-hidden shadow-md flex flex-col h-full card-hover">
                            <a href="program/<?php echo htmlspecialchars($program['id']); ?>">
                                <img src="<?php echo BASE_URL . '/assets/uploads/program/' . htmlspecialchars($program['gambar']); ?>"
                                    alt="<?php echo htmlspecialchars($program['nama_program']); ?>"
                                    class="w-full h-48 object-cover">
                            </a>
                            <div class="p-5 flex flex-col flex-grow">
                                <h3 class="text-lg font-semibold text-dark-text mb-3 flex-grow">
                                    <?php echo htmlspecialchars($program['nama_program']); ?></h3>
                                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                    <div class="bg-primary-orange h-2 rounded-full"
                                        style="width: <?php echo $persentase; ?>%"></div>
                                </div>
                                <div class="text-xs text-gray-600 mb-4 flex justify-between">
                                    <span>Terkumpul <strong>Rp
                                            <?php echo number_format($program['donasi_terkumpul'], 0, ',', '.'); ?></strong></span>
                                    <span><?php echo number_format($persentase, 0); ?>%</span>
                                </div>
                                <a href="program/<?php echo $program['id']; ?>"
                                    class="mt-auto block w-full text-center bg-primary-orange text-white px-6 py-2.5 rounded-lg font-bold hover:bg-primary-orange-dark transition-colors transform hover:scale-105">
                                    Donasi
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <p class="w-full text-center text-gray-500 py-8">Belum ada program unggulan.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center mt-12 scroll-animate">
                <a href="program.php"
                    class="inline-block bg-orange-100 text-primary-orange font-bold py-3 px-8 rounded-full transform transition hover:scale-105 hover:bg-orange-200">
                    Lihat Semua Program
                </a>
            </div>
        </div>
    </section>

    <!-- [DIKEMBALIKAN] Kalkulator Zakat Section -->
    <section class="py-20 px-4">
        <div class="container mx-auto">
            <div
                class="relative rounded-2xl bg-gradient-to-r from-primary-orange to-secondary-orange text-white p-8 md:p-12 text-center overflow-hidden scroll-animate">
                <div class="relative z-10">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4">Sudah Tepatkah Zakat Anda?</h2>
                    <p class="text-lg mb-8 max-w-2xl mx-auto opacity-90">Gunakan kalkulator kami untuk menghitung zakat
                        dengan mudah sesuai syariat.</p>
                    <a href="kalkulator_zakat.php"
                        class="inline-block bg-white text-primary-orange font-bold py-3 px-8 rounded-full shadow-lg transform transition hover:scale-105 hover:shadow-xl">
                        Buka Kalkulator Zakat
                    </a>
                </div>
                <div class="absolute -bottom-10 -left-10 w-32 h-32 border-4 border-white rounded-full opacity-20"></div>
                <div class="absolute -top-10 -right-10 w-48 h-48 border-4 border-white rounded-full opacity-20"></div>
            </div>
        </div>
    </section>

    <section id="berita" class="py-20 px-4 bg-white">
        <div class="container mx-auto">
            <div class="text-center mb-12 scroll-animate">
                <h2 class="text-3xl md:text-4xl font-bold text-dark-text mb-3">Kabar Kebaikan Terbaru</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Ikuti setiap langkah kebaikan yang kita ciptakan bersama
                    melalui berita dan artikel.</p>
            </div>

            <div class="horizontal-scroll-wrapper">
                <div id="beritaSlider" class="horizontal-scroll-container pl-4 md:pl-0">
                    <?php if ($result_berita && $result_berita->num_rows > 0): ?>
                    <?php while($berita = $result_berita->fetch_assoc()): ?>
                    <div class="slider-item berita">
                        <article
                            class="news-card bg-white rounded-2xl overflow-hidden shadow-md flex flex-col h-full card-hover">
                            <a href="berita/<?php echo htmlspecialchars($berita['slug']); ?>">
                                <img src="<?php echo BASE_URL . '/assets/uploads/berita/' . htmlspecialchars($berita['gambar']); ?>"
                                    alt="<?php echo htmlspecialchars($berita['judul']); ?>"
                                    class="w-full h-48 object-cover">
                            </a>
                            <div class="p-5 flex flex-col flex-grow">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-sm text-gray-500">
                                        <?php echo date('d F Y', strtotime($berita['created_at'])); ?></p>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $berita['type'] == 'berita' ? 'text-blue-800 bg-blue-100' : 'text-purple-800 bg-purple-100'; ?>">
                                        <?php echo ucfirst($berita['type']); ?>
                                    </span>
                                </div>
                                <h3 class="text-lg font-semibold text-dark-text mb-3 leading-tight flex-grow">
                                    <a href="berita/<?php echo htmlspecialchars($berita['slug']); ?>"
                                        class="hover:text-primary-orange transition-colors">
                                        <?php echo htmlspecialchars($berita['judul']); ?>
                                    </a>
                                </h3>
                                <a href="berita/<?php echo htmlspecialchars($berita['slug']); ?>"
                                    class="inline-flex items-center text-primary-orange font-semibold hover:text-orange-600 group mt-auto">
                                    Baca Selengkapnya <i
                                        class="bi bi-arrow-right ml-1 group-hover:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </article>
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <p class="w-full text-center text-gray-500 py-8">Belum ada berita yang diterbitkan.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-center mt-12 scroll-animate">
                <a href="berita.php"
                    class="inline-block bg-orange-100 text-primary-orange font-bold py-3 px-8 rounded-full transform transition hover:scale-105 hover:bg-orange-200">
                    Lihat Semua Berita
                </a>
            </div>
        </div>
    </section>

    <!-- [DIKEMBALIKAN] Tim Kami Section -->
    <section id="tim" class="py-20 px-4">
        <div class="container mx-auto">
            <div class="text-center mb-12 scroll-animate">
                <h2 class="text-3xl md:text-4xl font-bold text-dark-text mb-3">Tim Amanah Kami</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Kenali tim berdedikasi yang siap menyalurkan kebaikan Anda
                    kepada yang membutuhkan.</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 md:gap-8">
                <?php if ($result_tim && $result_tim->num_rows > 0): ?>
                <?php while($amil = $result_tim->fetch_assoc()): 
                        $foto_path = 'assets/uploads/amil/' . htmlspecialchars($amil['foto']);
                    ?>
                <div class="text-center scroll-animate">
                    <img src="<?php echo BASE_URL . '/' . $foto_path; ?>"
                        alt="Foto <?php echo htmlspecialchars($amil['nama_lengkap']); ?>"
                        class="w-24 h-24 md:w-32 md:h-32 mx-auto rounded-full object-cover mb-4 shadow-lg border-4 border-white">
                    <h3 class="font-bold text-dark-text text-sm md:text-base">
                        <?php echo htmlspecialchars($amil['nama_lengkap']); ?></h3>
                    <p class="text-primary-orange text-xs md:text-sm font-semibold">
                        <?php echo htmlspecialchars($amil['jabatan']); ?></p>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <p class="col-span-full text-center text-gray-500 py-8">Data tim belum tersedia.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

</main>

<div id="sticky-donate" class="sticky-donate-button hidden fixed bottom-4 right-4 md:hidden z-30">
    <a href="donasi.php"
        class="flex items-center justify-center bg-primary-orange text-white font-bold px-6 py-3 rounded-full shadow-lg text-lg">
        <i class="bi bi-heart-fill mr-2"></i> Donasi Sekarang
    </a>
</div>

<?php
require_once 'includes/templates/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const scrollElements = document.querySelectorAll('.scroll-animate');
    const elementInView = (el) => {
        const elementTop = el.getBoundingClientRect().top;
        return (elementTop <= (window.innerHeight || document.documentElement.clientHeight) - 50);
    };
    const handleScrollAnimation = () => {
        scrollElements.forEach((el) => {
            if (elementInView(el)) {
                el.classList.add('animated');
            }
        });
    };
    window.addEventListener('scroll', handleScrollAnimation);
    handleScrollAnimation();

    const stickyButton = document.getElementById('sticky-donate');
    if (stickyButton) {
        let lastScrollTop = 0;
        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            if (scrollTop > lastScrollTop && scrollTop > 400) {
                stickyButton.classList.remove('hidden');
            } else if (scrollTop < lastScrollTop || scrollTop <= 400) {
                stickyButton.classList.add('hidden');
            }
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        }, false);
    }

    function enableDragScroll(elementId) {
        const slider = document.getElementById(elementId);
        if (!slider) return;
        let isDown = false,
            startX, scrollLeft;

        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            slider.style.cursor = 'grabbing';
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
        });
        slider.addEventListener('mouseleave', () => {
            isDown = false;
            slider.style.cursor = 'grab';
        });
        slider.addEventListener('mouseup', () => {
            isDown = false;
            slider.style.cursor = 'grab';
        });
        slider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 2;
            slider.scrollLeft = scrollLeft - walk;
        });
    }

    enableDragScroll('imageSlider');
    enableDragScroll('programSlider');
    enableDragScroll('beritaSlider');
});
</script>
