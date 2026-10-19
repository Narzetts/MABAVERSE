<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Timeline.php';
require_once __DIR__ . '/../models/Absensi.php';
require_once __DIR__ . '/../models/Pengumuman.php';

class ApiController {
    
    public function __construct() {
        header('Content-Type: application/json');
    }
    
    public function checkAbsensi() {
        if (!User::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        $sesi_id = $_GET['sesi_id'] ?? null;
        
        if (!$sesi_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing sesi_id']);
            return;
        }
        
        $absensiModel = new Absensi();
        $exists = $absensiModel->checkAbsensiExists($sesi_id, $user_id);
        
        echo json_encode(['absented' => $exists]);
    }
    
    public function getUnreadPengumuman() {
        if (!User::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        $user_fakultas_id = $_SESSION['user_fakultas_id'] ?? null;
        $user_jurusan_id = $_SESSION['user_jurusan_id'] ?? null;
        $user_kelompok_id = $_SESSION['user_kelompok_id'] ?? null;
        
        $pengumumanModel = new Pengumuman();
        $count = $pengumumanModel->getUnreadCount($user_id, $user_fakultas_id, $user_jurusan_id, $user_kelompok_id);
        
        echo json_encode(['unread_count' => $count]);
    }
    
    public function getStatusTimeline() {
        $timelineModel = new Timeline();
        $timelineModel->updateStatus();
        $timeline = $timelineModel->getAllTahap();
        
        echo json_encode(['timeline' => $timeline]);
    }
    
    public function getAbsensiStats() {
        $user_id = $_SESSION['user_id'] ?? null;
        $sesi_id = $_GET['sesi_id'] ?? null;
        
        if (!$user_id && !$sesi_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing user_id or sesi_id']);
            return;
        }
        
        $absensiModel = new Absensi();
        
        if ($sesi_id) {
            $stats = $absensiModel->getStatistikAbsensi($sesi_id);
        } else {
            $stats = $absensiModel->getStatistikAbsensi(null, $user_id);
        }
        
        echo json_encode(['stats' => $stats]);
    }
    
    public function reorderTahap() {
        if (!User::isLoggedIn() || !in_array($_SESSION['user_role'], ['admin', 'superadmin'], true)) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $csrf = $input['csrf_token'] ?? '';
        $ids = $input['ids'] ?? null;
        
        if (!verifyCSRFToken($csrf)) {
            http_response_code(403);
            echo json_encode(['error' => 'Token CSRF tidak valid']);
            return;
        }
        
        if (!is_array($ids) || empty($ids)) {
            http_response_code(400);
            echo json_encode(['error' => 'Data urutan tidak valid']);
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("UPDATE tahap_ospek SET urutan = ? WHERE id = ?");
            $urutan = 1;
            foreach ($ids as $id) {
                $stmt->execute([$urutan, (int)$id]);
                $urutan++;
            }
            $db->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Gagal menyimpan urutan']);
        }
    }
}
