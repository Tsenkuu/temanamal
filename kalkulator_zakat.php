<?php
// Menetapkan judul halaman
$page_title = "Kalkulator Zakat";

// Memuat file konfigurasi dan template header baru
require_once 'includes/config.php';
require_once 'includes/templates/header.php';

// URL API kalkulator
$api_url = 'https://logam-mulia-api.vercel.app/prices/hargaemas-com';
?>

<!-- Judul Halaman -->
<section class="bg-white py-12">
    <div class="container mx-auto px-6 text-center scroll-animate">
        <h1 class="text-4xl font-bold text-dark-text">Kalkulator Zakat</h1>
        <p class="text-gray-600 mt-2 max-w-2xl mx-auto">Hitung kewajiban zakat Anda dengan mudah dan akurat. Pilih jenis
            zakat yang ingin Anda hitung di bawah ini.</p>
    </div>
</section>

<!-- Konten Kalkulator Zakat -->
<section class="py-16 px-4 md:px-12 bg-light-bg">
    <div class="container mx-auto max-w-4xl">
        <div class="bg-white rounded-2xl shadow-lg scroll-animate">
            <!-- Navigasi Tab -->
            <div class="p-4 border-b border-gray-200">
                <nav class="flex overflow-x-auto pb-2 -mx-1" aria-label="Tabs">
                    <button class="tab-btn active mx-1" data-target="penghasilan">💼<span
                            class="ml-2 hidden sm:inline">Penghasilan</span></button>
                    <button class="tab-btn mx-1" data-target="tabungan">🏦<span class="ml-2 hidden sm:inline">Tabungan</span></button>
                    <button class="tab-btn mx-1" data-target="emas">🥇<span class="ml-2 hidden sm:inline">Emas</span></button>
                    <button class="tab-btn mx-1" data-target="perdagangan">🛒<span class="ml-2 hidden sm:inline">Perdagangan</span></button>
                    <button class="tab-btn mx-1" data-target="perusahaan">🏢<span class="ml-2 hidden sm:inline">Perusahaan</span></button>
                    <button class="tab-btn mx-1" data-target="pertanian">🌾<span class="ml-2 hidden sm:inline">Pertanian</span></button>
                </nav>
            </div>

            <div class="p-6 md:p-8">
                <!-- Info Harga Emas -->
                <div id="harga-emas-info" class="mb-6 p-3 bg-blue-50 rounded-lg flex items-center">
                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span id="harga-emas-text" class="text-blue-700 text-sm">Memuat harga emas terbaru...</span>
                </div>

                <!-- Form Zakat Penghasilan -->
                <div id="penghasilan" class="tab-content">
                    <h3 class="text-xl font-semibold mb-1">Zakat Penghasilan</h3>
                    <p class="text-gray-500 mb-4">Dihitung dari penghasilan bersih setelah dikurangi kebutuhan pokok.
                    </p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hitung Per</label>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center"><input type="radio" name="periode_penghasilan"
                                        value="bulan" class="zakat-input-radio" checked> <span
                                        class="ml-2">Bulan</span></label>
                                <label class="flex items-center"><input type="radio" name="periode_penghasilan"
                                        value="tahun" class="zakat-input-radio"> <span class="ml-2">Tahun</span></label>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center">
                                <label for="pendapatan" class="block text-sm font-medium text-gray-700">Penghasilan (Rp)</label>
                                <span class="text-xs text-gray-500 tooltip-info" data-tooltip="Masukkan total penghasilan bulanan/tahunan sebelum pajak">ℹ️ Info</span>
                            </div>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                                <input type="text" id="pendapatan"
                                    class="zakat-input pl-10 mt-1 w-full p-3 border border-gray-300 rounded-lg"
                                    placeholder="0">
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center">
                                <label for="penghasilan_lain" class="block text-sm font-medium text-gray-700">Penghasilan Lain (Bonus, Tunjangan) (Rp)</label>
                                <span class="text-xs text-gray-500 tooltip-info" data-tooltip="Masukkan penghasilan tambahan seperti bonus, tunjangan, dll">ℹ️ Info</span>
                            </div>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                                <input type="text" id="penghasilan_lain"
                                    class="zakat-input pl-10 mt-1 w-full p-3 border border-gray-300 rounded-lg"
                                    placeholder="0">
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center">
                                <label for="kebutuhan_pokok" class="block text-sm font-medium text-gray-700">Kebutuhan Pokok (Termasuk Hutang Jatuh Tempo) (Rp)</label>
                                <span class="text-xs text-gray-500 tooltip-info" data-tooltip="Kebutuhan pokok meliputi makanan, pakaian, tempat tinggal, pendidikan, kesehatan, dan utang yang jatuh tempo">ℹ️ Info</span>
                            </div>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                                <input type="text" id="kebutuhan_pokok"
                                    class="zakat-input pl-10 mt-1 w-full p-3 border border-gray-300 rounded-lg"
                                    placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Zakat Tabungan -->
                <div id="tabungan" class="tab-content hidden">
                    <h3 class="text-xl font-semibold mb-1">Zakat Tabungan</h3>
                    <p class="text-gray-500 mb-4">Berlaku untuk tabungan, deposito, dan aset simpanan sejenis yang telah
                        mencapai haul (1 tahun).</p>
                    <div>
                        <div class="flex justify-between items-center">
                            <label for="saldo_tabungan" class="block text-sm font-medium text-gray-700">Saldo Akhir Tabungan (Rp)</label>
                            <span class="text-xs text-gray-500 tooltip-info" data-tooltip="Saldo tabungan dan deposito yang telah disimpan selama 1 tahun">ℹ️ Info</span>
                        </div>
                        <div class="relative mt-1">
                            <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                            <input type="text" id="saldo_tabungan"
                                class="zakat-input pl-10 mt-1 w-full p-3 border border-gray-300 rounded-lg" placeholder="0">
                        </div>
                    </div>
                </div>

                <!-- Form Zakat Emas -->
                <div id="emas" class="tab-content hidden">
                    <h3 class="text-xl font-semibold mb-1">Zakat Emas</h3>
                    <p class="text-gray-500 mb-4">Dikenakan jika emas yang disimpan (tidak dipakai) telah mencapai
                        nisabnya selama 1 tahun.</p>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center">
                                <label for="nilai_emas" class="block text-sm font-medium text-gray-700">Emas yang Dimiliki (gram)</label>
                                <span class="text-xs text-gray-500 tooltip-info" data-tooltip="Hanya emas yang tidak digunakan (disimpan)">ℹ️ Info</span>
                            </div>
                            <input type="number" id="nilai_emas" class="zakat-input-no-prefix"
                                placeholder="min. 85 gram" min="0" step="0.1">
                            <p class="text-xs text-gray-500 mt-1">Nisab: 85 gram emas murni (24 karat)</p>
                        </div>
                    </div>
                </div>

                <!-- Form Zakat Perdagangan -->
                <div id="perdagangan" class="tab-content hidden">
                    <h3 class="text-xl font-semibold mb-1">Zakat Perdagangan</h3>
                    <p class="text-gray-500 mb-4">Dihitung dari aset lancar usaha setelah dikurangi utang jatuh tempo
                        dan telah mencapai haul (1 tahun).</p>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center">
                                <label for="aset_lancar" class="block text-sm font-medium text-gray-700">Aset Lancar (Modal + Keuntungan) (Rp)</label>
                                <span class="text-xs text-gray-500 tooltip-info" data-tooltip="Nilai persediaan barang, piutang yang diharapkan dibayar, dan kas">ℹ️ Info</span>
                            </div>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                                <input type="text" id="aset_lancar"
                                    class="zakat-input pl-10 mt-1 w-full p-3 border border-gray-300 rounded-lg"
                                    placeholder="0">
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center">
                                <label for="utang_dagang" class="block text-sm font-medium text-gray-700">Utang Jatuh Tempo (Rp)</label>
                                <span class="text-xs text-gray-500 tooltip-info" data-tooltip="Utang yang harus segera dibayar">ℹ️ Info</span>
                            </div>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                                <input type="text" id="utang_dagang"
                                    class="zakat-input pl-10 mt-1 w-full p-3 border border-gray-300 rounded-lg"
                                    placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Zakat Perusahaan -->
                <div id="perusahaan" class="tab-content hidden">
                    <h3 class="text-xl font-semibold mb-1">Zakat Perusahaan</h3>
                    <p class="text-gray-500 mb-4">Dikenakan atas aset perusahaan yang telah berjalan selama 1 tahun dan
                        mencapai nisab.</p>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center">
                                <label for="aset_perusahaan" class="block text-sm font-medium text-gray-700">Aset Kena Zakat (Rp)</label>
                                <span class="text-xs text-gray-500 tooltip-info" data-tooltip="Modal kerja, investasi jangka pendek, dan piutang">ℹ️ Info</span>
                            </div>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                                <input type="text" id="aset_perusahaan"
                                    class="zakat-input pl-10 mt-1 w-full p-3 border border-gray-300 rounded-lg"
                                    placeholder="0">
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center">
                                <label for="utang_perusahaan" class="block text-sm font-medium text-gray-700">Utang Perusahaan (Rp)</label>
                                <span class="text-xs text-gray-500 tooltip-info" data-tooltip="Utang jangka pendek yang harus segera dilunasi">ℹ️ Info</span>
                            </div>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                                <input type="text" id="utang_perusahaan"
                                    class="zakat-input pl-10 mt-1 w-full p-3 border border-gray-300 rounded-lg"
                                    placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Zakat Pertanian -->
                <div id="pertanian" class="tab-content hidden">
                    <h3 class="text-xl font-semibold mb-1">Zakat Pertanian</h3>
                    <p class="text-gray-500 mb-4">Dibayarkan saat panen jika hasil panen telah mencapai nisab.</p>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center">
                                <label for="hasil_panen" class="block text-sm font-medium text-gray-700">Nilai Hasil Panen (Rp)</label>
                                <span class="text-xs text-gray-500 tooltip-info" data-tooltip="Nilai hasil panen setelah dikurangi biaya">ℹ️ Info</span>
                            </div>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                                <input type="text" id="hasil_panen"
                                    class="zakat-input pl-10 mt-1 w-full p-3 border border-gray-300 rounded-lg"
                                    placeholder="0">
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center">
                                <label for="jenis_irigasi" class="block text-sm font-medium text-gray-700">Jenis Irigasi</label>
                                <span class="text-xs text-gray-500 tooltip-info" data-tooltip="Pilih jenis irigasi yang digunakan">ℹ️ Info</span>
                            </div>
                            <select id="jenis_irigasi" class="zakat-input-no-prefix">
                                <option value="0.05">Irigasi (Berbayar) - 5%</option>
                                <option value="0.1">Tadah Hujan (Gratis) - 10%</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tombol Hitung -->
                <div class="mt-8 border-t pt-6">
                       <button id="hitung-btn" class="w-full text-center bg-primary-orange text-white px-8 py-3 rounded-full font-bold hover:bg-orange-600 transition duration-300 shadow-lg">
                            Hitung Zakat Kamu
                        </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Tooltip Container -->
