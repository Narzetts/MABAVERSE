<?php
require_once __DIR__ . '/../config/config.php';

class Absensi {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function submitAbsensi($data) {
        $sesi = $this->getSesiInfo($data['sesi_id']);
        if (!$sesi) return false;
        
        $jarak = $this->calculateDistance(
            $data['lat'], 
            $data['lng'], 
            $sesi['lokasi_lat'], 
            $sesi['lokasi_lng']
        );
        
        $status = 'hadir';
        if ($jarak > $sesi['radius_meter']) {
            $status = 'di_luar_radius';
        }
        
        $now = date('Y-m-d H:i:s');
        $batas_waktu = $sesi['tanggal'] . ' ' . $sesi['jam_selesai'];
        if ($now > $batas_waktu) {
            $status = 'terlambat';
        }
        
        $check = $this->db->prepare("SELECT id FROM absensi WHERE sesi_id = ? AND user_id = ?");
        $check->execute([$data['sesi_id'], $data['user_id']]);
        
        if ($check->fetch()) {
            $stmt = $this->db->prepare("
                UPDATE absensi 
                SET foto_path = ?, lat = ?, lng = ?, jarak_dari_lokasi_meter = ?, 
                    waktu_submit_server = CURRENT_TIMESTAMP, status = ?
                WHERE sesi_id = ? AND user_id = ?
            ");
            return $stmt->execute([
                $data['foto_path'],
                $data['lat'],
                $data['lng'],
                $jarak,
                $status,
                $data['sesi_id'],
                $data['user_id']
            ]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO absensi (sesi_id, user_id, foto_path, lat, lng, jarak_dari_lokasi_meter, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $data['sesi_id'],
                $data['user_id'],
                $data['foto_path'],
                $data['lat'],
                $data['lng'],
                $jarak,
                $status
            ]);
        }
    }
    
    public function getAbsensiByUser($user_id) {
        $stmt = $this->db->prepare("
            SELECT a.*, s.judul as sesi_judul, s.tanggal, s.jam_mulai, s.jam_selesai,
                   t.judul as tahap_judul
            FROM absensi a
            INNER JOIN sesi s ON a.sesi_id = s.id
            INNER JOIN tahap_ospek t ON s.tahap_id = t.id
            WHERE a.user_id = ?
            ORDER BY a.waktu_submit_server DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    public function getAbsensiBySesi($sesi_id, $filters = []) {
        $sql = "
            SELECT a.*, u.nim, u.nama, u.kelompok_id, k.nama as kelompok_nama
            FROM absensi a
            INNER JOIN users u ON a.user_id = u.id
            LEFT JOIN kelompok k ON u.kelompok_id = k.id
            WHERE a.sesi_id = ?
        ";
        
        $params = [$sesi_id];
        
        if (!empty($filters['kelompok_id'])) {
            $sql .= " AND u.kelompok_id = ?";
            $params[] = $filters['kelompok_id'];
        }
        
        if (!empty($filters['fakultas_id'])) {
            $sql .= " AND u.fakultas_id = ?";
            $params[] = $filters['fakultas_id'];
        }
        
        if (!empty($filters['jurusan_id'])) {
            $sql .= " AND u.jurusan_id = ?";
            $params[] = $filters['jurusan_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY a.waktu_submit_server DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getAbsensiById($id) {
        $stmt = $this->db->prepare("
            SELECT a.*, s.judul as sesi_judul, s.lokasi_text, s.radius_meter,
                   u.nim, u.nama
            FROM absensi a
            INNER JOIN sesi s ON a.sesi_id = s.id
            INNER JOIN users u ON a.user_id = u.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function updateStatusAbsensi($id, $status, $catatan, $verifikator_id) {
        $old_data = $this->getAbsensiById($id);
        
        $stmt = $this->db->prepare("
            UPDATE absensi 
            SET status = ?, catatan = ?, diverifikasi_oleh = ?
            WHERE id = ?
        ");
        
        $result = $stmt->execute([$status, $catatan, $verifikator_id, $id]);
        
        if ($result) {
            $this->auditLog(
                $verifikator_id, 
                'UPDATE_STATUS_ABSENSI', 
                'absensi', 
                $id,
                json_encode(['status' => $old_data['status']]),
                json_encode(['status' => $status, 'catatan' => $catatan])
            );
        }
        
        return $result;
    }
    
    public function getStatistikAbsensi($sesi_id = null, $user_id = null) {
        if ($sesi_id) {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(DISTINCT u.id) as total_mahasiswa,
                    SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN a.status = 'di_luar_radius' THEN 1 ELSE 0 END) as di_luar_radius,
                    SUM(CASE WHEN a.status = 'terlambat' THEN 1 ELSE 0 END) as terlambat,
                    SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN a.id IS NULL THEN 1 ELSE 0 END) as tidak_hadir
                FROM users u
                LEFT JOIN absensi a ON u.id = a.user_id AND a.sesi_id = ?
                WHERE u.role = 'maba'
            ");
            $stmt->execute([$sesi_id]);
            return $stmt->fetch();
        } elseif ($user_id) {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(s.id) as total_sesi,
                    SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN a.status = 'di_luar_radius' THEN 1 ELSE 0 END) as di_luar_radius,
                    SUM(CASE WHEN a.status = 'terlambat' THEN 1 ELSE 0 END) as terlambat,
                    SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN a.id IS NULL THEN 1 ELSE 0 END) as tidak_hadir
                FROM sesi s
                LEFT JOIN absensi a ON s.id = a.sesi_id AND a.user_id = ?
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetch();
        }
        return null;
    }
    
    public function checkAbsensiExists($sesi_id, $user_id) {
        $stmt = $this->db->prepare("SELECT id FROM absensi WHERE sesi_id = ? AND user_id = ?");
        $stmt->execute([$sesi_id, $user_id]);
        return $stmt->fetch() !== false;
    }
    
    public function getAbsensiByUserSesi($user_id, $sesi_id) {
        $stmt = $this->db->prepare("SELECT * FROM absensi WHERE user_id = ? AND sesi_id = ?");
        $stmt->execute([$user_id, $sesi_id]);
        return $stmt->fetch();
    }
    
    private function getSesiInfo($sesi_id) {
        $stmt = $this->db->prepare("SELECT * FROM sesi WHERE id = ?");
        $stmt->execute([$sesi_id]);
        return $stmt->fetch();
    }
    
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        if (is_null($lat2) || is_null($lon2)) {
            return 0;
        }
        
        $earthRadius = 6371000;
        
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
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
