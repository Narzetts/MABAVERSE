<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Timeline.php';
require_once __DIR__ . '/../models/Tugas.php';
require_once __DIR__ . '/../models/Absensi.php';
require_once __DIR__ . '/../models/Pengesahan.php';
require_once __DIR__ . '/../models/Pengumuman.php';
require_once __DIR__ . '/../utils/upload.php';

class MabaController {
    
    public function dashboard() {
        User::requireRole('maba');
        
        $userModel = new User();
        $timelineModel = new Timeline();
        $absensiModel = new Absensi();
        $pengumumanModel = new Pengumuman();
        $pengesahanModel = new Pengesahan();
        
        $user = $userModel->getUserById($_SESSION['user_id']);
        if (!$user) {
            header('Location: ' . BASE_URL . 'login?expired=1');
            exit;
        }
        $timeline = $timelineModel->getAllTahap('maba', $_SESSION['user_fakultas_id'], $_SESSION['user_jurusan_id']);
        $upcoming_sesi = $timelineModel->getUpcomingSesi($_SESSION['user_fakultas_id'], $_SESSION['user_jurusan_id']);
        $absensi_stats = $absensiModel->getStatistikAbsensi(null, $_SESSION['user_id']);
        $pengumuman = $pengumumanModel->getAllPengumuman(
            $_SESSION['user_id'], 
            $_SESSION['user_fakultas_id'], 
            $_SESSION['user_jurusan_id'],
            $_SESSION['user_kelompok_id']
        );
        $pengesahan = $pengesahanModel->getPengesahanByUser($_SESSION['user_id']);
        
        include_once __DIR__ . '/../views/maba/dashboard.php';
    }
    
    public function timeline() {
        User::requireRole('maba');
        
        $timelineModel = new Timeline();
        $timeline = $timelineModel->getAllTahap('maba', $_SESSION['user_fakultas_id'], $_SESSION['user_jurusan_id']);
        
        $sesi_by_tahap = [];
        foreach ($timeline as $tahap) {
            $sesi_by_tahap[$tahap['id']] = $timelineModel->getSesiByTahap($tahap['id']);
        }
        
        include_once __DIR__ . '/../views/maba/timeline.php';
    }
    
    public function detailSesi($sesi_id) {
        User::requireRole('maba');
        
        $timelineModel = new Timeline();
        $absensiModel = new Absensi();
        
        $sesi = $timelineModel->getSesiById($sesi_id);
        
        if (!$sesi) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
        
        $tahap = $timelineModel->getTahapById($sesi['tahap_id']);
        $absensi_exists = $absensiModel->checkAbsensiExists($sesi_id, $_SESSION['user_id']);
        $absensi_saya = $absensi_exists ? $absensiModel->getAbsensiByUserSesi($_SESSION['user_id'], $sesi_id) : null;
        
        include_once __DIR__ . '/../views/maba/detail_sesi.php';
    }
    
