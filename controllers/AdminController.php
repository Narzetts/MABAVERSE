<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Timeline.php';
require_once __DIR__ . '/../models/Tugas.php';
require_once __DIR__ . '/../models/Absensi.php';
require_once __DIR__ . '/../models/Pengesahan.php';
require_once __DIR__ . '/../models/Pengumuman.php';
require_once __DIR__ . '/../models/Pengaturan.php';
require_once __DIR__ . '/../utils/upload.php';

class AdminController {
    
    public function dashboard() {
        User::requireRole(['admin', 'superadmin']);
        
        $db = Database::getInstance()->getConnection();
        $timelineModel = new Timeline();
        
        $stats = [
            'total_maba' => $this->countUsers('maba'),
            'total_panitia' => $this->countUsers('panitia'),
            'total_admin' => $this->countUsers('admin') + $this->countUsers('superadmin'),
            'total_tugas' => $this->countRows('tugas'),
            'total_sesi' => $this->countRows('sesi'),
            'total_pengumuman' => $this->countRows('pengumuman'),
        ];
        
        // Kehadiran keseluruhan: absensi masuk dibanding (sesi yang sudah mulai x jumlah maba aktif)
        $stmt = $db->query("
            SELECT
                (SELECT COUNT(*) FROM sesi WHERE CONCAT(tanggal, ' ', jam_mulai) <= NOW()) AS sesi_berjalan,
                (SELECT COUNT(*) FROM users WHERE role = 'maba' AND is_active = 1) AS total_maba_aktif,
                (SELECT COUNT(*) FROM absensi WHERE status IN ('hadir', 'di_luar_radius', 'terlambat')) AS absensi_masuk
        ");
        $agg = $stmt->fetch();
        $kapasitas = (int)$agg['sesi_berjalan'] * (int)$agg['total_maba_aktif'];
        $stats['persentase_kehadiran'] = $kapasitas > 0
            ? round((int)$agg['absensi_masuk'] / $kapasitas * 100, 1)
            : null;
        
        // Tren kehadiran sesi-sesi yang sudah dimulai (grafik bar sederhana)
        $stmt = $db->query("
            SELECT s.id, s.judul, s.tanggal,
                   SUM(CASE WHEN a.status IN ('hadir', 'di_luar_radius', 'terlambat') THEN 1 ELSE 0 END) AS hadir,
                   (SELECT COUNT(*) FROM users u WHERE u.role = 'maba' AND u.is_active = 1) AS target
            FROM sesi s
            LEFT JOIN absensi a ON a.sesi_id = s.id
            WHERE CONCAT(s.tanggal, ' ', s.jam_mulai) <= NOW()
            GROUP BY s.id
            ORDER BY s.tanggal DESC, s.jam_mulai DESC
            LIMIT 6
        ");
        $tren_kehadiran = array_reverse($stmt->fetchAll());
        
        $sesi_terdekat = $timelineModel->getUpcomingSesi(null, null, 5);
        
        include_once __DIR__ . '/../views/admin/dashboard.php';
    }
    
    private function countRows($table) {
        $allowed = ['tugas', 'sesi', 'pengumuman', 'tahap_ospek'];
        if (!in_array($table, $allowed, true)) {
            return 0;
        }
        $db = Database::getInstance()->getConnection();
        return (int)$db->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
    }
    
    // Ubah nilai target gabungan dari form tugas ("semua", "f:2", "j:5", "k:1") jadi kolom target
    private function parseTarget() {
        $fakultas = null;
        $jurusan = null;
        $kelompok = null;
        
        $target = $_POST['target'] ?? '';
        
        if (strpos($target, ':') !== false) {
            list($tipe, $id) = explode(':', $target, 2);
            if (is_numeric($id)) {
                switch ($tipe) {
                    case 'f':
                        $fakultas = (int)$id;
                        break;
                    case 'j':
                        $jurusan = (int)$id;
                        break;
                    case 'k':
                        $kelompok = (int)$id;
                        break;
                }
            }
        }
        
        return [$fakultas, $jurusan, $kelompok];
    }
    
    // Ubah nilai target gabungan jadi level + id untuk pengumuman
    private function parsePengumumanTarget() {
        $level = 'semua';
        $id = null;
        
        $target = $_POST['target'] ?? '';
        
        if (strpos($target, ':') !== false) {
            list($tipe, $target_id) = explode(':', $target, 2);
            $map = ['f' => 'fakultas', 'j' => 'jurusan', 'k' => 'kelompok'];
            if (isset($map[$tipe]) && is_numeric($target_id)) {
                $level = $map[$tipe];
                $id = (int)$target_id;
            }
        }
        
        return [$level, $id];
    }
    
    private function countUsers($role) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE role = ?");
        $stmt->execute([$role]);
        return $stmt->fetch()['count'];
    }
    
    public function timeline() {
        User::requireRole(['admin', 'superadmin']);
        
        $timelineModel = new Timeline();
        $timelineModel->updateStatus();
        $timeline = $timelineModel->getAllTahap();
        
        include_once __DIR__ . '/../views/admin/timeline.php';
    }
    
    public function tambahTahap() {
        User::requireRole(['admin', 'superadmin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/admin/timeline_form.php';
                return;
            }
            
            $timelineModel = new Timeline();
            $data = [
                'judul' => $_POST['judul'],
                'deskripsi' => $_POST['deskripsi'],
                'urutan' => $_POST['urutan'],
                'tanggal_mulai' => $_POST['tanggal_mulai'],
                'tanggal_selesai' => $_POST['tanggal_selesai'],
                'target_level' => $_POST['target_level'],
                'lokasi_text' => $_POST['lokasi_text'] ?? null
            ];
            
            if ($timelineModel->createTahap($data)) {
                header('Location: ' . BASE_URL . 'admin/timeline?success=Tahap berhasil ditambahkan');
                exit;
            } else {
                $error = "Gagal menambahkan tahap.";
            }
        }
        
        include_once __DIR__ . '/../views/admin/timeline_form.php';
    }
    
    public function editTahap($id) {
        User::requireRole(['admin', 'superadmin']);
        
        $timelineModel = new Timeline();
        $tahap = $timelineModel->getTahapById($id);
        
        if (!$tahap) {
            header('Location: ' . BASE_URL . 'admin/timeline');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/admin/timeline_form.php';
                return;
            }
            
            $data = [
                'judul' => $_POST['judul'],
                'deskripsi' => $_POST['deskripsi'],
                'urutan' => $_POST['urutan'],
                'tanggal_mulai' => $_POST['tanggal_mulai'],
                'tanggal_selesai' => $_POST['tanggal_selesai'],
                'target_level' => $_POST['target_level'],
                'lokasi_text' => $_POST['lokasi_text'] ?? null
            ];
            
            if ($timelineModel->updateTahap($id, $data)) {
                header('Location: ' . BASE_URL . 'admin/timeline?success=Tahap berhasil diperbarui');
                exit;
            } else {
                $error = "Gagal memperbarui tahap.";
            }
        }
        
        include_once __DIR__ . '/../views/admin/timeline_form.php';
    }
    
