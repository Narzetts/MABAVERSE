<?php
require_once __DIR__ . '/../config/config.php';

// Pengaturan aplikasi berbasis key-value (tabel `pengaturan`).
// Contoh pemakaian: ambang minimal kehadiran untuk pengesahan otomatis.
class Pengaturan {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Baca satu nilai pengaturan; kembalikan $default bila belum diisi
    // atau tabel belum ada (mis. instalasi lama sebelum upgrade database).
    public function get($nama, $default = null) {
        try {
            $stmt = $this->db->prepare("SELECT nilai FROM pengaturan WHERE nama = ?");
            $stmt->execute([$nama]);
            $nilai = $stmt->fetchColumn();
            return $nilai === false ? $default : $nilai;
        } catch (PDOException $e) {
            return $default;
        }
    }
    
    // Simpan (insert atau update) satu nilai pengaturan.
    public function set($nama, $nilai, $oleh_user_id = null) {
        $lama = $this->get($nama);
        
        $stmt = $this->db->prepare("
            INSERT INTO pengaturan (nama, nilai) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)
        ");
        $result = $stmt->execute([$nama, $nilai]);
        
        if ($result && $oleh_user_id !== null) {
            $this->auditLog($oleh_user_id, 'UPDATE_PENGATURAN', $nama, $lama, $nilai);
        }
        
        return $result;
    }
    
    private function auditLog($user_id, $aksi, $nama_pengaturan, $data_lama, $data_baru) {
        $stmt = $this->db->prepare("
            INSERT INTO audit_log (user_id, aksi, target_tabel, target_id, data_lama, data_baru, ip_address)
            VALUES (?, ?, 'pengaturan', 0, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $aksi,
            json_encode(['nama' => $nama_pengaturan, 'nilai' => $data_lama]),
            json_encode(['nama' => $nama_pengaturan, 'nilai' => $data_baru]),
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
}