<div id="tooltip" class="absolute invisible max-w-xs p-2 bg-gray-800 text-white text-sm rounded shadow-lg z-50"></div>

<!-- Modal/Popup Hasil -->
<div id="hasil-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 max-w-md w-full relative modal-content-animate">
        <button id="close-modal-btn" class="absolute top-3 right-4 text-gray-500 hover:text-gray-800 text-3xl font-light">&times;</button>
        <h3 class="text-2xl font-bold text-dark-text mb-4 text-center">Hasil Perhitungan Zakat</h3>
        
        <div id="hasil-zakat" class="text-left bg-gray-50 p-4 rounded-lg">
            <p id="jumlah-zakat" class="text-3xl font-bold text-primary-orange text-center">Rp 0</p>
            <p id="nisab-info" class="text-sm text-gray-600 mt-2 text-center"></p>
        </div>
        
        <div class="text-center">
			<a href="program/21" id="tunaikan-btn" 
			   class="hidden mt-6 inline-block text-center bg-primary-orange text-white px-8 py-3 rounded-full font-bold hover:bg-orange-600 transition duration-300 shadow-lg">
				Tunaikan Zakat Sekarang
			</a>
		</div>

    </div>
</div>


<!-- CSS Kustom untuk Tab dan Input -->
<style>
.tab-btn {
    padding: 0.75rem 1rem;
    text-align: center;
    font-weight: 600;
    color: #22223b;
    background-color: #f3f4f6;
    border-radius: 0.75rem;
    transition: all 0.3s;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    border: 1px solid #e5e7eb;
    outline: none;
    min-width: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    white-space: nowrap;
    flex-shrink: 0;
}

