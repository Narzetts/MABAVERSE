<?php
// =========================================================================
// Lingkungan aplikasi
// - 'local'      : pengembangan di XAMPP (default).
// - 'production' : server produksi (wajib HTTPS).
// Nilai khusus server (kredensial database, BASE_URL produksi) diisi di
// config/config.local.php — salin dari config/config.sample.php. File itu
// memuat kredensial sehingga tidak perlu ikut di-commit ke repositori.
// =========================================================================
$config_local = __DIR__ . '/config.local.php';
if (file_exists($config_local)) {
    require_once $config_local;
}

if (!defined('APP_ENV')) {
    define('APP_ENV', 'local');
}

// Default untuk XAMPP lokal; config.local.php menimpa nilai ini di server.
if (!defined('BASE_URL')) { define('BASE_URL', 'http://localhost/MABAVERSE/'); }
if (!defined('DB_HOST')) { define('DB_HOST', 'localhost'); }
if (!defined('DB_USER')) { define('DB_USER', 'root'); }
if (!defined('DB_PASS')) { define('DB_PASS', ''); }
if (!defined('DB_NAME')) { define('DB_NAME', 'mabaverse'); }
if (!defined('DB_CHARSET')) { define('DB_CHARSET', 'utf8mb4'); }

define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// =========================================================================
// Penanganan error
// Produksi: error masuk logs/php-error.log, tidak pernah tampil ke user.
// Lokal: error tampil untuk memudahkan pengembangan, tetap dicatat.
// =========================================================================
$log_dir = __DIR__ . '/../logs';

if (APP_ENV === 'production') {
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0775, true);
    }
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', $log_dir . '/php-error.log');
    error_reporting(E_ALL);

    // Exception tak tertangkap: catat detail ke log, tampilkan halaman generik.
    set_exception_handler(function ($e) {
        error_log('[MABAVERSE] ' . get_class($e) . ': ' . $e->getMessage()
            . ' di ' . $e->getFile() . ':' . $e->getLine());
        if (!headers_sent()) {
            http_response_code(500);
            include __DIR__ . '/../views/errors/500.php';
        } else {
            echo 'Terjadi kesalahan pada server. Muat ulang halaman beberapa saat lagi.';
        }
        exit;
    });

    // Fatal error (parse/compile/memory): user tetap mendapat halaman generik.
    register_shutdown_function(function () {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            if (!headers_sent()) {
                http_response_code(500);
                include __DIR__ . '/../views/errors/500.php';
            }
        }
    });
} else {
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
}

// =========================================================================
// Sesi: cookie httponly selalu aktif; 'secure' wajib di produksi.
// =========================================================================
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'secure' => APP_ENV === 'production',
        'samesite' => 'Lax',
    ]);
    session_start();
}

// CSRF Token
if (PHP_SAPI !== 'cli' && !isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function generateCSRFToken() {
    return $_SESSION['csrf_token'] ?? '';
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Escape output
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Label dan nada warna untuk status domain (absensi, tugas, pengesahan, tahap, prioritas)
function status_meta($status) {
    $map = [
        'hadir'             => ['Hadir', 'ok'],
        'di_luar_radius'    => ['Di luar radius', 'warn'],
        'terlambat'         => ['Terlambat', 'warn'],
        'tidak_hadir'       => ['Tidak hadir', 'danger'],
        'izin'              => ['Izin', 'info'],
        'terkumpul'         => ['Terkumpul', 'info'],
        'revisi'            => ['Perlu revisi', 'warn'],
        'diterima'          => ['Diterima', 'ok'],
        'belum_dikumpulkan' => ['Belum dikumpulkan', 'neutral'],
        'disahkan'          => ['Disahkan', 'ok'],
        'belum_memenuhi'    => ['Belum memenuhi syarat', 'danger'],
        'belum_mulai'       => ['Belum mulai', 'neutral'],
        'berlangsung'       => ['Berlangsung', 'brand'],
        'selesai'           => ['Selesai', 'ok'],
        'normal'            => ['Normal', 'neutral'],
        'penting'           => ['Penting', 'warn'],
        'mendesak'          => ['Mendesak', 'danger'],
    ];

    if (isset($map[$status])) {
        return $map[$status];
    }

    return [ucwords(str_replace('_', ' ', (string)$status)), 'neutral'];
}

// Format tanggal dalam bahasa Indonesia, mis. "18 Okt 2026, 08:00"
function format_tanggal($datetime, $dengan_waktu = true) {
    if (empty($datetime)) {
        return '-';
    }

    $ts = strtotime($datetime);
    if ($ts === false) {
        return '-';
    }

    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $hasil = date('j', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);

    if ($dengan_waktu) {
        $hasil .= ', ' . date('H:i', $ts);
    }

    return $hasil;
}

// Security Headers (berlaku global untuk semua halaman aplikasi)
if (PHP_SAPI !== 'cli') {
    // Nonce untuk satu script inline kecil di header layout (penyimpanan tema).
    define('CSP_NONCE', bin2hex(random_bytes(16)));

    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: same-origin");
    header(
        "Content-Security-Policy: default-src 'self'; " .
        "img-src 'self' data: blob:; " .
        "style-src 'self' 'unsafe-inline'; " .
        "script-src 'self' 'nonce-" . CSP_NONCE . "'; " .
        "connect-src 'self'; " .
        "object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'"
    );

    if (APP_ENV === 'production') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Auto require
require_once __DIR__ . '/database.php';