    public function hapusTahap($id) {
        User::requireRole(['admin', 'superadmin']);
        
        $timelineModel = new Timeline();
        if ($timelineModel->deleteTahap($id)) {
            header('Location: ' . BASE_URL . 'admin/timeline?success=Tahap berhasil dihapus');
        } else {
            header('Location: ' . BASE_URL . 'admin/timeline?error=Gagal menghapus tahap');
        }
        exit;
    }
    
    public function sesi($tahap_id = null) {
        User::requireRole(['admin', 'superadmin']);
        
        $timelineModel = new Timeline();
        
        if ($tahap_id) {
            $tahap = $timelineModel->getTahapById($tahap_id);
            $sesi_list = $timelineModel->getSesiByTahap($tahap_id);
            include_once __DIR__ . '/../views/admin/sesi.php';
        } else {
            $timeline = $timelineModel->getAllTahap();
            include_once __DIR__ . '/../views/admin/sesi_list.php';
        }
    }
    
    public function tambahSesi($tahap_id) {
        User::requireRole(['admin', 'superadmin']);
        
        $timelineModel = new Timeline();
        $tahap = $timelineModel->getTahapById($tahap_id);
        
        if (!$tahap) {
            header('Location: ' . BASE_URL . 'admin/sesi');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/admin/sesi_form.php';
                return;
            }
            
            $data = [
                'tahap_id' => $tahap_id,
                'judul' => $_POST['judul'],
                'tanggal' => $_POST['tanggal'],
                'jam_mulai' => $_POST['jam_mulai'],
                'jam_selesai' => $_POST['jam_selesai'],
                'lokasi_text' => $_POST['lokasi_text'],
                'lokasi_lat' => $_POST['lokasi_lat'] ?? null,
                'lokasi_lng' => $_POST['lokasi_lng'] ?? null,
                'radius_meter' => $_POST['radius_meter'] ?? 150,
                'pic' => $_POST['pic'] ?? null
            ];
            
            if ($timelineModel->createSesi($data)) {
                header('Location: ' . BASE_URL . 'admin/sesi/' . $tahap_id . '?success=Sesi berhasil ditambahkan');
                exit;
            } else {
                $error = "Gagal menambahkan sesi.";
            }
        }
        
