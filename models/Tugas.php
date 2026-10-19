<?php
require_once __DIR__ . '/../config/config.php';

class Tugas {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAllTugas($user_id = null, $filters = []) {
        $sql = "
            SELECT t.*, ta.judul as tahap_judul,
                   u.nama as created_by_nama,
                   f.nama as target_fakultas_nama,
                   j.nama as target_jurusan_nama,
                   k.nama as target_kelompok_nama
            FROM tugas t
            INNER JOIN tahap_ospek ta ON t.tahap_id = ta.id
            INNER JOIN users u ON t.created_by = u.id
            LEFT JOIN fakultas f ON t.target_fakultas_id = f.id
            LEFT JOIN jurusan j ON t.target_jurusan_id = j.id
            LEFT JOIN kelompok k ON t.target_kelompok_id = k.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($filters['fakultas_id'])) {
            $sql .= " AND (t.target_fakultas_id IS NULL OR t.target_fakultas_id = ?)";
            $params[] = $filters['fakultas_id'];
        }
        
        if (!empty($filters['jurusan_id'])) {
            $sql .= " AND (t.target_jurusan_id IS NULL OR t.target_jurusan_id = ?)";
            $params[] = $filters['jurusan_id'];
        }
        
        if (!empty($filters['kelompok_id'])) {
            $sql .= " AND (t.target_kelompok_id IS NULL OR t.target_kelompok_id = ?)";
            $params[] = $filters['kelompok_id'];
        }
        
