<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Timeline.php';
require_once __DIR__ . '/../models/Tugas.php';
require_once __DIR__ . '/../models/Absensi.php';
require_once __DIR__ . '/../models/Pengumuman.php';

class PanitiaController {
    
    public function dashboard() {
        User::requireRole('panitia');
        
        $userModel = new User();
        $timelineModel = new Timeline();
        $db = Database::getInstance()->getConnection();
        
        $kelompok_id = $_SESSION['user_kelompok_id'] ?? null;
        
        $kelompok = null;
        if ($kelompok_id) {
            $stmt = $db->prepare("SELECT k.*, j.nama as jurusan_nama FROM kelompok k INNER JOIN jurusan j ON k.jurusan_id = j.id WHERE k.id = ?");
            $stmt->execute([$kelompok_id]);
            $kelompok = $stmt->fetch();
        }
        
        $maba_kelompok = $kelompok_id ? $userModel->getAllMaba(['kelompok_id' => $kelompok_id]) : [];
        $sesi_terdekat = $timelineModel->getUpcomingSesi(null, null, 3);
        
        include_once __DIR__ . '/../views/panitia/dashboard.php';
    }
    
    public function kelompok() {
        User::requireRole('panitia');
        
        $userModel = new User();
        $kelompok_id = $_SESSION['user_kelompok_id'] ?? null;
        
        $maba = $userModel->getAllMaba(['kelompok_id' => $kelompok_id]);
        
        include_once __DIR__ . '/../views/panitia/kelompok.php';
    }
    
    public function absensi() {
        User::requireRole('panitia');
        
        $timelineModel = new Timeline();
        
        $timeline = $timelineModel->getAllTahap();
        
        $sesi_by_tahap = [];
        foreach ($timeline as $tahap) {
            $sesi_by_tahap[$tahap['id']] = $timelineModel->getSesiByTahap($tahap['id']);
        }
        
        include_once __DIR__ . '/../views/panitia/absensi_list.php';
    }
    
    public function detailAbsensi($sesi_id) {
        User::requireRole('panitia');
        
        $timelineModel = new Timeline();
        $absensiModel = new Absensi();
        $userModel = new User();
        $kelompok_id = $_SESSION['user_kelompok_id'] ?? null;
        
        $sesi = $timelineModel->getSesiById($sesi_id);
        
        if (!$sesi) {
            header('Location: ' . BASE_URL . 'panitia/absensi');
            exit;
        }
        
        $absensi = $absensiModel->getAbsensiBySesi($sesi_id, ['kelompok_id' => $kelompok_id]);
        $anggota = $kelompok_id ? $userModel->getAllMaba(['kelompok_id' => $kelompok_id]) : [];
        
        $stats = ['total_anggota' => count($anggota)];
        foreach ($absensi as $row) {
            $stats[$row['status']] = ($stats[$row['status']] ?? 0) + 1;
        }
        
        include_once __DIR__ . '/../views/panitia/detail_absensi.php';
    }
    
    public function verifikasiAbsensi($id) {
        User::requireRole('panitia');
        
        $absensiModel = new Absensi();
        $absensi = $absensiModel->getAbsensiById($id);
        
        if (!$absensi) {
            header('Location: ' . BASE_URL . 'panitia/absensi');
            exit;
        }
        
        $kelompok_id = $_SESSION['user_kelompok_id'] ?? null;
        $userModel = new User();
        $maba = $userModel->getUserById($absensi['user_id']);
        
        if ($maba['kelompok_id'] != $kelompok_id) {
            header('Location: ' . BASE_URL . 'panitia/absensi?error=Bukan kelompok Anda');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/panitia/verifikasi_absensi.php';
                return;
            }
            
            $status = $_POST['status'];
            $catatan = $_POST['catatan'] ?? null;
            
            if ($absensiModel->updateStatusAbsensi($id, $status, $catatan, $_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . 'panitia/absensi/' . $absensi['sesi_id'] . '?success=Status absensi diperbarui');
                exit;
            } else {
                $error = "Gagal memperbarui status absensi.";
            }
        }
        
