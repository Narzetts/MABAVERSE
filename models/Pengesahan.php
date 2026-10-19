<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Pengaturan.php';

class Pengesahan {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function checkKelengkapan($user_id) {
        $user = $this->getUserInfo($user_id);
        if (!$user) return null;
        
        $kekurangan = [];
        
        $tugas_kampus = $this->checkTugasKampus($user_id);
        if (!$tugas_kampus['lengkap']) {
            $kekurangan[] = "Tugas Ospek Kampus: {$tugas_kampus['belum_submit']} tugas belum dikumpulkan";
        }
        
        $tugas_fakultas = $this->checkTugasFakultas($user_id, $user['fakultas_id']);
        if (!$tugas_fakultas['lengkap']) {
            $kekurangan[] = "Tugas Ospek Fakultas: {$tugas_fakultas['belum_submit']} tugas belum dikumpulkan";
        }
        
        $tugas_jurusan = $this->checkTugasJurusan($user_id, $user['jurusan_id']);
        if (!$tugas_jurusan['lengkap']) {
            $kekurangan[] = "Tugas Ospek Jurusan: {$tugas_jurusan['belum_submit']} tugas belum dikumpulkan";
        }
        
        $absensi_stats = $this->checkAbsensi($user_id);
        // Hadir di luar radius dan terlambat tetap dihitung hadir (dengan catatan),
        // sama seperti perhitungan statistik di dashboard maba.
        $hadir_efektif = (int)$absensi_stats['hadir'] + (int)$absensi_stats['di_luar_radius'] + (int)$absensi_stats['terlambat'];
        $persentase_kehadiran = $absensi_stats['total_sesi'] > 0 
            ? ($hadir_efektif / $absensi_stats['total_sesi']) * 100 
            : 0;
        
        // Ambang minimal kehadiran dibaca dari tabel pengaturan (bisa diubah admin,
        // default 75% bila baris pengaturan belum ada).
        $min_kehadiran = (float)(new Pengaturan())->get('min_kehadiran_pengesahan', 75);
        
        if ($persentase_kehadiran < $min_kehadiran) {
            $kekurangan[] = 'Kehadiran: ' . round($persentase_kehadiran) . "% (minimal {$min_kehadiran}%)";
        }
        
        $status = empty($kekurangan) ? 'disahkan' : 'belum_memenuhi';
        
        return [
            'user_id' => $user_id,
            'status' => $status,
            'kekurangan' => $kekurangan,
            'detail' => [
                'tugas_kampus' => $tugas_kampus,
                'tugas_fakultas' => $tugas_fakultas,
                'tugas_jurusan' => $tugas_jurusan,
                'absensi' => $absensi_stats,
                'persentase_kehadiran' => round($persentase_kehadiran, 2),
                'min_kehadiran' => $min_kehadiran
            ]
        ];
    }
    
    public function prosesPengesahan($user_id, $diproses_oleh, $override_status = null, $catatan_override = null) {
        $kelengkapan = $this->checkKelengkapan($user_id);
        if (!$kelengkapan) return false;
        
        $status = $override_status ?? $kelengkapan['status'];
        $detail_kekurangan = !empty($kelengkapan['kekurangan']) 
            ? implode("; ", $kelengkapan['kekurangan']) 
            : null;
        
        if ($override_status && $catatan_override) {
            $detail_kekurangan .= " | OVERRIDE: " . $catatan_override;
        }
        
        $check = $this->db->prepare("SELECT id FROM pengesahan WHERE user_id = ?");
        $check->execute([$user_id]);
        
        if ($check->fetch()) {
            $stmt = $this->db->prepare("
                UPDATE pengesahan 
                SET status = ?, detail_kekurangan = ?, diproses_oleh = ?, diproses_at = CURRENT_TIMESTAMP
                WHERE user_id = ?
            ");
            $result = $stmt->execute([$status, $detail_kekurangan, $diproses_oleh, $user_id]);
            
            if ($result) {
                $this->auditLog($diproses_oleh, 'PROSES_PENGESAHAN', 'pengesahan', $user_id, null, json_encode([
                    'status' => $status,
                    'override' => $override_status ? 'ya' : 'tidak'
                ]));
            }
            
            return $result;
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO pengesahan (user_id, status, detail_kekurangan, diproses_oleh, diproses_at)
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
            ");
            $result = $stmt->execute([$user_id, $status, $detail_kekurangan, $diproses_oleh]);
            
            if ($result) {
                $this->auditLog($diproses_oleh, 'PROSES_PENGESAHAN', 'pengesahan', $user_id, null, json_encode([
                    'status' => $status,
                    'override' => $override_status ? 'ya' : 'tidak'
                ]));
            }
            
            return $result;
        }
    }
    
    public function getPengesahanByUser($user_id) {
        $stmt = $this->db->prepare("
            SELECT p.*, u.nama as diproses_oleh_nama
            FROM pengesahan p
            LEFT JOIN users u ON p.diproses_oleh = u.id
            WHERE p.user_id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }
    
    public function getAllPengesahan($filters = []) {
        $sql = "
            SELECT p.*, u.nim, u.nama, u.fakultas_id, u.jurusan_id,
                   f.nama as fakultas_nama, j.nama as jurusan_nama
            FROM pengesahan p
            INNER JOIN users u ON p.user_id = u.id
            LEFT JOIN fakultas f ON u.fakultas_id = f.id
            LEFT JOIN jurusan j ON u.jurusan_id = j.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['fakultas_id'])) {
            $sql .= " AND u.fakultas_id = ?";
            $params[] = $filters['fakultas_id'];
        }
        
        if (!empty($filters['jurusan_id'])) {
            $sql .= " AND u.jurusan_id = ?";
            $params[] = $filters['jurusan_id'];
        }
        
        $sql .= " ORDER BY p.status DESC, u.nama ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function prosesAllPengesahan($diproses_oleh) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'maba'");
        $stmt->execute();
        $maba_list = $stmt->fetchAll();
        
