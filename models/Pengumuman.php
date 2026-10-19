<?php
require_once __DIR__ . '/../config/config.php';

class Pengumuman {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAllPengumuman($user_id = null, $user_fakultas_id = null, $user_jurusan_id = null, $user_kelompok_id = null) {
        $sql = "
            SELECT p.*, u.nama as created_by_nama,
                   (SELECT COUNT(*) FROM pengumuman_dibaca WHERE pengumuman_id = p.id AND user_id = ?) as is_read
            FROM pengumuman p
            INNER JOIN users u ON p.created_by = u.id
            WHERE (p.target_level = 'semua'
        ";
        
        $params = [$user_id];
        
        if ($user_fakultas_id) {
            $sql .= " OR (p.target_level = 'fakultas' AND p.target_id = ?)";
            $params[] = $user_fakultas_id;
        }
        
        if ($user_jurusan_id) {
            $sql .= " OR (p.target_level = 'jurusan' AND p.target_id = ?)";
            $params[] = $user_jurusan_id;
        }
        
        if ($user_kelompok_id) {
            $sql .= " OR (p.target_level = 'kelompok' AND p.target_id = ?)";
            $params[] = $user_kelompok_id;
        }
        
        $sql .= ") ORDER BY p.prioritas DESC, p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getPengumumanById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*, u.nama as created_by_nama
            FROM pengumuman p
            INNER JOIN users u ON p.created_by = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function createPengumuman($data, $created_by) {
        $stmt = $this->db->prepare("
            INSERT INTO pengumuman (judul, isi, prioritas, target_level, target_id, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['judul'],
            $data['isi'],
            $data['prioritas'] ?? 'normal',
            $data['target_level'] ?? 'semua',
            $data['target_id'] ?? null,
            $created_by
        ]);
    }
    
    public function updatePengumuman($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE pengumuman 
            SET judul = ?, isi = ?, prioritas = ?, target_level = ?, target_id = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['judul'],
            $data['isi'],
            $data['prioritas'],
            $data['target_level'],
            $data['target_id'] ?? null,
            $id
        ]);
    }
    
    public function deletePengumuman($id) {
        $stmt = $this->db->prepare("DELETE FROM pengumuman WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function markAsRead($pengumuman_id, $user_id) {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO pengumuman_dibaca (pengumuman_id, user_id)
            VALUES (?, ?)
        ");
        return $stmt->execute([$pengumuman_id, $user_id]);
    }
    
    public function getUnreadCount($user_id, $user_fakultas_id = null, $user_jurusan_id = null, $user_kelompok_id = null) {
        $sql = "
            SELECT COUNT(*) as unread_count
            FROM pengumuman p
            LEFT JOIN pengumuman_dibaca pd ON p.id = pd.pengumuman_id AND pd.user_id = ?
            WHERE pd.id IS NULL
            AND (p.target_level = 'semua'
        ";
        
        $params = [$user_id];
        
        if ($user_fakultas_id) {
            $sql .= " OR (p.target_level = 'fakultas' AND p.target_id = ?)";
            $params[] = $user_fakultas_id;
        }
        
        if ($user_jurusan_id) {
            $sql .= " OR (p.target_level = 'jurusan' AND p.target_id = ?)";
            $params[] = $user_jurusan_id;
        }
        
        if ($user_kelompok_id) {
            $sql .= " OR (p.target_level = 'kelompok' AND p.target_id = ?)";
            $params[] = $user_kelompok_id;
        }
        
        $sql .= ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['unread_count'];
    }
}
