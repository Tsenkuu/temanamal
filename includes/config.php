<?php
// --- DEBUGGING MODE ---
// Aktifkan jika diperlukan debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai sesi (jika belum aktif)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- DETEKSI LINGKUNGAN (LOCAL vs SERVER) ---
// Lingkungan lokal HANYA 'localhost' atau '127.0.0.1'.
// Semua nama domain atau IP lain dianggap sebagai server/produksi.
$isLocal = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']);

define('DB_SERVER',   'localhost');
define('DB_USERNAME', $isLocal ? 'root' : 'fzqcqgbi_data'); // Username lokal vs server
define('DB_PASSWORD', $isLocal ? '' : 'pyp@123rawy');       // Password lokal vs server
define('DB_NAME',     $isLocal ? 'lazismu2' : 'fzqcqgbi_cb');   // Nama database lokal vs server

// --- KONEKSI DATABASE ---
// Menggunakan error handling dengan try-catch untuk mysqli
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    $mysqli->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // Tampilkan pesan error yang lebih ramah di produksi
    // dan log error yang detail untuk developer.
    error_log("Database Connection Error: " . $e->getMessage());
    die("Terjadi masalah koneksi ke database. Silakan coba lagi nanti.");
}


// --- BASE URL OTOMATIS ---
$protocol = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
) ? "https" : "http";

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$path_to_includes = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', __DIR__));
$base_path = str_replace('/includes', '', $path_to_includes);
$base_url = rtrim("$protocol://$host" . ($base_path === '/' ? '' : $base_path), '/');
define('BASE_URL', $base_url);

// --- TIMEZONE ---
date_default_timezone_set('Asia/Jakarta');

// --- KONFIGURASI WHATSAPP ---
$result_wa = $mysqli->query("SELECT nilai_pengaturan FROM pengaturan WHERE nama_pengaturan = 'admin_wa_number'");
$admin_wa_number_from_db = $result_wa && $result_wa->num_rows > 0
    ? $result_wa->fetch_assoc()['nilai_pengaturan']
    : '6285806917113'; // Nomor default jika query gagal

define('ADMIN_WA_NUMBER', $admin_wa_number_from_db);
// Local wa-api service (default). Sesuaikan jika Anda menjalankan di host/port lain.
define('API_WA_BASE_URL', 'https://apiwa.invtulungagung.my.id'); // Base URL server Node.js (wa-api)
define('API_WA_URL',      API_WA_BASE_URL . '/kirim-pesan');
define('API_KALKULATOR_URL', API_WA_BASE_URL . '/kalkulator-details');
define('API_WA_RESET_URL',  API_WA_BASE_URL . '/reset-sesi');
// Pastikan nilai token ini sama dengan `API_WA_TOKEN` di file .env pada folder wa-api
define('API_WA_TOKEN',      'RAHASIAPIXELYOGA');

/**
 * Fungsi helper untuk melakukan panggilan ke API WhatsApp Node.js.
 * @param string $endpoint Endpoint yang dituju (misal: '/kirim-pesan').
 * @param string $method Metode HTTP ('GET' atau 'POST').
 * @param array $data Data yang akan dikirim (untuk metode POST).
 * @return array Hasil response dari API dalam bentuk array.
 */
function callWhatsappAPI($endpoint, $method = 'POST', $data = []) {
    // Selalu tambahkan token ke data yang dikirim
    $data['token'] = API_WA_TOKEN;

    $url = API_WA_BASE_URL . $endpoint;
    // Jika metode GET, tambahkan parameter query dari $data (termasuk token)
    if (strtoupper($method) === 'GET' && !empty($data)) {
        $query = http_build_query($data);
        $url .= (strpos($url, '?') === false ? '?' : '&') . $query;
    }
    $ch = curl_init($url);

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10, // Waktu tunggu total
        CURLOPT_CONNECTTIMEOUT => 5,  // Waktu tunggu koneksi
    ];

    if (strtoupper($method) === 'POST') {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
        $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
    }

    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        // Jika cURL gagal, kembalikan response error
        return ['success' => false, 'message' => 'Curl Error: ' . $error];
    }

    return json_decode($response, true) ?: ['success' => false, 'message' => 'Invalid JSON response from API.'];
}

/**
 * Mengambil nomor WA admin dari server Node.js.
 * @return string|null Nomor WA admin atau null jika gagal.
 */
function getAdminWhatsappNumber() {
    // Gunakan static variable untuk caching agar tidak memanggil API berulang kali dalam satu request
    static $admin_number = null;
    if ($admin_number !== null) {
        return $admin_number;
    }

    $response = callWhatsappAPI('/ambil-pengaturan', 'GET');
    if (isset($response['success']) && $response['success']) {
        $admin_number = $response['data']['admin_wa_number'] ?? null;
        return $admin_number;
    }
    return null; // Kembalikan null jika gagal mengambil
}

/**
 * Kirim notifikasi WhatsApp via API.
 * @param string $nomor Nomor tujuan (format 08xx atau 628xx).
 * @param string $pesan Isi pesan.
 * @return array Response dari API.
 */
function kirimNotifikasiWA($nomor, $pesan) {
    return callWhatsappAPI('/kirim-pesan', 'POST', [
        'nomor' => $nomor,
        'pesan' => $pesan,
    ]);
}


/**
 * Fungsi untuk melacak pengunjung unik harian dan menambah jumlah view.
 */
function track_visitor($mysqli, $page_type, $page_id = null) {
    // Jangan lacak jika yang mengakses adalah admin atau amil yang sedang login
    if (isset($_SESSION['admin_id']) || isset($_SESSION['amil_id'])) {
        return;
    }

    $ip_address = $_SERVER['REMOTE_ADDR'];
    $today = date("Y-m-d");

    // Query untuk mencatat kunjungan unik. ON DUPLICATE KEY UPDATE memastikan
    // satu IP hanya dihitung sekali per hari untuk halaman yang sama.
    $stmt_insert = $mysqli->prepare(
        "INSERT INTO visitors (page_type, page_id, ip_address, visit_date) VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE visit_count = visit_count + 0" // Tidak melakukan apa-apa jika duplikat
    );
    // Memastikan page_id adalah integer atau null
    $page_id_val = $page_id ? (int)$page_id : null;
    $stmt_insert->bind_param("siss", $page_type, $page_id_val, $ip_address, $today);
    $stmt_insert->execute();

    // affected_rows akan > 0 hanya jika baris BARU berhasil dimasukkan (kunjungan unik pertama hari ini)
    if ($stmt_insert->affected_rows > 0) {
        // Hanya update jumlah view jika ini adalah kunjungan unik pertama hari ini
        if ($page_type === 'berita' && $page_id) {
            $mysqli->query("UPDATE berita SET views = views + 1 WHERE id = " . (int)$page_id);
        } elseif ($page_type === 'program' && $page_id) {
            $mysqli->query("UPDATE program SET views = views + 1 WHERE id = " . (int)$page_id);
        }
    }
    $stmt_insert->close();
}