        $processed = 0;
        foreach ($maba_list as $maba) {
            if ($this->prosesPengesahan($maba['id'], $diproses_oleh)) {
                $processed++;
            }
        }
        
        return $processed;
    }
    
    private function getUserInfo($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }
    
    private function checkTugasKampus($user_id) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT t.id) as total_tugas,
                COUNT(DISTINCT pt.tugas_id) as sudah_submit
            FROM tugas t
            INNER JOIN tahap_ospek ta ON t.tahap_id = ta.id
            LEFT JOIN pengumpulan_tugas pt ON t.id = pt.tugas_id AND pt.user_id = ?
            WHERE ta.target_level = 'universitas'
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        
        return [
            'total' => $result['total_tugas'],
            'sudah_submit' => $result['sudah_submit'],
            'belum_submit' => $result['total_tugas'] - $result['sudah_submit'],
            'lengkap' => $result['total_tugas'] == $result['sudah_submit']
        ];
    }
    
    private function checkTugasFakultas($user_id, $fakultas_id) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT t.id) as total_tugas,
                COUNT(DISTINCT pt.tugas_id) as sudah_submit
            FROM tugas t
            INNER JOIN tahap_ospek ta ON t.tahap_id = ta.id
            LEFT JOIN pengumpulan_tugas pt ON t.id = pt.tugas_id AND pt.user_id = ?
            WHERE ta.target_level = 'fakultas'
            AND (t.target_fakultas_id IS NULL OR t.target_fakultas_id = ?)
        ");
        $stmt->execute([$user_id, $fakultas_id]);
        $result = $stmt->fetch();
        
        return [
            'total' => $result['total_tugas'],
            'sudah_submit' => $result['sudah_submit'],
            'belum_submit' => $result['total_tugas'] - $result['sudah_submit'],
            'lengkap' => $result['total_tugas'] == $result['sudah_submit']
        ];
    }
    
    private function checkTugasJurusan($user_id, $jurusan_id) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT t.id) as total_tugas,
                COUNT(DISTINCT pt.tugas_id) as sudah_submit
            FROM tugas t
            INNER JOIN tahap_ospek ta ON t.tahap_id = ta.id
            LEFT JOIN pengumpulan_tugas pt ON t.id = pt.tugas_id AND pt.user_id = ?
            WHERE ta.target_level = 'jurusan'
            AND (t.target_jurusan_id IS NULL OR t.target_jurusan_id = ?)
        ");
        $stmt->execute([$user_id, $jurusan_id]);
        $result = $stmt->fetch();
        
        return [
            'total' => $result['total_tugas'],
            'sudah_submit' => $result['sudah_submit'],
            'belum_submit' => $result['total_tugas'] - $result['sudah_submit'],
            'lengkap' => $result['total_tugas'] == $result['sudah_submit']
        ];
    }
    
    private function checkAbsensi($user_id) {
        // Rincian per status dipakai halaman status pengesahan; kehadiran
        // efektif (hadir + di luar radius + terlambat) dihitung pemanggil.
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT s.id) as total_sesi,
                SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN a.status = 'di_luar_radius' THEN 1 ELSE 0 END) as di_luar_radius,
                SUM(CASE WHEN a.status = 'terlambat' THEN 1 ELSE 0 END) as terlambat
            FROM sesi s
            LEFT JOIN absensi a ON s.id = a.sesi_id AND a.user_id = ?
        ");
        $stmt->execute([$user_id]);
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