    public function absen($sesi_id) {
        User::requireRole('maba');
        
        $timelineModel = new Timeline();
        $absensiModel = new Absensi();
        
        $sesi = $timelineModel->getSesiById($sesi_id);
        
        if (!$sesi) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
        
        $tahap = $timelineModel->getTahapById($sesi['tahap_id']);
        $absensi_exists = $absensiModel->checkAbsensiExists($sesi_id, $_SESSION['user_id']);
        $error = null;
        
        if ($absensi_exists) {
            $error = "Anda sudah melakukan absensi untuk sesi ini.";
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
            } elseif (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                $error = "Foto absensi wajib diambil langsung dari kamera.";
            } elseif (!isset($_POST['lat'], $_POST['lng']) || !is_numeric($_POST['lat']) || !is_numeric($_POST['lng'])) {
                $error = "Lokasi tidak terdeteksi. Aktifkan izin lokasi lalu coba lagi.";
            } else {
                try {
                    $foto_path = UploadHandler::handleFotoAbsensi($_FILES['foto']);
                    
                    $absensiData = [
                        'sesi_id' => $sesi_id,
                        'user_id' => $_SESSION['user_id'],
                        'foto_path' => $foto_path,
                        'lat' => (float)$_POST['lat'],
                        'lng' => (float)$_POST['lng']
                    ];
                    
                    if ($absensiModel->submitAbsensi($absensiData)) {
                        header('Location: ' . BASE_URL . 'detail-sesi/' . $sesi_id . '?success=Absensi berhasil tercatat');
                        exit;
                    } else {
                        UploadHandler::deleteFile($foto_path);
                        $error = "Gagal menyimpan absensi.";
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }
        
        include_once __DIR__ . '/../views/maba/absen.php';
    }
    
    public function tugas() {
        User::requireRole('maba');
        
        $tugasModel = new Tugas();
        $filters = [
            'fakultas_id' => $_SESSION['user_fakultas_id'],
            'jurusan_id' => $_SESSION['user_jurusan_id'],
            'kelompok_id' => $_SESSION['user_kelompok_id']
        ];
        
        $tugas_list = $tugasModel->getAllTugas($_SESSION['user_id'], $filters);
        $riwayat = $tugasModel->getPengumpulanByUser($_SESSION['user_id']);
        
        include_once __DIR__ . '/../views/maba/tugas.php';
    }
    
    public function submitTugas($tugas_id) {
        User::requireRole('maba');
        
        $tugasModel = new Tugas();
        $tugas = $tugasModel->getTugasById($tugas_id);
        
        if (!$tugas) {
            header('Location: ' . BASE_URL . 'tugas');
            exit;
        }
        
        $current_submission = $tugasModel->getPengumpulanByUser($_SESSION['user_id'], $tugas_id);
        $deadline_lewat = date('Y-m-d H:i:s') > $tugas['deadline'];
        $error = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
            } elseif ($deadline_lewat && $current_submission) {
                $error = "Batas waktu sudah lewat. File yang sudah dikumpulkan tidak dapat diganti.";
            } elseif (!isset($_FILES['file_tugas']) || $_FILES['file_tugas']['error'] !== UPLOAD_ERR_OK) {
                $error = "Harap pilih file tugas.";
            } else {
                try {
                    $allowed_formats = explode(',', $tugas['format_diizinkan']);
                    $allowed_mimes = [];
                    
                    foreach ($allowed_formats as $format) {
                        $format = trim($format);
                        switch ($format) {
                            case 'pdf':
                                $allowed_mimes[] = 'application/pdf';
                                break;
                            case 'jpg':
                            case 'jpeg':
                                $allowed_mimes[] = 'image/jpeg';
                                break;
                            case 'png':
                                $allowed_mimes[] = 'image/png';
                                break;
                            case 'doc':
                                $allowed_mimes[] = 'application/msword';
                                break;
                            case 'docx':
                                $allowed_mimes[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                                break;
                        }
                    }
                    
                    // Batas ukuran mengikuti pengaturan tugas, tidak melebihi batas global.
                    $max_bytes = ((int)$tugas['ukuran_max_mb'] > 0)
                        ? min(MAX_FILE_SIZE, (int)$tugas['ukuran_max_mb'] * 1024 * 1024)
                        : MAX_FILE_SIZE;
                    
                    $file_path = UploadHandler::handleFileTugas($_FILES['file_tugas'], $allowed_mimes, $max_bytes);
                    
                    if ($tugasModel->submitTugas($tugas_id, $_SESSION['user_id'], $file_path)) {
                        if ($current_submission) {
                            UploadHandler::deleteFile($current_submission['file_path']);
                        }
                        header('Location: ' . BASE_URL . 'tugas?success=Tugas berhasil dikumpulkan');
                        exit;
                    } else {
                        UploadHandler::deleteFile($file_path);
                        $error = "Gagal menyimpan tugas.";
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }
        
        include_once __DIR__ . '/../views/maba/submit_tugas.php';
    }
    
    public function riwayatTugas() {
        User::requireRole('maba');
        
        $tugasModel = new Tugas();
        $riwayat = $tugasModel->getPengumpulanByUser($_SESSION['user_id']);
        
        include_once __DIR__ . '/../views/maba/riwayat_tugas.php';
    }
    
    public function pengumuman() {
        User::requireRole('maba');
        
        $pengumumanModel = new Pengumuman();
        $pengumuman = $pengumumanModel->getAllPengumuman(
            $_SESSION['user_id'], 
            $_SESSION['user_fakultas_id'], 
            $_SESSION['user_jurusan_id'],
            $_SESSION['user_kelompok_id']
        );
        
        if (isset($_GET['read'])) {
            $pengumumanModel->markAsRead($_GET['read'], $_SESSION['user_id']);
            header('Location: ' . BASE_URL . 'pengumuman');
            exit;
        }
        
        include_once __DIR__ . '/../views/maba/pengumuman.php';
    }
    
    public function detailPengumuman($id) {
        User::requireRole('maba');
        
        $pengumumanModel = new Pengumuman();
        $pengumuman = $pengumumanModel->getPengumumanById($id);
        
        if (!$pengumuman) {
            header('Location: ' . BASE_URL . 'pengumuman');
            exit;
        }
        
        $pengumumanModel->markAsRead($id, $_SESSION['user_id']);
        
        include_once __DIR__ . '/../views/maba/detail_pengumuman.php';
    }
    
    public function statusPengesahan() {
        User::requireRole('maba');
        
        $pengesahanModel = new Pengesahan();
        $status = $pengesahanModel->checkKelengkapan($_SESSION['user_id']);
        if (!$status) {
            header('Location: ' . BASE_URL . 'login?expired=1');
            exit;
        }
        $pengesahan = $pengesahanModel->getPengesahanByUser($_SESSION['user_id']);
        
        include_once __DIR__ . '/../views/maba/status_pengesahan.php';
    }
    
    public function profil() {
        User::requireRole('maba');
        
        $userModel = new User();
        $user = $userModel->getUserById($_SESSION['user_id']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/maba/profil.php';
                return;
            }
            
            $update_data = [];
            
            if (!empty($_POST['nama'])) {
                $update_data['nama'] = trim($_POST['nama']);
            }
            
            if (!empty($_POST['email'])) {
                $update_data['email'] = trim($_POST['email']);
            }
            
            if (!empty($_POST['no_kontak_darurat'])) {
                $update_data['no_kontak_darurat'] = trim($_POST['no_kontak_darurat']);
            }
            
            if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
                try {
                    $foto_path = UploadHandler::handleFotoProfil($_FILES['foto_profil']);
                    $update_data['foto_profil'] = $foto_path;
                } catch (Exception $e) {
                    $error = $e->getMessage();
                    include_once __DIR__ . '/../views/maba/profil.php';
                    return;
                }
            }
            
            if (!empty($update_data)) {
                if ($userModel->updateProfile($_SESSION['user_id'], $update_data)) {
                    if (isset($update_data['foto_profil']) && $user['foto_profil']) {
                        UploadHandler::deleteFile($user['foto_profil']);
                    }
                    $success = "Profil berhasil diperbarui.";
                    $user = $userModel->getUserById($_SESSION['user_id']);
                } else {
                    $error = "Gagal memperbarui profil.";
                }
            }
        }
        
        include_once __DIR__ . '/../views/maba/profil.php';
    }
    
    public function ubahPassword() {
        User::requireRole('maba');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
                include_once __DIR__ . '/../views/maba/ubah_password.php';
                return;
            }
            
            $old_password = $_POST['old_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            if ($new_password !== $confirm_password) {
                $error = "Password baru tidak cocok.";
                include_once __DIR__ . '/../views/maba/ubah_password.php';
                return;
            }
            
            if (strlen($new_password) < 6) {
                $error = "Password minimal 6 karakter.";
                include_once __DIR__ . '/../views/maba/ubah_password.php';
                return;
            }
            
            $userModel = new User();
            if ($userModel->changePassword($_SESSION['user_id'], $old_password, $new_password)) {
                $success = "Password berhasil diubah.";
            } else {
                $error = "Password lama salah.";
            }
        }
        
        include_once __DIR__ . '/../views/maba/ubah_password.php';
    }
}
