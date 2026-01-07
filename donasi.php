<?php
// Menetapkan judul halaman
$page_title = "Donasi Sekarang";

// Memuat file konfigurasi dan template
require_once 'includes/config.php';
require_once 'includes/templates/header.php';

// Query untuk mengambil data program donasi yang aktif
$result_program = $mysqli->query("SELECT id, nama_program, gambar, kategori FROM program ORDER BY nama_program ASC");
$program_data = [];
if ($result_program) {
    while($program_item = $result_program->fetch_assoc()) {
        $program_data[] = $program_item;
    }
    // Mengembalikan pointer hasil query ke awal untuk digunakan di loop HTML
    $result_program->data_seek(0);
}

// Ambil metode pembayaran dan kelompokkan berdasarkan kategori
$result_metode = $mysqli->query("SELECT id, nama_metode, tipe, kategori FROM metode_pembayaran WHERE status = 'Aktif'");
$metode_pembayaran = ['Zakat' => [], 'Infak' => [], 'Qurban' => [], 'Umum' => []];
if ($result_metode) {
    while($metode = $result_metode->fetch_assoc()) {
        $metode_pembayaran[$metode['kategori']][] = $metode;
    }
}
?>

<main class="container mx-auto my-12 px-6">
    <div class="max-w-3xl mx-auto">

        <div id="sapaan-program" class="bg-white p-6 rounded-2xl shadow-lg mb-8 text-center scroll-animate hidden">
            <img id="sapaan-gambar" src="" alt="Gambar Program" class="w-full h-48 object-cover rounded-lg mb-4">
            <p class="text-gray-600">Anda akan berdonasi dalam program:</p>
            <h2 id="sapaan-judul" class="text-2xl font-bold text-dark-text"></h2>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-lg scroll-animate">
            <form action="proses_donasi.php" method="POST" id="donation-form">

                <div class="mb-8">
                    <label class="block text-lg font-semibold text-dark-text mb-3">1. Pilih Nominal Donasi</label>
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <button type="button"
                            class="nominal-btn text-center py-3 px-2 border border-gray-300 rounded-lg hover:border-primary-orange hover:bg-orange-50 transition"
                            data-nominal="50000">Rp 50.000</button>
                        <button type="button"
                            class="nominal-btn text-center py-3 px-2 border border-gray-300 rounded-lg hover:border-primary-orange hover:bg-orange-50 transition"
                            data-nominal="100000">Rp 100.000</button>
                        <button type="button"
                            class="nominal-btn text-center py-3 px-2 border border-gray-300 rounded-lg hover:border-primary-orange hover:bg-orange-50 transition"
                            data-nominal="250000">Rp 250.000</button>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">Rp</span>
                        <input type="text" id="nominal_custom" name="nominal"
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange"
                            placeholder="Masukkan nominal lain" required>
                    </div>
                </div>

                <div class="mb-8">
                    <label for="id_program" class="block text-lg font-semibold text-dark-text mb-3">2. Tujukan Donasi</label>
                    <select id="id_program" name="id_program"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                        
                        <?php if ($result_program && $result_program->num_rows > 0): ?>
                            <?php $is_first = true; ?>
                            <?php while($program = $result_program->fetch_assoc()): ?>
                            <option value="<?php echo $program['id']; ?>" <?php if ($is_first) { echo 'selected'; $is_first = false; } ?>>
                                <?php echo htmlspecialchars($program['nama_program']); ?>
                            </option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option value="">Tidak ada program donasi yang tersedia</option>
                        <?php endif; ?>

                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-lg font-semibold text-dark-text mb-3">3. Lengkapi Data Diri</label>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-1">
                                <label for="sapaan" class="block text-sm font-medium text-gray-700">Sapaan</label>
                                <select id="sapaan" name="sapaan"
                                    class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                                    <option>Bapak</option>
                                    <option>Ibu</option>
                                    
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label for="nama_donatur" class="block text-sm font-medium text-gray-700">Nama
                                    Lengkap</label>
                                <input type="text" id="nama_donatur" name="nama_donatur"
                                    class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange"
                                    required>
                            </div>
                        </div>
                        <div>
                            <label for="kontak_donatur" class="block text-sm font-medium text-gray-700">Nomor HP
                                (WhatsApp)</label>
                            <input type="tel" id="kontak_donatur" name="kontak_donatur"
                                class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange"
                                required placeholder="Contoh: 081234567890">
                        </div>
                        <div class="flex items-center">
                            <input id="is_anonim" name="is_anonim" type="checkbox"
                                class="h-4 w-4 text-primary-orange border-gray-300 rounded focus:ring-primary-orange">
                            <label for="is_anonim" class="ml-2 block text-sm text-gray-900">Sembunyikan nama saya
                                (donasi sebagai Hamba Allah)</label>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-lg font-semibold text-dark-text mb-3">4. Pilih Metode Pembayaran</label>
                    <div id="metode-zakat" class="space-y-2 hidden">
                        <?php foreach (array_merge($metode_pembayaran['Zakat'], $metode_pembayaran['Umum']) as $metode): ?>
                        <label
                            class="flex items-center p-4 border rounded-lg has-[:checked]:bg-orange-50 has-[:checked]:border-primary-orange"><input
                                type="radio" name="metode_pembayaran_id" value="<?php echo $metode['id']; ?>"
                                class="h-4 w-4 text-primary-orange"><span
                                class="ml-3 font-medium"><?php echo htmlspecialchars($metode['nama_metode']); ?></span></label>
                        <?php endforeach; ?>
                    </div>
                    <div id="metode-infaq" class="space-y-2">
                        <?php foreach (array_merge($metode_pembayaran['Infak'], $metode_pembayaran['Umum'], $metode_pembayaran['Qurban']) as $metode): ?>
                        <label
                            class="flex items-center p-4 border rounded-lg has-[:checked]:bg-orange-50 has-[:checked]:border-primary-orange"><input
                                type="radio" name="metode_pembayaran_id" value="<?php echo $metode['id']; ?>"
                                class="h-4 w-4 text-primary-orange"><span
                                class="ml-3 font-medium"><?php echo htmlspecialchars($metode['nama_metode']); ?></span></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-primary-orange text-white text-lg font-bold py-4 rounded-full hover:bg-orange-600 transition duration-300 shadow-lg">
                    Lanjutkan Pembayaran
                </button>
            </form>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nominalButtons = document.querySelectorAll('.nominal-btn');
    const customNominalInput = document.getElementById('nominal_custom');

    nominalButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const value = this.getAttribute('data-nominal');
            customNominalInput.value = new Intl.NumberFormat('id-ID').format(value);

            nominalButtons.forEach(btn => {
                btn.classList.remove('bg-primary-orange', 'text-white', 'border-primary-orange');
                btn.classList.add('border-gray-300');
            });
            this.classList.add('bg-primary-orange', 'text-white', 'border-primary-orange');
            this.classList.remove('border-gray-300');
        });
    });

    customNominalInput.addEventListener('keyup', function(e) {
        let value = e.target.value.replace(/[^\d]/g, '');
        e.target.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';

        nominalButtons.forEach(btn => {
            btn.classList.remove('bg-primary-orange', 'text-white', 'border-primary-orange');
            btn.classList.add('border-gray-300');
        });
    });

    const programSelect = document.getElementById('id_program');
    const sapaanSection = document.getElementById('sapaan-program');
    const sapaanGambar = document.getElementById('sapaan-gambar');
    const sapaanJudul = document.getElementById('sapaan-judul');

    const programData = <?php echo json_encode($program_data); ?>;
    const baseUrl = "<?php echo BASE_URL; ?>";

    programSelect.addEventListener('change', function() {
        const selectedId = this.value;
        const metodeZakat = document.getElementById('metode-zakat');
        const metodeInfaq = document.getElementById('metode-infaq');
        let isZakat = false;

        if (selectedId && selectedId !== "") {
            const selectedProgram = programData.find(p => p.id == selectedId);
            if (selectedProgram) {
                sapaanGambar.src = `${baseUrl}/assets/uploads/program/${selectedProgram.gambar}`;
                sapaanJudul.textContent = selectedProgram.nama_program;
                sapaanSection.classList.remove('hidden');
                if (selectedProgram.kategori && selectedProgram.kategori.toLowerCase() === 'zakat') {
                    isZakat = true;
                }
            }
        } else {
            sapaanSection.classList.add('hidden');
        }

        if (isZakat) {
            metodeZakat.classList.remove('hidden');
            metodeInfaq.classList.add('hidden');
            metodeInfaq.querySelectorAll('input[type=radio]').forEach(rb => rb.checked = false);
        } else {
            metodeZakat.classList.add('hidden');
            metodeInfaq.classList.remove('hidden');
            metodeZakat.querySelectorAll('input[type=radio]').forEach(rb => rb.checked = false);
        }
    });

    // PERUBAHAN 2: Memicu event 'change' pada elemen select saat halaman pertama kali dimuat
    if (programSelect.options.length > 0) {
        programSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<?php
require_once 'includes/templates/footer.php';
?>