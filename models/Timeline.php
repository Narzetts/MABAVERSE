<?php
require_once __DIR__ . '/../config/config.php';

class Timeline {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAllTahap($user_role = null, $user_fakultas_id = null, $user_jurusan_id = null) {
        $sql = "SELECT * FROM tahap_ospek WHERE 1=1";
        $params = [];
        
        if ($user_role === 'maba') {
            $sql .= " AND (target_level = 'universitas' 
                      OR (target_level = 'fakultas' AND ? IS NOT NULL)
                      OR (target_level = 'jurusan' AND ? IS NOT NULL))";
            $params[] = $user_fakultas_id;
            $params[] = $user_jurusan_id;
        }
        
        $sql .= " ORDER BY urutan ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getTahapById($id) {
        $stmt = $this->db->prepare("SELECT * FROM tahap_ospek WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function createTahap($data) {
        $stmt = $this->db->prepare("
            INSERT INTO tahap_ospek (judul, deskripsi, urutan, tanggal_mulai, tanggal_selesai, target_level, lokasi_text, lampiran)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['judul'],
            $data['deskripsi'],
            $data['urutan'],
            $data['tanggal_mulai'],
            $data['tanggal_selesai'],
            $data['target_level'],
            $data['lokasi_text'] ?? null,
            $data['lampiran'] ?? null
        ]);
    }
    
    public function updateTahap($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE tahap_ospek 
            SET judul = ?, deskripsi = ?, urutan = ?, tanggal_mulai = ?, tanggal_selesai = ?, 
                target_level = ?, lokasi_text = ?, lampiran = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['judul'],
            $data['deskripsi'],
            $data['urutan'],
            $data['tanggal_mulai'],
            $data['tanggal_selesai'],
            $data['target_level'],
            $data['lokasi_text'] ?? null,
            $data['lampiran'] ?? null,
            $id
        ]);
    }
    
    public function deleteTahap($id) {
        $stmt = $this->db->prepare("DELETE FROM tahap_ospek WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function updateStatus() {
        $now = date('Y-m-d H:i:s');
        
        $stmt = $this->db->prepare("
            UPDATE tahap_ospek 
            SET status = CASE 
                WHEN tanggal_mulai > ? THEN 'belum_mulai'
                WHEN tanggal_mulai <= ? AND tanggal_selesai >= ? THEN 'berlangsung'
                WHEN tanggal_selesai < ? THEN 'selesai'
            END
        ");
        
        return $stmt->execute([$now, $now, $now, $now]);
    }
    
    public function getSesiByTahap($tahap_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM sesi 
            WHERE tahap_id = ? 
            ORDER BY tanggal ASC, jam_mulai ASC
        ");
        $stmt->execute([$tahap_id]);
        return $stmt->fetchAll();
    }
    
    public function getSesiById($id) {
        $stmt = $this->db->prepare("SELECT * FROM sesi WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function createSesi($data) {
        $stmt = $this->db->prepare("
            INSERT INTO sesi (tahap_id, judul, tanggal, jam_mulai, jam_selesai, lokasi_text, lokasi_lat, lokasi_lng, radius_meter, pic)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['tahap_id'],
            $data['judul'],
            $data['tanggal'],
            $data['jam_mulai'],
            $data['jam_selesai'],
            $data['lokasi_text'],
            $data['lokasi_lat'] ?? null,
            $data['lokasi_lng'] ?? null,
            $data['radius_meter'] ?? 150,
            $data['pic'] ?? null
        ]);
    }
    
    public function updateSesi($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE sesi 
            SET judul = ?, tanggal = ?, jam_mulai = ?, jam_selesai = ?, 
                lokasi_text = ?, lokasi_lat = ?, lokasi_lng = ?, radius_meter = ?, pic = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['judul'],
            $data['tanggal'],
            $data['jam_mulai'],
            $data['jam_selesai'],
            $data['lokasi_text'],
            $data['lokasi_lat'] ?? null,
            $data['lokasi_lng'] ?? null,
            $data['radius_meter'] ?? 150,
            $data['pic'] ?? null,
            $id
        ]);
    }
    
    public function deleteSesi($id) {
        $stmt = $this->db->prepare("DELETE FROM sesi WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getUpcomingSesi($user_fakultas_id = null, $user_jurusan_id = null, $limit = 5) {
        $now = date('Y-m-d H:i:s');
        
        $sql = "
            SELECT s.*, t.judul as tahap_judul, t.target_level
            FROM sesi s
            INNER JOIN tahap_ospek t ON s.tahap_id = t.id
            WHERE CONCAT(s.tanggal, ' ', s.jam_mulai) >= ?
        ";
        
        $params = [$now];
        
        if ($user_fakultas_id) {
            $sql .= " AND (t.target_level = 'universitas' OR t.target_level = 'fakultas' OR t.target_level = 'jurusan')";
        }
        
        $sql .= " ORDER BY s.tanggal ASC, s.jam_mulai ASC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
