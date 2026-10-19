<?php
// =========================================================================
// Template konfigurasi MABAVERSE untuk server produksi.
//
// Cara pakai:
//   1. Salin file ini menjadi config/config.local.php
//   2. Sesuaikan semua nilai di bawah (terutama kredensial database)
//   3. Jangan commit config.local.php ke repositori — file itu memuat
//      kredensial dan sudah dipisahkan dari config/config.php.
//
// File config/config.php memuat config.local.php secara otomatis bila ada,
// lalu memakai nilai di bawah sebagai pengganti default lokal XAMPP.
// =========================================================================

// 'production' menjalankan mode aman: error ke log (bukan ke layar),
// cookie sesi secure + httponly, header HSTS, dan halaman error generik.
define('APP_ENV', 'production');

// Base URL produksi. Wajib https agar cookie secure dan HSTS konsisten.
define('BASE_URL', 'https://portal.kampus-anda.ac.id/MABAVERSE/');

// Kredensial database produksi.
define('DB_HOST', 'localhost');
define('DB_USER', 'mabaverse_user');
define('DB_PASS', 'ganti-dengan-password-kuat');
define('DB_NAME', 'mabaverse');
define('DB_CHARSET', 'utf8mb4');