        $sql .= " ORDER BY t.deadline ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getTugasById($id) {
        $stmt = $this->db->prepare("
            SELECT t.*, ta.judul as tahap_judul,
                   f.nama as target_fakultas_nama,
                   j.nama as target_jurusan_nama,
                   k.nama as target_kelompok_nama
            FROM tugas t
            INNER JOIN tahap_ospek ta ON t.tahap_id = ta.id
            LEFT JOIN fakultas f ON t.target_fakultas_id = f.id
            LEFT JOIN jurusan j ON t.target_jurusan_id = j.id
            LEFT JOIN kelompok k ON t.target_kelompok_id = k.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function createTugas($data, $created_by) {
        $stmt = $this->db->prepare("
            INSERT INTO tugas (tahap_id, judul, deskripsi, format_diizinkan, ukuran_max_mb, deadline, 
                              target_fakultas_id, target_jurusan_id, target_kelompok_id, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['tahap_id'],
            $data['judul'],
            $data['deskripsi'],
            $data['format_diizinkan'] ?? 'pdf,jpg,png,doc,docx',
            $data['ukuran_max_mb'] ?? 10,
            $data['deadline'],
            $data['target_fakultas_id'] ?? null,
            $data['target_jurusan_id'] ?? null,
            $data['target_kelompok_id'] ?? null,
            $created_by
        ]);
    }
    
    public function updateTugas($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE tugas 
            SET judul = ?, deskripsi = ?, format_diizinkan = ?, ukuran_max_mb = ?, deadline = ?,
                target_fakultas_id = ?, target_jurusan_id = ?, target_kelompok_id = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['judul'],
            $data['deskripsi'],
            $data['format_diizinkan'],
            $data['ukuran_max_mb'],
            $data['deadline'],
            $data['target_fakultas_id'] ?? null,
            $data['target_jurusan_id'] ?? null,
            $data['target_kelompok_id'] ?? null,
            $id
        ]);
    }
    
    public function deleteTugas($id) {
        $stmt = $this->db->prepare("DELETE FROM tugas WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function submitTugas($tugas_id, $user_id, $file_path) {
        $deadline = $this->db->prepare("SELECT deadline FROM tugas WHERE id = ?");
        $deadline->execute([$tugas_id]);
        $tugas = $deadline->fetch();
        
        $now = date('Y-m-d H:i:s');
        $status = ($now > $tugas['deadline']) ? 'terlambat' : 'terkumpul';
        
        $check = $this->db->prepare("SELECT id FROM pengumpulan_tugas WHERE tugas_id = ? AND user_id = ?");
        $check->execute([$tugas_id, $user_id]);
        
        if ($check->fetch()) {
            $stmt = $this->db->prepare("
                UPDATE pengumpulan_tugas 
                SET file_path = ?, waktu_submit_server = CURRENT_TIMESTAMP, status = ?
                WHERE tugas_id = ? AND user_id = ?
            ");
            return $stmt->execute([$file_path, $status, $tugas_id, $user_id]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO pengumpulan_tugas (tugas_id, user_id, file_path, status)
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([$tugas_id, $user_id, $file_path, $status]);
        }
    }
    
    public function getPengumpulanByUser($user_id, $tugas_id = null) {
        if ($tugas_id) {
            $stmt = $this->db->prepare("
                SELECT pt.*, t.judul as tugas_judul, t.deadline
                FROM pengumpulan_tugas pt
                INNER JOIN tugas t ON pt.tugas_id = t.id
                WHERE pt.user_id = ? AND pt.tugas_id = ?
            ");
            $stmt->execute([$user_id, $tugas_id]);
            return $stmt->fetch();
        } else {
            $stmt = $this->db->prepare("
                SELECT pt.*, t.judul as tugas_judul, t.deadline, ta.judul as tahap_judul
                FROM pengumpulan_tugas pt
                INNER JOIN tugas t ON pt.tugas_id = t.id
                INNER JOIN tahap_ospek ta ON t.tahap_id = ta.id
                WHERE pt.user_id = ?
                ORDER BY pt.waktu_submit_server DESC
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll();
        }
    }
    
    public function getPengumpulanByTugas($tugas_id, $filters = []) {
        $sql = "
            SELECT pt.*, u.nim, u.nama, u.fakultas_id, u.jurusan_id, u.kelompok_id
            FROM pengumpulan_tugas pt
            INNER JOIN users u ON pt.user_id = u.id
            WHERE pt.tugas_id = ?
        ";
        
        $params = [$tugas_id];
        
        if (!empty($filters['kelompok_id'])) {
            $sql .= " AND u.kelompok_id = ?";
            $params[] = $filters['kelompok_id'];
        }
        
        $sql .= " ORDER BY pt.waktu_submit_server DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function updateStatusPengumpulan($id, $status, $catatan, $verifikator_id) {
        $lama = $this->db->prepare("SELECT status FROM pengumpulan_tugas WHERE id = ?");
        $lama->execute([$id]);
        $data_lama = $lama->fetch();
        
        $stmt = $this->db->prepare("
            UPDATE pengumpulan_tugas 
            SET status = ?, catatan_panitia = ?, diverifikasi_oleh = ?
            WHERE id = ?
        ");
        $result = $stmt->execute([$status, $catatan, $verifikator_id, $id]);
        
        if ($result) {
            $this->auditLog($verifikator_id, 'VERIFIKASI_TUGAS', 'pengumpulan_tugas', $id,
                json_encode(['status' => $data_lama['status'] ?? null]),
                json_encode(['status' => $status, 'catatan' => $catatan])
            );
        }
        
        return $result;
    }
    
    public function getStatistikTugas($tugas_id) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_mahasiswa,
                SUM(CASE WHEN pt.id IS NOT NULL THEN 1 ELSE 0 END) as sudah_submit,
                SUM(CASE WHEN pt.status = 'terlambat' THEN 1 ELSE 0 END) as terlambat,
                SUM(CASE WHEN pt.status = 'diterima' THEN 1 ELSE 0 END) as diterima
            FROM users u
            LEFT JOIN pengumpulan_tugas pt ON u.id = pt.user_id AND pt.tugas_id = ?
            WHERE u.role = 'maba'
        ");
        $stmt->execute([$tugas_id]);
        return $stmt->fetch();
    }
    
    private function auditLog($user_id, $aksi, $target_tabel, $target_id, $data_lama = null, $data_baru = null) {
        $stmt = $this->db->prepare("
            INSERT INTO audit_log (user_id, aksi, target_tabel, target_id, data_lama, data_baru, ip_address)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $aksi,
            $target_tabel,
            $target_id,
            $data_lama,
            $data_baru,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
}
