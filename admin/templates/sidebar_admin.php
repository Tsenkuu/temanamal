<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Inisialisasi notifikasi
$pending_donasi_count = $mysqli->query("SELECT COUNT(id) as total FROM donasi WHERE status = 'Menunggu Konfirmasi'")->fetch_assoc()['total'] ?? 0;
$pending_berita_count = $mysqli->query("SELECT COUNT(id) as total FROM berita WHERE status = 'pending'")->fetch_assoc()['total'] ?? 0;
$pending_komentar_count = $mysqli->query("SELECT COUNT(id) as total FROM komentar WHERE status = 'pending'")->fetch_assoc()['total'] ?? 0;

// Peta grup untuk menentukan grup mana yang aktif
$group_map = [
    'utama' => ['dashboard.php', 'statistik.php', 'kelola_program.php', 'tambah_program.php', 'edit_program.php', 'kelola_berita.php', 'tambah_berita.php', 'edit_berita.php', 'kelola_komentar.php', 'setujui_komentar.php', 'kelola_slider.php', 'kelola_majalah.php', 'tambah_majalah.php', 'hapus_majalah.php'],
    'donasi' => ['kelola_pembayaran.php', 'tambah_metode_pembayaran.php', 'edit_metode_pembayaran.php', 'konfirmasi_donasi.php', 'riwayat_donasi.php', 'kelola_laporan.php', 'tambah_laporan.php', 'export_donasi.php'],
    'tim' => ['kelola_amil.php', 'tambah_amil.php', 'edit_amil.php', 'kelola_admin.php', 'kelola_user.php'],
    'infak' => ['kelola_kotak_infak.php', 'tambah_kotak_infak.php', 'edit_kotak_infak.php', 'kelola_tugas.php', 'peta_kotak_infak.php', 'riwayat_pengambilan.php', 'edit_pengambilan.php'],
    'pengaturan' => ['pengaturan.php', 'ganti_sandi.php']
];

function is_group_active($group_key, $current_page, $map) {
    return in_array($current_page, $map[$group_key] ?? []);
}

function is_link_active($page_name, $current_page) {
    // Menangani kasus di mana halaman edit/tambah harus mengaktifkan link kelola utamanya
    $related_pages = [
        'kelola_program.php' => ['tambah_program.php', 'edit_program.php'],
        'kelola_berita.php' => ['tambah_berita.php', 'edit_berita.php'],
        'kelola_majalah.php' => ['tambah_majalah.php', 'hapus_majalah.php'],
    ];
    return $current_page === $page_name || (isset($related_pages[$page_name]) && in_array($current_page, $related_pages[$page_name]));
}

$menu_items = [
    'utama' => [
        'title' => 'Utama', 'icon' => 'bi-speedometer2',
        'items' => [
            ['page' => 'dashboard.php', 'label' => 'Dashboard'],
            ['page' => 'statistik.php', 'label' => 'Statistik'],
            ['page' => 'kelola_program.php', 'label' => 'Program'],
            ['page' => 'kelola_berita.php', 'label' => 'Berita', 'count' => $pending_berita_count],
            ['page' => 'kelola_komentar.php', 'label' => 'Komentar', 'count' => $pending_komentar_count],
            ['page' => 'kelola_slider.php', 'label' => 'Slider'],
            ['page' => 'kelola_majalah.php', 'label' => 'Majalah'],
        ]
    ],
    'donasi' => [
        'title' => 'Donasi', 'icon' => 'bi-wallet-fill',
        'items' => [
            ['page' => 'kelola_pembayaran.php', 'label' => 'Metode Bayar'],
            ['page' => 'konfirmasi_donasi.php', 'label' => 'Konfirmasi', 'count' => $pending_donasi_count],
            ['page' => 'riwayat_donasi.php', 'label' => 'Riwayat Donasi'],
            ['page' => 'kelola_laporan.php', 'label' => 'Impor Laporan'],
        ]
    ],
    'tim' => [
        'title' => 'Manajemen Tim', 'icon' => 'bi-people-fill',
        'items' => [
             ['page' => 'kelola_amil.php', 'label' => 'Kelola Amil'],
             ['page' => 'kelola_user.php', 'label' => 'Kelola Donatur'],
        ]
    ],
     'infak' => [
        'title' => 'Kotak Infak', 'icon' => 'bi-box2-heart-fill',
        'items' => [
             ['page' => 'kelola_kotak_infak.php', 'label' => 'Kelola Kotak'],
             ['page' => 'kelola_tugas.php', 'label' => 'Penugasan Amil'],
             ['page' => 'peta_kotak_infak.php', 'label' => 'Peta Navigasi'],
             ['page' => 'riwayat_pengambilan.php', 'label' => 'Riwayat Pengambilan'],
        ]
    ],
     'pengaturan' => [
        'title' => 'Akun & Pengaturan', 'icon' => 'bi-gear-fill',
        'items' => [
             ['page' => 'pengaturan.php', 'label' => 'Pengaturan Web'],
             ['page' => 'ganti_sandi.php', 'label' => 'Ganti Sandi'],
             ['page' => '../logout.php', 'label' => 'Logout'],
        ]
    ],
];
?>
<style>
.submenu {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
}

.submenu-item a {
    padding-left: 3.25rem !important;
    /* pl-13 */
}
</style>

<aside id="sidebar" class="sidebar">
    <div class="p-6 flex items-center justify-between">
        <a href="dashboard.php" class="flex items-center gap-2">
            <img src="../assets/images/logo.png" alt="Logo" class="h-10">
            <span class="font-bold text-xl text-dark-text">Admin</span>
        </a>
        <button id="close-sidebar-btn" class="lg:hidden p-2 rounded-md hover:bg-gray-100">
            <i class="bi bi-x-lg text-xl text-gray-600"></i>
        </button>
    </div>
    <nav class="flex-1 px-4 space-y-1 pb-4">
        <?php foreach($menu_items as $group_key => $group): ?>
        <?php $is_active = is_group_active($group_key, $current_page, $group_map); ?>
        <div>
            <button type="button"
                class="w-full flex items-center gap-3 px-4 py-2.5 rounded-md text-left transition-colors <?php echo $is_active ? 'text-primary-orange' : 'text-gray-600 hover:bg-gray-100' ?>"
                data-group-toggle="<?php echo $group_key; ?>">
                <i class="<?php echo $group['icon']; ?> text-lg"></i>
                <span class="flex-1 font-semibold"><?php echo $group['title']; ?></span>
                <i
                    class="bi bi-chevron-down transition-transform duration-300 <?php echo $is_active ? 'rotate-180' : '' ?>"></i>
            </button>
            <div id="submenu-<?php echo $group_key; ?>"
                class="submenu space-y-1 mt-1 <?php echo $is_active ? 'show' : '' ?>">
                <?php foreach($group['items'] as $item): ?>
                <?php $is_link_active = is_link_active($item['page'], $current_page); ?>
                <div class="submenu-item">
                    <a href="<?php echo $item['page']; ?>"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-md transition-colors text-sm <?php echo $is_link_active ? 'bg-orange-100 text-primary-orange font-bold' : 'text-gray-500 hover:bg-gray-100 hover:text-dark-text font-medium'; ?>">
                        <span class="flex-1"><?php echo $item['label']; ?></span>
                        <?php if(isset($item['count']) && $item['count'] > 0): ?>
                        <span
                            class='px-2 py-0.5 text-xs font-bold text-white bg-red-500 rounded-full'><?php echo $item['count']; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </nav>
</aside>