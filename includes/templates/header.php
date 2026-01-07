<?php
// Fallback jika BASE_URL tidak terdefinisi di config.php
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $script_name = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    define('BASE_URL', rtrim($protocol . $host . $script_name, '/'));
}

// --- Logika Terpusat untuk Meta Tags ---
$default_title = 'Lazismu Tulungagung';
$default_description = 'Lembaga Amil Zakat terpercaya di Tulungagung yang menyalurkan zakat, infak, sedekah, dan wakaf.';
$default_keywords = 'zakat, infak, sedekah, wakaf, lazismu, tulungagung, donasi, amal';
$default_og_image = BASE_URL . '/assets/images/og-default.jpg';
$default_url = BASE_URL . $_SERVER['REQUEST_URI'];
$site_name = 'Lazismu Tulungagung';

$final_title = htmlspecialchars((isset($page_title) ? $page_title . ' - ' : '') . $site_name);
$final_description = htmlspecialchars(strip_tags(isset($og_description) ? $og_description : (isset($meta_description) ? $meta_description : $default_description)));
$final_keywords = htmlspecialchars(isset($meta_keywords) ? $meta_keywords : $default_keywords);
$final_url = htmlspecialchars(isset($og_url) ? $og_url : (isset($canonical_url) ? $canonical_url : $default_url));
$final_image = htmlspecialchars(isset($og_image) ? $og_image : $default_og_image);
$final_og_title = htmlspecialchars(isset($og_title) ? $og_title : (isset($page_title) ? $page_title : $default_title));

$current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($base_dir && $base_dir !== '/') {
    $current_path = substr($current_path, strlen($base_dir));
}

// Menentukan status login dan link dasbor
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['admin_id']) || isset($_SESSION['amil_id']);
$dashboard_link = '#';
if (isset($_SESSION['admin_id'])) {
    $dashboard_link = BASE_URL . '/admin/dashboard.php';
} elseif (isset($_SESSION['amil_id'])) {
    $dashboard_link = BASE_URL . '/amil/dashboard.php';
} elseif (isset($_SESSION['user_id'])) {
    $dashboard_link = BASE_URL . '/user/dashboard.php';
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $final_title; ?></title>

    <meta name="description" content="<?php echo $final_description; ?>">
    <meta name="keywords" content="<?php echo $final_keywords; ?>">
    <meta name="author" content="<?php echo htmlspecialchars($site_name); ?>">
    <meta name="theme-color" content="#fb8201">

    <link rel="canonical" href="<?php echo $final_url; ?>">
    <link rel="icon" href="<?php echo BASE_URL; ?>/assets/images/icon.png" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>/assets/images/apple-touch-icon.png">

    <meta property="og:title" content="<?php echo $final_og_title; ?>">
    <meta property="og:description" content="<?php echo $final_description; ?>">
    <meta property="og:image" content="<?php echo $final_image; ?>">
    <meta property="og:url" content="<?php echo $final_url; ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($site_name); ?>">
    <meta property="og:locale" content="id_ID">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'primary-orange': '#fb8201',
                    'secondary-orange': '#ffb253',
                    'dark-text': '#1f2937',
                    'light-bg': '#F9FAFB'
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif']
                }
            }
        }
    }
    </script>

    <style>
    body {
        font-family: 'Inter', sans-serif;
    }

    #preloader-container {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background-color: #F9FAFB;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
    }

    #preloader-container.hiding {
        opacity: 0;
        visibility: hidden;
    }

    .preloader-logo {
        max-width: 120px;
        animation: logo-breathe 2s infinite ease-in-out;
    }

    .loading-bar-container {
        width: 150px;
        height: 4px;
        background-color: #e5e7eb;
        border-radius: 2px;
        margin: 20px auto 0;
        overflow: hidden;
    }

    .loading-bar {
        width: 0;
        height: 100%;
        background-color: #fb8201;
        border-radius: 2px;
        animation: loading-progress 1.5s ease-out forwards;
    }

    @keyframes logo-breathe {

        0%,
        100% {
            transform: scale(1);
            opacity: 0.8;
        }

        50% {
            transform: scale(1.05);
            opacity: 1;
        }
    }

    @keyframes loading-progress {
        from {
            width: 0;
        }

        to {
            width: 100%;
        }
    }
    </style>