        include_once __DIR__ . '/../views/panitia/verifikasi_absensi.php';
    }
    
    public function tugas() {
        User::requireRole('panitia');
        
        $tugasModel = new Tugas();
        $tugas_list = $tugasModel->getAllTugas();
        
        include_once __DIR__ . '/../views/panitia/tugas_list.php';
    }
    
    public function detailTugas($tugas_id) {
        User::requireRole('panitia');
        
        $tugasModel = new Tugas();
        $userModel = new User();
        $kelompok_id = $_SESSION['user_kelompok_id'] ?? null;
        
        $tugas = $tugasModel->getTugasById($tugas_id);
        
        if (!$tugas) {
            header('Location: ' . BASE_URL . 'panitia/tugas');
            exit;
        }
        
        $pengumpulan = $tugasModel->getPengumpulanByTugas($tugas_id, ['kelompok_id' => $kelompok_id]);
        $anggota = $kelompok_id ? $userModel->getAllMaba(['kelompok_id' => $kelompok_id]) : [];
        $total_anggota = count($anggota);
        
        include_once __DIR__ . '/../views/panitia/detail_tugas.php';
    }
    
    public function verifikasiTugas($id) {
        User::requireRole('panitia');
        
        $tugasModel = new Tugas();
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT pt.*, u.nim, u.nama, t.judul as tugas_judul, u.kelompok_id
            FROM pengumpulan_tugas pt
            INNER JOIN users u ON pt.user_id = u.id
            INNER JOIN tugas t ON pt.tugas_id = t.id
            WHERE pt.id = ?
        ");
        $stmt->execute([$id]);
        $pengumpulan = $stmt->fetch();
        
        if (!$pengumpulan) {
            header('Location: ' . BASE_URL . 'panitia/tugas');
            exit;
        }
        
        $kelompok_id = $_SESSION['user_kelompok_id'] ?? null;
        if ($pengumpulan['kelompok_id'] != $kelompok_id) {
            header('Location: ' . BASE_URL . 'panitia/tugas?error=Bukan kelompok Anda');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/panitia/verifikasi_tugas.php';
                return;
            }
            
            $status = $_POST['status'];
            $catatan = $_POST['catatan'] ?? null;
            
            if ($tugasModel->updateStatusPengumpulan($id, $status, $catatan, $_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . 'panitia/tugas/' . $pengumpulan['tugas_id'] . '?success=Status tugas diperbarui');
                exit;
            } else {
                $error = "Gagal memperbarui status tugas.";
            }
        }
        
        include_once __DIR__ . '/../views/panitia/verifikasi_tugas.php';
    }
    
    public function pengumuman() {
        User::requireRole('panitia');
        
        $pengumumanModel = new Pengumuman();
        $pengumuman = $pengumumanModel->getAllPengumuman();
        
        include_once __DIR__ . '/../views/panitia/pengumuman.php';
    }
    
    public function tambahPengumuman() {
        User::requireRole('panitia');
        
        $kelompok_id = $_SESSION['user_kelompok_id'] ?? null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/panitia/pengumuman_form.php';
                return;
            }
            
            $pengumumanModel = new Pengumuman();
            $data = [
                'judul' => $_POST['judul'],
                'isi' => $_POST['isi'],
                'prioritas' => $_POST['prioritas'] ?? 'normal',
                'target_level' => 'kelompok',
                'target_id' => $kelompok_id
            ];
            
            if ($pengumumanModel->createPengumuman($data, $_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . 'panitia/pengumuman?success=Pengumuman berhasil ditambahkan');
                exit;
            } else {
                $error = "Gagal menambahkan pengumuman.";
            }
        }
        
        include_once __DIR__ . '/../views/panitia/pengumuman_form.php';
    }
}