        include_once __DIR__ . '/../views/admin/sesi_form.php';
    }
    
    public function editSesi($id) {
        User::requireRole(['admin', 'superadmin']);
        
        $timelineModel = new Timeline();
        $sesi = $timelineModel->getSesiById($id);
        
        if (!$sesi) {
            header('Location: ' . BASE_URL . 'admin/sesi');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/admin/sesi_form.php';
                return;
            }
            
            $data = [
                'judul' => $_POST['judul'],
                'tanggal' => $_POST['tanggal'],
                'jam_mulai' => $_POST['jam_mulai'],
                'jam_selesai' => $_POST['jam_selesai'],
                'lokasi_text' => $_POST['lokasi_text'],
                'lokasi_lat' => $_POST['lokasi_lat'] ?? null,
                'lokasi_lng' => $_POST['lokasi_lng'] ?? null,
                'radius_meter' => $_POST['radius_meter'] ?? 150,
                'pic' => $_POST['pic'] ?? null
            ];
            
            if ($timelineModel->updateSesi($id, $data)) {
                header('Location: ' . BASE_URL . 'admin/sesi/' . $sesi['tahap_id'] . '?success=Sesi berhasil diperbarui');
                exit;
            } else {
                $error = "Gagal memperbarui sesi.";
            }
        }
        
        include_once __DIR__ . '/../views/admin/sesi_form.php';
    }
    
    public function hapusSesi($id) {
        User::requireRole(['admin', 'superadmin']);
        
        $timelineModel = new Timeline();
        $sesi = $timelineModel->getSesiById($id);
        
        if ($timelineModel->deleteSesi($id)) {
            header('Location: ' . BASE_URL . 'admin/sesi/' . $sesi['tahap_id'] . '?success=Sesi berhasil dihapus');
        } else {
            header('Location: ' . BASE_URL . 'admin/sesi?error=Gagal menghapus sesi');
        }
        exit;
    }
    
    public function tugas() {
        User::requireRole(['admin', 'superadmin']);
        
        $tugasModel = new Tugas();
        $tugas_list = $tugasModel->getAllTugas();
        
        include_once __DIR__ . '/../views/admin/tugas.php';
    }
    
    public function tambahTugas() {
        User::requireRole(['admin', 'superadmin']);
        
        $timelineModel = new Timeline();
        $timeline = $timelineModel->getAllTahap();
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM fakultas ORDER BY nama");
        $fakultas = $stmt->fetchAll();
        
        $stmt = $db->query("SELECT j.*, f.nama as fakultas_nama FROM jurusan j INNER JOIN fakultas f ON j.fakultas_id = f.id ORDER BY f.nama, j.nama");
        $jurusan = $stmt->fetchAll();
        
        $stmt = $db->query("SELECT k.*, j.nama as jurusan_nama FROM kelompok k INNER JOIN jurusan j ON k.jurusan_id = j.id ORDER BY j.nama, k.nama");
        $kelompok = $stmt->fetchAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/admin/tugas_form.php';
                return;
            }
            
            $tugasModel = new Tugas();
            list($target_fakultas, $target_jurusan, $target_kelompok) = $this->parseTarget();
            $data = [
                'tahap_id' => $_POST['tahap_id'],
                'judul' => $_POST['judul'],
                'deskripsi' => $_POST['deskripsi'],
                'format_diizinkan' => $_POST['format_diizinkan'],
                'ukuran_max_mb' => $_POST['ukuran_max_mb'],
                'deadline' => $_POST['deadline'],
                'target_fakultas_id' => $target_fakultas,
                'target_jurusan_id' => $target_jurusan,
                'target_kelompok_id' => $target_kelompok
            ];
            
            if ($tugasModel->createTugas($data, $_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . 'admin/tugas?success=Tugas berhasil ditambahkan');
                exit;
            } else {
                $error = "Gagal menambahkan tugas.";
            }
        }
        
        include_once __DIR__ . '/../views/admin/tugas_form.php';
    }
    
    public function editTugas($id) {
        User::requireRole(['admin', 'superadmin']);
        
        $tugasModel = new Tugas();
        $tugas = $tugasModel->getTugasById($id);
        
        if (!$tugas) {
            header('Location: ' . BASE_URL . 'admin/tugas');
            exit;
        }
        
        $timelineModel = new Timeline();
        $timeline = $timelineModel->getAllTahap();
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM fakultas ORDER BY nama");
        $fakultas = $stmt->fetchAll();
        
        $stmt = $db->query("SELECT j.*, f.nama as fakultas_nama FROM jurusan j INNER JOIN fakultas f ON j.fakultas_id = f.id ORDER BY f.nama, j.nama");
        $jurusan = $stmt->fetchAll();
        
        $stmt = $db->query("SELECT k.*, j.nama as jurusan_nama FROM kelompok k INNER JOIN jurusan j ON k.jurusan_id = j.id ORDER BY j.nama, k.nama");
        $kelompok = $stmt->fetchAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/admin/tugas_form.php';
                return;
            }
            
            list($target_fakultas, $target_jurusan, $target_kelompok) = $this->parseTarget();
            $data = [
                'judul' => $_POST['judul'],
                'deskripsi' => $_POST['deskripsi'],
                'format_diizinkan' => $_POST['format_diizinkan'],
                'ukuran_max_mb' => $_POST['ukuran_max_mb'],
                'deadline' => $_POST['deadline'],
                'target_fakultas_id' => $target_fakultas,
                'target_jurusan_id' => $target_jurusan,
                'target_kelompok_id' => $target_kelompok
            ];
            
            if ($tugasModel->updateTugas($id, $data)) {
                header('Location: ' . BASE_URL . 'admin/tugas?success=Tugas berhasil diperbarui');
                exit;
            } else {
                $error = "Gagal memperbarui tugas.";
            }
        }
        
        include_once __DIR__ . '/../views/admin/tugas_form.php';
    }
    
    public function hapusTugas($id) {
        User::requireRole(['admin', 'superadmin']);
        
        $tugasModel = new Tugas();
        if ($tugasModel->deleteTugas($id)) {
            header('Location: ' . BASE_URL . 'admin/tugas?success=Tugas berhasil dihapus');
        } else {
            header('Location: ' . BASE_URL . 'admin/tugas?error=Gagal menghapus tugas');
        }
        exit;
    }
    
    public function rekapAbsensi($sesi_id = null) {
        User::requireRole(['admin', 'superadmin']);
        
        $timelineModel = new Timeline();
        $absensiModel = new Absensi();
        
        if ($sesi_id) {
            $sesi = $timelineModel->getSesiById($sesi_id);
            
            if (!$sesi) {
                header('Location: ' . BASE_URL . 'admin/rekap-absensi');
                exit;
            }
            
            $filters = array_filter([
                'kelompok_id' => $_GET['kelompok_id'] ?? null,
                'fakultas_id' => $_GET['fakultas_id'] ?? null,
                'jurusan_id' => $_GET['jurusan_id'] ?? null,
                'status' => $_GET['status'] ?? null,
            ], function ($v) {
                return $v !== null && $v !== '';
            });
            
            $absensi = $absensiModel->getAbsensiBySesi($sesi_id, $filters);
            $stats = $absensiModel->getStatistikAbsensi($sesi_id);
            
            $db = Database::getInstance()->getConnection();
            $fakultas = $db->query("SELECT * FROM fakultas ORDER BY nama")->fetchAll();
            $kelompok = $db->query("SELECT k.*, j.nama as jurusan_nama FROM kelompok k INNER JOIN jurusan j ON k.jurusan_id = j.id ORDER BY j.nama, k.nama")->fetchAll();
            
            include_once __DIR__ . '/../views/admin/rekap_absensi_detail.php';
        } else {
            $timeline = $timelineModel->getAllTahap();
            
            $sesi_by_tahap = [];
            foreach ($timeline as $tahap) {
                $sesi_by_tahap[$tahap['id']] = $timelineModel->getSesiByTahap($tahap['id']);
            }
            
            include_once __DIR__ . '/../views/admin/rekap_absensi.php';
        }
    }
    
    public function verifikasiAbsensi($id) {
        User::requireRole(['admin', 'superadmin']);
        
        $absensiModel = new Absensi();
        $absensi = $absensiModel->getAbsensiById($id);
        
        if (!$absensi) {
            header('Location: ' . BASE_URL . 'admin/rekap-absensi');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/admin/verifikasi_absensi.php';
                return;
            }
            
            $status = $_POST['status'];
            $catatan = $_POST['catatan'] ?? null;
            
            if ($absensiModel->updateStatusAbsensi($id, $status, $catatan, $_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . 'admin/rekap-absensi/' . $absensi['sesi_id'] . '?success=Status absensi diperbarui');
                exit;
            } else {
                $error = "Gagal memperbarui status absensi.";
            }
        }
        
        include_once __DIR__ . '/../views/admin/verifikasi_absensi.php';
    }
    
    public function rekapTugas($tugas_id = null) {
        User::requireRole(['admin', 'superadmin']);
        
        $tugasModel = new Tugas();
        
        if ($tugas_id) {
            $tugas = $tugasModel->getTugasById($tugas_id);
            $pengumpulan = $tugasModel->getPengumpulanByTugas($tugas_id);
            $stats = $tugasModel->getStatistikTugas($tugas_id);
            include_once __DIR__ . '/../views/admin/rekap_tugas_detail.php';
        } else {
            $tugas_list = $tugasModel->getAllTugas();
            include_once __DIR__ . '/../views/admin/rekap_tugas.php';
        }
    }
    
    public function verifikasiTugas($id) {
        User::requireRole(['admin', 'superadmin']);
        
        $tugasModel = new Tugas();
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT pt.*, u.nim, u.nama, t.judul as tugas_judul
            FROM pengumpulan_tugas pt
            INNER JOIN users u ON pt.user_id = u.id
            INNER JOIN tugas t ON pt.tugas_id = t.id
            WHERE pt.id = ?
        ");
        $stmt->execute([$id]);
        $pengumpulan = $stmt->fetch();
        
        if (!$pengumpulan) {
            header('Location: ' . BASE_URL . 'admin/rekap-tugas');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/admin/verifikasi_tugas.php';
                return;
            }
            
            $status = $_POST['status'];
            $catatan = $_POST['catatan'] ?? null;
            
            if ($tugasModel->updateStatusPengumpulan($id, $status, $catatan, $_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . 'admin/rekap-tugas/' . $pengumpulan['tugas_id'] . '?success=Status tugas diperbarui');
                exit;
            } else {
                $error = "Gagal memperbarui status tugas.";
            }
        }
        
        include_once __DIR__ . '/../views/admin/verifikasi_tugas.php';
    }
    
    public function pengumuman() {
        User::requireRole(['admin', 'superadmin']);
        
        $pengumumanModel = new Pengumuman();
        $pengumuman = $pengumumanModel->getAllPengumuman();
        
        include_once __DIR__ . '/../views/admin/pengumuman.php';
    }
    
    public function tambahPengumuman() {
        User::requireRole(['admin', 'superadmin']);
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM fakultas ORDER BY nama");
        $fakultas = $stmt->fetchAll();
        
        $stmt = $db->query("SELECT j.*, f.nama as fakultas_nama FROM jurusan j INNER JOIN fakultas f ON j.fakultas_id = f.id ORDER BY f.nama, j.nama");
        $jurusan = $stmt->fetchAll();
        
        $stmt = $db->query("SELECT k.*, j.nama as jurusan_nama FROM kelompok k INNER JOIN jurusan j ON k.jurusan_id = j.id ORDER BY j.nama, k.nama");
        $kelompok = $stmt->fetchAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/admin/pengumuman_form.php';
                return;
            }
            
            list($target_level, $target_id) = $this->parsePengumumanTarget();
            $pengumumanModel = new Pengumuman();
            $data = [
                'judul' => $_POST['judul'],
                'isi' => $_POST['isi'],
                'prioritas' => $_POST['prioritas'],
                'target_level' => $target_level,
                'target_id' => $target_id
            ];
            
            if ($pengumumanModel->createPengumuman($data, $_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . 'admin/pengumuman?success=Pengumuman berhasil ditambahkan');
                exit;
            } else {
                $error = "Gagal menambahkan pengumuman.";
            }
        }
        
        include_once __DIR__ . '/../views/admin/pengumuman_form.php';
    }
    
    public function editPengumuman($id) {
        User::requireRole(['admin', 'superadmin']);
        
        $pengumumanModel = new Pengumuman();
        $pengumuman = $pengumumanModel->getPengumumanById($id);
        
        if (!$pengumuman) {
            header('Location: ' . BASE_URL . 'admin/pengumuman');
            exit;
        }
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM fakultas ORDER BY nama");
        $fakultas = $stmt->fetchAll();
        
        $stmt = $db->query("SELECT j.*, f.nama as fakultas_nama FROM jurusan j INNER JOIN fakultas f ON j.fakultas_id = f.id ORDER BY f.nama, j.nama");
        $jurusan = $stmt->fetchAll();
        
        $stmt = $db->query("SELECT k.*, j.nama as jurusan_nama FROM kelompok k INNER JOIN jurusan j ON k.jurusan_id = j.id ORDER BY j.nama, k.nama");
        $kelompok = $stmt->fetchAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/admin/pengumuman_form.php';
                return;
            }
            
            $data = [
                'judul' => $_POST['judul'],
                'isi' => $_POST['isi'],
                'prioritas' => $_POST['prioritas'],
                'target_level' => $_POST['target_level'],
                'target_id' => $_POST['target_id'] ?? null
            ];
            
            if ($pengumumanModel->updatePengumuman($id, $data)) {
                header('Location: ' . BASE_URL . 'admin/pengumuman?success=Pengumuman berhasil diperbarui');
                exit;
            } else {
                $error = "Gagal memperbarui pengumuman.";
            }
        }
        
        include_once __DIR__ . '/../views/admin/pengumuman_form.php';
    }
    
    public function hapusPengumuman($id) {
        User::requireRole(['admin', 'superadmin']);
        
        $pengumumanModel = new Pengumuman();
        if ($pengumumanModel->deletePengumuman($id)) {
            header('Location: ' . BASE_URL . 'admin/pengumuman?success=Pengumuman berhasil dihapus');
        } else {
            header('Location: ' . BASE_URL . 'admin/pengumuman?error=Gagal menghapus pengumuman');
        }
        exit;
    }
    
    public function users() {
        User::requireRole(['admin', 'superadmin']);
        
        $userModel = new User();
        
        $filters = array_filter([
            'fakultas_id' => $_GET['fakultas_id'] ?? null,
            'jurusan_id' => $_GET['jurusan_id'] ?? null,
            'kelompok_id' => $_GET['kelompok_id'] ?? null,
        ], function ($v) {
            return $v !== null && $v !== '';
        });
        
        $users = $userModel->getAllMaba($filters);
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM users WHERE role IN ('panitia', 'admin', 'superadmin') ORDER BY role, nama");
        $staff = $stmt->fetchAll();
        
        $fakultas = $db->query("SELECT * FROM fakultas ORDER BY nama")->fetchAll();
        $jurusan = $db->query("SELECT j.*, f.nama as fakultas_nama FROM jurusan j INNER JOIN fakultas f ON j.fakultas_id = f.id ORDER BY f.nama, j.nama")->fetchAll();
        $kelompok = $db->query("SELECT k.*, j.nama as jurusan_nama FROM kelompok k INNER JOIN jurusan j ON k.jurusan_id = j.id ORDER BY j.nama, k.nama")->fetchAll();
        
        include_once __DIR__ . '/../views/admin/users.php';
    }
    
    public function tambahUser() {
        User::requireRole('superadmin');
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM fakultas ORDER BY nama");
        $fakultas = $stmt->fetchAll();
        
        $stmt = $db->query("SELECT j.*, f.nama as fakultas_nama FROM jurusan j INNER JOIN fakultas f ON j.fakultas_id = f.id ORDER BY f.nama, j.nama");
        $jurusan = $stmt->fetchAll();
        
        $stmt = $db->query("SELECT k.*, j.nama as jurusan_nama FROM kelompok k INNER JOIN jurusan j ON k.jurusan_id = j.id ORDER BY j.nama, k.nama");
        $kelompok = $stmt->fetchAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/admin/user_form.php';
                return;
            }
            
            $userModel = new User();
            $data = [
                'nim' => $_POST['nim'],
                'nama' => $_POST['nama'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'role' => $_POST['role'],
                'fakultas_id' => $_POST['fakultas_id'] ?? null,
                'jurusan_id' => $_POST['jurusan_id'] ?? null,
                'kelompok_id' => $_POST['kelompok_id'] ?? null
            ];
            
            if ($userModel->register($data)) {
                header('Location: ' . BASE_URL . 'admin/users?success=User berhasil ditambahkan');
                exit;
            } else {
                $error = "Gagal menambahkan user.";
            }
        }
        
        include_once __DIR__ . '/../views/admin/user_form.php';
    }
    
    public function pengesahan() {
        User::requireRole(['admin', 'superadmin']);
        
        $filters = array_filter([
            'status' => $_GET['status'] ?? null,
            'fakultas_id' => $_GET['fakultas_id'] ?? null,
        ], function ($v) {
            return $v !== null && $v !== '';
        });
        
        $pengesahanModel = new Pengesahan();
        $list = $pengesahanModel->getAllPengesahan($filters);
        
        $db = Database::getInstance()->getConnection();
        $fakultas = $db->query("SELECT * FROM fakultas ORDER BY nama")->fetchAll();
        
        include_once __DIR__ . '/../views/admin/pengesahan.php';
    }
    
    public function prosesSemuaPengesahan() {
        User::requireRole(['admin', 'superadmin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            header('Location: ' . BASE_URL . 'admin/pengesahan?error=Permintaan tidak valid');
            exit;
        }
        
        $pengesahanModel = new Pengesahan();
        $jumlah = $pengesahanModel->prosesAllPengesahan($_SESSION['user_id']);
        
        header('Location: ' . BASE_URL . 'admin/pengesahan?success=' . urlencode($jumlah . ' pengesahan dihitung ulang'));
        exit;
    }
    
    public function prosesPengesahan($user_id) {
        User::requireRole(['admin', 'superadmin']);
        
        $pengesahanModel = new Pengesahan();
        $kelengkapan = $pengesahanModel->checkKelengkapan($user_id);
        
        $userModel = new User();
        $user = $userModel->getUserById($user_id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/admin/proses_pengesahan.php';
                return;
            }
            
            $override_status = null;
            $catatan_override = null;
            
            if (isset($_POST['override']) && $_POST['override'] === 'yes') {
                $override_status = $_POST['override_status'];
                $catatan_override = $_POST['catatan_override'] ?? null;
            }
            
            if ($pengesahanModel->prosesPengesahan($user_id, $_SESSION['user_id'], $override_status, $catatan_override)) {
                header('Location: ' . BASE_URL . 'admin/pengesahan?success=Pengesahan berhasil diproses');
                exit;
            } else {
                $error = "Gagal memproses pengesahan.";
            }
        }
        
        include_once __DIR__ . '/../views/admin/proses_pengesahan.php';
    }
    
    public function exportAbsensi() {
        User::requireRole(['admin', 'superadmin']);
        
        $absensiModel = new Absensi();
        $db = Database::getInstance()->getConnection();
        
        $sesi_id = $_GET['sesi_id'] ?? null;

        if ($sesi_id) {
            $stmt = $db->prepare("
                SELECT a.*, u.nim, u.nama, s.judul as sesi_judul, s.tanggal
                FROM absensi a
                INNER JOIN users u ON a.user_id = u.id
                INNER JOIN sesi s ON a.sesi_id = s.id
                WHERE a.sesi_id = ?
                ORDER BY u.nama ASC
            ");
            $stmt->execute([$sesi_id]);
        } else {
            $stmt = $db->query("
                SELECT a.*, u.nim, u.nama, s.judul as sesi_judul, s.tanggal
                FROM absensi a
                INNER JOIN users u ON a.user_id = u.id
                INNER JOIN sesi s ON a.sesi_id = s.id
                ORDER BY s.tanggal DESC, u.nama ASC
            ");
        }
        $data = $stmt->fetchAll();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=rekap_absensi_' . date('Ymd') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['No', 'NIM', 'Nama', 'Sesi', 'Tanggal', 'Status', 'Jarak (m)', 'Waktu Submit']);
        
        $no = 1;
        foreach ($data as $row) {
            fputcsv($output, [
                $no++,
                $row['nim'],
                $row['nama'],
                $row['sesi_judul'],
                $row['tanggal'],
                $row['status'],
                $row['jarak_dari_lokasi_meter'],
                $row['waktu_submit_server']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    public function exportTugas() {
        User::requireRole(['admin', 'superadmin']);
        
        $db = Database::getInstance()->getConnection();
        
        $tugas_id = $_GET['tugas_id'] ?? null;

        if ($tugas_id) {
            $stmt = $db->prepare("
                SELECT pt.*, u.nim, u.nama, t.judul as tugas_judul
                FROM pengumpulan_tugas pt
                INNER JOIN users u ON pt.user_id = u.id
                INNER JOIN tugas t ON pt.tugas_id = t.id
                WHERE pt.tugas_id = ?
                ORDER BY u.nama ASC
            ");
            $stmt->execute([$tugas_id]);
        } else {
            $stmt = $db->query("
                SELECT pt.*, u.nim, u.nama, t.judul as tugas_judul
                FROM pengumpulan_tugas pt
                INNER JOIN users u ON pt.user_id = u.id
                INNER JOIN tugas t ON pt.tugas_id = t.id
                ORDER BY t.deadline DESC, u.nama ASC
            ");
        }
        $data = $stmt->fetchAll();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=rekap_tugas_' . date('Ymd') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['No', 'NIM', 'Nama', 'Tugas', 'Status', 'Waktu Submit', 'Catatan']);
        
        $no = 1;
        foreach ($data as $row) {
            fputcsv($output, [
                $no++,
                $row['nim'],
                $row['nama'],
                $row['tugas_judul'],
                $row['status'],
                $row['waktu_submit_server'],
                $row['catatan_panitia']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    public function auditLog() {
        User::requireRole('superadmin');
        
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->query("
            SELECT al.*, u.nama as user_nama
            FROM audit_log al
            INNER JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC
            LIMIT 500
        ");
        $logs = $stmt->fetchAll();
        
        include_once __DIR__ . '/../views/admin/audit_log.php';
    }
    
    // Daftar token reset password aktif; admin menyalurkan tautan ke mahasiswa
    // secara manual karena pengiriman email belum dikonfigurasi.
    public function resetPermintaan() {
        User::requireRole(['admin', 'superadmin']);
        
        $userModel = new User();
        $tokens = $userModel->getActiveResetTokens();
        
        include_once __DIR__ . '/../views/admin/reset_permintaan.php';
    }
    
    // Pengaturan aplikasi (mis. ambang kehadiran untuk pengesahan otomatis).
    public function pengaturan() {
        User::requireRole(['admin', 'superadmin']);
        
        $pengaturanModel = new Pengaturan();
        $error = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
            } else {
                $min = filter_input(INPUT_POST, 'min_kehadiran_pengesahan', FILTER_VALIDATE_FLOAT);
                if ($min === false || $min === null || $min < 0 || $min > 100) {
                    $error = "Ambang kehadiran harus berupa angka antara 0 sampai 100.";
                } else {
                    $pengaturanModel->set('min_kehadiran_pengesahan', (string)$min, $_SESSION['user_id']);
                    header('Location: ' . BASE_URL . 'admin/pengaturan?success=Pengaturan berhasil disimpan');
                    exit;
                }
            }
        }
        
        $min_kehadiran = (float)$pengaturanModel->get('min_kehadiran_pengesahan', 75);
        
        include_once __DIR__ . '/../views/admin/pengaturan.php';
    }
    
    public function importMaba() {
        User::requireRole('superadmin');
        
        $result = null;
        $error = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
            } elseif (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
                $error = "Harap pilih file CSV.";
            } else {
                $extension = strtolower(pathinfo($_FILES['csv']['name'], PATHINFO_EXTENSION));
                if ($extension !== 'csv') {
                    $error = "File harus berformat .csv";
                } else {
                    $userModel = new User();
                    $result = $userModel->importMabaFromCSV($_FILES['csv']['tmp_name']);
                }
            }
        }
        
        include_once __DIR__ . '/../views/admin/import_maba.php';
    }
}
