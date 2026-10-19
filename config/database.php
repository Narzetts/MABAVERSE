<?php
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Detail teknis hanya masuk log; user tidak pernah melihatnya di produksi.
            error_log('[MABAVERSE] Koneksi database gagal: ' . $e->getMessage());

            if (defined('APP_ENV') && APP_ENV === 'production') {
                throw new RuntimeException('Layanan database sedang tidak tersedia.');
            }

            die("Koneksi database gagal: " . $e->getMessage()
                . " — periksa DB_HOST/DB_USER/DB_PASS di config/config.local.php dan pastikan MySQL berjalan.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}
