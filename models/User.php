<?php
require_once __DIR__ . '/../config/config.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function login($nim, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE nim = ? AND is_active = 1");
        $stmt->execute([$nim]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nim'] = $user['nim'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_fakultas_id'] = $user['fakultas_id'];
            $_SESSION['user_jurusan_id'] = $user['jurusan_id'];
            $_SESSION['user_kelompok_id'] = $user['kelompok_id'];

            // Panitia tidak menyimpan kelompok_id di tabel users;
            // relasinya lewat kelompok.panitia_id.
            if ($user['role'] === 'panitia' && empty($user['kelompok_id'])) {
                $stmt = $this->db->prepare("SELECT id FROM kelompok WHERE panitia_id = ? LIMIT 1");
                $stmt->execute([$user['id']]);
                $kelompok = $stmt->fetch();
                if ($kelompok) {
                    $_SESSION['user_kelompok_id'] = $kelompok['id'];
                }
            }

            session_regenerate_id(true);
            $this->auditLog($user['id'], 'LOGIN', 'users', $user['id']);
            return true;
        }
        return false;
    }
    
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->auditLog($_SESSION['user_id'], 'LOGOUT', 'users', $_SESSION['user_id']);
        }
        session_destroy();
    }
    
    public function register($data) {
        $stmt = $this->db->prepare("
            INSERT INTO users (nim, nama, email, password_hash, role, fakultas_id, jurusan_id, kelompok_id, no_kontak_darurat)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
        
        return $stmt->execute([
            $data['nim'],
            $data['nama'],
            $data['email'],
            $password_hash,
            $data['role'] ?? 'maba',
            $data['fakultas_id'] ?? null,
            $data['jurusan_id'] ?? null,
            $data['kelompok_id'] ?? null,
            $data['no_kontak_darurat'] ?? null
        ]);
    }
    
    public function getUserById($id) {
        $stmt = $this->db->prepare("
            SELECT u.*, f.nama as fakultas_nama, j.nama as jurusan_nama, k.nama as kelompok_nama
            FROM users u
            LEFT JOIN fakultas f ON u.fakultas_id = f.id
            LEFT JOIN jurusan j ON u.jurusan_id = j.id
            LEFT JOIN kelompok k ON u.kelompok_id = k.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getUserByNim($nim) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE nim = ?");
        $stmt->execute([$nim]);
        return $stmt->fetch();
    }
    
    public function updateProfile($user_id, $data) {
        $allowed_fields = ['nama', 'email', 'no_kontak_darurat', 'foto_profil'];
        $fields = [];
        $values = [];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) return false;
        
        $values[] = $user_id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        $result = $stmt->execute($values);
        if ($result) {
            $this->auditLog($user_id, 'UPDATE_PROFILE', 'users', $user_id, null, json_encode($data));
        }
        return $result;
    }
    
    public function changePassword($user_id, $old_password, $new_password) {
        $user = $this->getUserById($user_id);
        
        if (!password_verify($old_password, $user['password_hash'])) {
            return false;
        }
        
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        
        $result = $stmt->execute([$new_hash, $user_id]);
        if ($result) {
            $this->auditLog($user_id, 'CHANGE_PASSWORD', 'users', $user_id);
        }
        return $result;
    }
    
    public function getAllMaba($filters = []) {
        $sql = "
            SELECT u.*, f.nama as fakultas_nama, j.nama as jurusan_nama, k.nama as kelompok_nama
            FROM users u
            LEFT JOIN fakultas f ON u.fakultas_id = f.id
            LEFT JOIN jurusan j ON u.jurusan_id = j.id
            LEFT JOIN kelompok k ON u.kelompok_id = k.id
            WHERE u.role = 'maba'
        ";
        
        $params = [];
        
        if (!empty($filters['fakultas_id'])) {
            $sql .= " AND u.fakultas_id = ?";
            $params[] = $filters['fakultas_id'];
        }
        
        if (!empty($filters['jurusan_id'])) {
            $sql .= " AND u.jurusan_id = ?";
            $params[] = $filters['jurusan_id'];
        }
        
        if (!empty($filters['kelompok_id'])) {
            $sql .= " AND u.kelompok_id = ?";
            $params[] = $filters['kelompok_id'];
        }
        
        $sql .= " ORDER BY u.nama ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function createPasswordResetToken($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) return false;
        
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $this->db->prepare("
            INSERT INTO password_reset_tokens (user_id, token, expires_at)
            VALUES (?, ?, ?)
        ");
        
        if ($stmt->execute([$user['id'], $token, $expires])) {
            return $token;
        }
        return false;
    }
    
    public function resetPassword($token, $new_password) {
        $stmt = $this->db->prepare("
            SELECT user_id FROM password_reset_tokens
            WHERE token = ? AND expires_at > NOW() AND used = 0
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();
        
        if (!$reset) return false;
        
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$new_hash, $reset['user_id']]);
            
            $stmt = $this->db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
            $stmt->execute([$token]);
            
            $this->auditLog($reset['user_id'], 'RESET_PASSWORD', 'users', $reset['user_id']);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    // Token reset aktif yang siap disalurkan manual oleh admin ospek.
    public function getActiveResetTokens() {
        $stmt = $this->db->query("
            SELECT t.id, t.token, t.created_at, t.expires_at, u.nim, u.nama
            FROM password_reset_tokens t
            INNER JOIN users u ON t.user_id = u.id
            WHERE t.used = 0 AND t.expires_at > NOW()
            ORDER BY t.created_at DESC
        ");
        return $stmt->fetchAll();
    }
    
    public function importMabaFromCSV($file_path) {
        $imported = 0;
        $errors = [];
        
        if (($handle = fopen($file_path, "r")) !== false) {
            $header = fgetcsv($handle);
            
            while (($data = fgetcsv($handle)) !== false) {
                try {
                    $maba_data = [
                        'nim' => $data[0],
                        'nama' => $data[1],
                        'email' => $data[2],
                        'password' => $data[3] ?? 'mabaverse123',
                        'fakultas_id' => $data[4],
                        'jurusan_id' => $data[5],
                        'kelompok_id' => $data[6] ?? null,
                        'no_kontak_darurat' => $data[7] ?? null,
                        'role' => 'maba'
                    ];
                    
                    if ($this->register($maba_data)) {
                        $imported++;
                    }
                } catch (Exception $e) {
                    $errors[] = "Row error: " . $e->getMessage();
                }
            }
            fclose($handle);
        }
        
        return ['imported' => $imported, 'errors' => $errors];
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
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        // Sesi lama bisa menunjuk akun yang sudah dihapus atau dinonaktifkan
        // (mis. setelah restore database). Tanpa validasi ini halaman dirender
        // dengan data kosong dan memunculkan warning PHP.
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT role FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        $akun = $stmt->fetch();
        
        if (!$akun) {
            $_SESSION = [];
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            header('Location: ' . BASE_URL . 'login?expired=1');
            exit;
        }
        
        // Sinkronkan role bila diubah admin di tengah sesi berjalan.
        $_SESSION['user_role'] = $akun['role'];
    }
    
    public static function requireRole($roles) {
        self::requireLogin();
        if (!in_array($_SESSION['user_role'], (array)$roles)) {
            header('Location: ' . BASE_URL . 'forbidden');
            exit;
        }
    }
}