</head>

<body class="bg-light-bg">

    <div id="preloader-container">
        <div class="preloader-content text-center">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo Lazismu" class="preloader-logo">
            <div class="loading-bar-container">
                <div class="loading-bar"></div>
            </div>
        </div>
    </div>

    <header id="header"
        class="bg-white/90 backdrop-blur-lg shadow-sm py-3 px-4 md:px-8 flex justify-between items-center sticky top-0 z-40 transition-all duration-300">
        <div class="flex flex-1 items-center gap-4">
            <a href="<?php echo BASE_URL; ?>/" class="flex-shrink-0 flex items-center">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo Lazismu Tulungagung"
                    class="h-10 md:h-12 w-auto">
                <span class="hidden sm:block text-xl md:text-2xl font-bold ml-2 text-dark-text">Teman Amal</span>
            </a>
            <form action="<?php echo BASE_URL; ?>/search" method="GET" class="w-full max-w-xs md:max-w-sm relative">
                <input type="search" name="q"
                    class="w-full py-2 pl-4 pr-10 text-sm bg-gray-100 border border-transparent rounded-full focus:outline-none focus:ring-2 focus:ring-primary-orange"
                    placeholder="Cari..." autocomplete="off">
                <button type="submit"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-primary-orange">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>

        <div class="flex items-center gap-4 ml-4">
            <nav class="hidden lg:flex items-center space-x-6">
                <a href="<?php echo BASE_URL; ?>/"
                    class="<?php echo ($current_path == '/' || $current_path == '/index.php') ? 'text-primary-orange' : 'text-dark-text'; ?> font-semibold hover:text-primary-orange transition">Beranda</a>
                <a href="<?php echo BASE_URL; ?>/program"
                    class="<?php echo (strpos($current_path, '/program') === 0) ? 'text-primary-orange' : 'text-dark-text'; ?> font-semibold hover:text-primary-orange transition">Program</a>
                <a href="<?php echo BASE_URL; ?>/berita"
                    class="<?php echo (strpos($current_path, '/berita') === 0) ? 'text-primary-orange' : 'text-dark-text'; ?> font-semibold hover:text-primary-orange transition">Berita</a>
                <a href="<?php echo BASE_URL; ?>/majalah"
                    class="<?php echo (strpos($current_path, '/majalah') === 0) ? 'text-primary-orange' : 'text-dark-text'; ?> font-semibold hover:text-primary-orange transition">Majalah</a>
                <a href="<?php echo BASE_URL; ?>/personalia"
                    class="<?php echo (strpos($current_path, '/personalia') === 0) ? 'text-primary-orange' : 'text-dark-text'; ?> font-semibold hover:text-primary-orange transition">Personalia</a>
                <a href="<?php echo BASE_URL; ?>/kalkulator_zakat"
                    class="<?php echo (strpos($current_path, '/kalkulator_zakat') === 0) ? 'text-primary-orange' : 'text-dark-text'; ?> font-semibold hover:text-primary-orange transition">Kalkulator
                    Zakat</a>
            </nav>
            <?php if ($is_logged_in): ?>
            <a href="<?php echo $dashboard_link; ?>"
                class="hidden sm:inline-block bg-gray-200 text-dark-text px-5 py-2 rounded-full font-bold hover:bg-gray-300 transition">Dasbor</a>
            <?php else: ?>
            <a href="<?php echo BASE_URL; ?>/login"
                class="hidden sm:inline-block text-dark-text font-bold hover:text-primary-orange transition">Login</a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/donasi"
                class="bg-primary-orange text-white px-5 py-2 rounded-full font-bold hover:bg-orange-600 shadow-md transition transform hover:scale-105">Donasi</a>
            <button id="menu-btn"
                class="lg:hidden text-dark-text p-2 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-orange">
                <i class="bi bi-list text-2xl"></i>
            </button>
        </div>
    </header>

    <div id="mobile-menu"
        class="fixed top-0 right-0 w-full max-w-xs h-full bg-white shadow-lg z-50 transform translate-x-full transition-transform duration-300 ease-in-out">
        <div class="p-5">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold text-primary-orange">Menu</h2>
                <button id="close-menu-btn" class="text-dark-text p-2 rounded-full hover:bg-gray-100"><i
                        class="bi bi-x-lg text-xl"></i></button>
            </div>
            <div class="flex flex-col space-y-5 text-lg">
                <a href="<?php echo BASE_URL; ?>/"
                    class="<?php echo ($current_path == '/' || $current_path == '/index.php') ? 'text-primary-orange' : 'text-dark-text'; ?> font-semibold hover:text-primary-orange">Beranda</a>
                <a href="<?php echo BASE_URL; ?>/program"
                    class="<?php echo (strpos($current_path, '/program') === 0) ? 'text-primary-orange' : 'text-dark-text'; ?> font-semibold hover:text-primary-orange">Program</a>
                <a href="<?php echo BASE_URL; ?>/berita"
                    class="<?php echo (strpos($current_path, '/berita') === 0) ? 'text-primary-orange' : 'text-dark-text'; ?> font-semibold hover:text-primary-orange">Berita</a>
                <a href="<?php echo BASE_URL; ?>/majalah"
                    class="<?php echo (strpos($current_path, '/majalah') === 0) ? 'text-primary-orange' : 'text-dark-text'; ?> font-semibold hover:text-primary-orange">Majalah</a>
                <a href="<?php echo BASE_URL; ?>/personalia"
                    class="<?php echo (strpos($current_path, '/personalia') === 0) ? 'text-primary-orange' : 'text-dark-text'; ?> font-semibold hover:text-primary-orange">Personalia</a>
                <a href="<?php echo BASE_URL; ?>/kalkulator_zakat"
                    class="<?php echo (strpos($current_path, '/kalkulator_zakat') === 0) ? 'text-primary-orange' : 'text-dark-text'; ?> font-semibold hover:text-primary-orange">Kalkulator
                    Zakat</a>
                <hr class="my-3">
                <?php if ($is_logged_in): ?>
                <a href="<?php echo $dashboard_link; ?>"
                    class="bg-gray-100 text-dark-text text-center px-8 py-3 rounded-full font-bold hover:bg-gray-200 transition">Dasbor
                    Saya</a>
                <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/login"
                    class="bg-gray-100 text-dark-text text-center px-8 py-3 rounded-full font-bold hover:bg-gray-200 transition">Login
                    / Daftar</a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/donasi"
                    class="bg-primary-orange text-white text-center px-8 py-3 rounded-full font-bold hover:bg-primary-orange-dark transition shadow-lg">Donasi
                    Sekarang</a>
            </div>
        </div>
    </div>

    <!-- Skrip terpusat dan bersih HANYA di header -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const preloader = document.getElementById('preloader-container');
        if (preloader) {
            window.addEventListener('load', () => {
                preloader.classList.add('hiding');
                preloader.addEventListener('transitionend', () => preloader.remove());
            });
        }

        const menuBtn = document.getElementById('menu-btn');
        const closeMenuBtn = document.getElementById('close-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        const openMenu = () => {
            if (mobileMenu) mobileMenu.classList.remove('translate-x-full');
            document.body.classList.add('menu-open');
        };

        const closeMenu = () => {
            if (mobileMenu) mobileMenu.classList.add('translate-x-full');
            document.body.classList.remove('menu-open');
        };

        if (menuBtn && closeMenuBtn && mobileMenu) {
            menuBtn.addEventListener('click', openMenu);
            closeMenuBtn.addEventListener('click', closeMenu);
        }

        const currentYearEl = document.getElementById('current-year');
        if (currentYearEl) {
            currentYearEl.textContent = new Date().getFullYear();
        }
    });
    </script>