.tab-btn:hover {
    box-shadow: 0 4px 14px 0 rgb(255 140 0 / 10%);
    transform: translateY(-2px);
}

.tab-btn.active {
    background-image: linear-gradient(to right, #FF8C00, #FFA600);
    color: #fff !important;
    box-shadow: 0 4px 14px 0 rgb(255 140 0 / 20%);
    transform: translateY(-4px);
    border-color: #FF8C00;
}

.zakat-input {
    @apply w-full p-3 bg-gray-50 border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-orange/50 focus:border-primary-orange transition;
}

.zakat-input-no-prefix {
    @apply mt-1 w-full p-3 bg-gray-50 border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-orange/50 focus:border-primary-orange transition;
}

.zakat-input-radio {
    @apply h-4 w-4 text-primary-orange focus:ring-primary-orange border-gray-300;
}

.tooltip-info {
    cursor: pointer;
    position: relative;
}

#tooltip {
    transition: opacity 0.3s;
}

/* Animasi untuk Modal */
#hasil-modal.flex {
    display: flex;
}
.modal-content-animate {
    animation: zoomIn 0.3s ease-out;
}
@keyframes zoomIn {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

@media (max-width: 640px) {
    .tab-btn {
        min-width: 80px;
        padding: 0.6rem 0.8rem;
        font-size: 0.8rem;
    }
    
    .tab-btn span {
        margin-left: 0.25rem;
    }
}
</style>

<!-- JavaScript untuk Kalkulator -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');
    const hargaEmasInfo = document.getElementById('harga-emas-info');
    const hargaEmasText = document.getElementById('harga-emas-text');
    const tooltip = document.getElementById('tooltip');

    // Elementos del Modal
    const hitungBtn = document.getElementById('hitung-btn');
    const hasilModal = document.getElementById('hasil-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    
    let hargaEmasPerGram = 0;
    let nisabEmas = 0;
    const hargaBerasPerKg = 15000;
    const nisabPertanian = 524 * hargaBerasPerKg;
    
    // Format Rupiah
    const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
    
    // Fungsi untuk mendapatkan angka dari input
    const getAngka = (id) => {
        const element = document.getElementById(id);
        return element ? parseFloat(element.value.replace(/[^\d]/g, '')) || 0 : 0;
    };
    
    // Format input dengan mask rupiah
    const formatInput = (input) => {
        if (!input) return;
        let value = input.value.replace(/[^\d]/g, '');
        input.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
    };
    
    // Setup tooltip (Mobile Friendly)
    document.addEventListener('click', function(e) {
        if (!e.target.matches('.tooltip-info')) {
            tooltip.classList.add('invisible');
        }
    });

    document.querySelectorAll('.tooltip-info').forEach(el => {
        el.addEventListener('click', (e) => {
            e.stopPropagation(); 
            const isVisible = !tooltip.classList.contains('invisible');
            const tooltipText = e.currentTarget.getAttribute('data-tooltip');
            const isSameTarget = tooltip.dataset.target === tooltipText;

            if (isVisible && isSameTarget) {
                tooltip.classList.add('invisible');
            } else {
                tooltip.textContent = tooltipText;
                tooltip.dataset.target = tooltipText;
                tooltip.classList.remove('invisible');
                const rect = e.currentTarget.getBoundingClientRect();
                tooltip.style.left = `${rect.left}px`;
                tooltip.style.top = `${rect.bottom + 5}px`;
            }
        });
    });
    
    // Fungsi untuk mendapatkan harga emas dari API
    async function getHargaEmas() {
        try {
            const response = await fetch('<?php echo $api_url; ?>');
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            
            if (data && data.data && data.data.length > 0 && data.data[0].sell) {
                hargaEmasPerGram = data.data[0].sell;
                hargaEmasInfo.classList.remove('bg-yellow-50', 'text-yellow-700', 'bg-red-50', 'text-red-700');
                hargaEmasInfo.classList.add('bg-green-50', 'text-green-700');
                hargaEmasText.innerHTML = `✅ Harga emas terbaru: <strong>${formatRupiah(hargaEmasPerGram)}/gram</strong>. Nisab: ${formatRupiah(85 * hargaEmasPerGram)}`;
                return hargaEmasPerGram;
            } else {
                throw new Error('Struktur respon API tidak valid atau harga jual tidak ditemukan');
            }
        } catch (error) {
            console.error('Error fetching gold price:', error);
            hargaEmasPerGram = 2100000; // Harga default jika semua gagal (diperbarui ke nilai yang lebih realistis)
            hargaEmasInfo.classList.remove('bg-yellow-50', 'text-yellow-700', 'bg-green-50', 'text-green-700');
            hargaEmasInfo.classList.add('bg-red-50', 'text-red-700');
            hargaEmasText.innerHTML = `⚠️ Gagal mengambil harga emas. Menggunakan harga default: <strong>${formatRupiah(hargaEmasPerGram)}/gram</strong>. Nisab: ${formatRupiah(85 * hargaEmasPerGram)}`;
            return hargaEmasPerGram;
        }
    }
    
    // [FUNGSI YANG DIPERBARUI] Update hasil perhitungan zakat dan link tombol
    function updateHasil(hasil) {
        const jumlahZakatEl = document.getElementById('jumlah-zakat');
        const nisabInfoEl = document.getElementById('nisab-info');
        const tunaikanBtn = document.getElementById('tunaikan-btn');
        
        // URL dasar untuk tombol donasi
        const baseUrl = 'program/21';

        if (hasil.wajibZakat) {
            jumlahZakatEl.textContent = formatRupiah(hasil.zakat);
            jumlahZakatEl.classList.remove('text-red-500');
            jumlahZakatEl.classList.add('text-primary-orange');
            nisabInfoEl.textContent = hasil.nisabInfoText;
            
            // Perbarui URL tombol dengan nominal zakat (gunakan Math.round untuk menghindari desimal)
            tunaikanBtn.href = `${baseUrl}?nominal=${Math.round(hasil.zakat)}`;
            
            tunaikanBtn.classList.remove('hidden');
        } else {
            jumlahZakatEl.innerHTML = '<span class="text-xl text-red-500">Anda Belum Wajib Membayar Zakat</span>';
            nisabInfoEl.innerHTML = `Anda dapat tetap berinfak jika ingin. ${hasil.nisabInfoText}`;
            tunaikanBtn.classList.add('hidden');
        }
    }
    
    // Hitung zakat berdasarkan tab aktif
    async function hitungZakat() {
        if (hargaEmasPerGram === 0) {
            await getHargaEmas();
        }
        
        nisabEmas = 85 * hargaEmasPerGram;
        const activeTab = document.querySelector('.tab-btn.active').dataset.target;
        let zakat = 0, wajibZakat = false, nisabInfoText = '';
        
        if (activeTab === 'penghasilan') {
            const periode = document.querySelector('input[name="periode_penghasilan"]:checked').value;
            const pendapatan = getAngka('pendapatan');
            const penghasilanLain = getAngka('penghasilan_lain');
            const kebutuhanPokok = getAngka('kebutuhan_pokok');
            const totalPendapatan = pendapatan + penghasilanLain;
            
            if (periode === 'bulan') {
                const pendapatanBersih = totalPendapatan - kebutuhanPokok;
                const nisabBulanan = nisabEmas / 12;
                if (pendapatanBersih >= nisabBulanan) {
                    zakat = pendapatanBersih * 0.025;
                    wajibZakat = true;
                    nisabInfoText = `Nisab zakat penghasilan per bulan: ${formatRupiah(nisabBulanan)}.`;
                } else {
                    nisabInfoText = `Pendapatan bersih Anda (${formatRupiah(pendapatanBersih)}) belum mencapai nisab (${formatRupiah(nisabBulanan)}).`;
                }
            } else { // tahun
                const pendapatanTahunan = totalPendapatan - (kebutuhanPokok * 12);
                 if (pendapatanTahunan >= nisabEmas) {
                    zakat = pendapatanTahunan * 0.025;
                    wajibZakat = true;
                    nisabInfoText = `Nisab zakat penghasilan per tahun: ${formatRupiah(nisabEmas)}.`;
                } else {
                    nisabInfoText = `Pendapatan bersih Anda (${formatRupiah(pendapatanTahunan)}) belum mencapai nisab (${formatRupiah(nisabEmas)}).`;
                }
            }
        } 
        else if (['tabungan', 'perdagangan', 'perusahaan'].includes(activeTab)) {
            let totalHarta = 0;
            if (activeTab === 'tabungan') totalHarta = getAngka('saldo_tabungan');
            if (activeTab === 'perdagangan') totalHarta = getAngka('aset_lancar') - getAngka('utang_dagang');
            if (activeTab === 'perusahaan') totalHarta = getAngka('aset_perusahaan') - getAngka('utang_perusahaan');
            
            if (totalHarta >= nisabEmas) {
                zakat = totalHarta * 0.025;
                wajibZakat = true;
                nisabInfoText = `Nisab untuk ${activeTab}: ${formatRupiah(nisabEmas)}.`;
            } else {
                nisabInfoText = `Total harta Anda (${formatRupiah(totalHarta)}) belum mencapai nisab (${formatRupiah(nisabEmas)}).`;
            }
        } 
        else if (activeTab === 'emas') {
            const gramEmas = parseFloat(document.getElementById('nilai_emas').value) || 0;
            if (gramEmas >= 85) {
                zakat = (gramEmas * hargaEmasPerGram) * 0.025;
                wajibZakat = true;
                nisabInfoText = `Nisab emas: 85 gram (${formatRupiah(nisabEmas)}).`;
            } else {
                nisabInfoText = `Emas Anda (${gramEmas} gram) belum mencapai nisab (85 gram).`;
            }
        } 
        else if (activeTab === 'pertanian') {
            const hasilPanen = getAngka('hasil_panen');
            const jenisIrigasi = parseFloat(document.getElementById('jenis_irigasi').value);
            if (hasilPanen >= nisabPertanian) {
                zakat = hasilPanen * jenisIrigasi;
                wajibZakat = true;
                nisabInfoText = `Nisab pertanian: ${formatRupiah(nisabPertanian)} (setara 524 kg beras).`;
            } else {
                nisabInfoText = `Hasil panen Anda (${formatRupiah(hasilPanen)}) belum mencapai nisab (${formatRupiah(nisabPertanian)}).`;
            }
        }
        
        updateHasil({ zakat, wajibZakat, nisabInfoText });
    }
    
    // Inisialisasi tabs
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.add('hidden'));
            this.classList.add('active');
            document.getElementById(this.dataset.target).classList.remove('hidden');
        });
    });
    
    // Event listeners untuk input
    const allInputs = document.querySelectorAll('.zakat-input, .zakat-input-no-prefix, .zakat-input-radio, select');
    allInputs.forEach(input => {
        input.addEventListener('input', () => {
             if (input.classList.contains('zakat-input')) {
                formatInput(input);
            }
        });
    });
    
    // Fungsi untuk Modal
    function openModal() {
        hasilModal.classList.remove('hidden');
        hasilModal.classList.add('flex');
    }
    function closeModal() {
        hasilModal.classList.add('hidden');
        hasilModal.classList.remove('flex');
    }

    hitungBtn.addEventListener('click', async () => {
        await hitungZakat();
        openModal();
    });
    closeModalBtn.addEventListener('click', closeModal);
    hasilModal.addEventListener('click', (e) => {
        if (e.target === hasilModal) {
            closeModal();
        }
    });

    // Inisialisasi pertama
    getHargaEmas();
});
</script>

<?php
require_once 'includes/templates/footer.php';
?>
