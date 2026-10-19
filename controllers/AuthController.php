<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    
    public function login() {
        $error = null;
        $success = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Rate limiting sederhana: maks 5 percobaan gagal per 10 menit per sesi
            $fails = (int)($_SESSION['login_fails'] ?? 0);
            $last_fail = (int)($_SESSION['login_last_fail'] ?? 0);
            
            if ($fails >= 5 && (time() - $last_fail) < 600) {
                $sisa_menit = (int)ceil((600 - (time() - $last_fail)) / 60);
                $error = "Terlalu banyak percobaan login. Coba lagi dalam {$sisa_menit} menit.";
            } elseif (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                $error = "Token CSRF tidak valid.";
            } else {
                $nim = trim($_POST['nim']);
                $password = $_POST['password'];
                
                $userModel = new User();
                if ($userModel->login($nim, $password)) {
                    unset($_SESSION['login_fails'], $_SESSION['login_last_fail']);
                    switch ($_SESSION['user_role']) {
                        case 'panitia':
                            header('Location: ' . BASE_URL . 'panitia/dashboard');
                            break;
                        case 'admin':
                        case 'superadmin':
                            header('Location: ' . BASE_URL . 'admin/dashboard');
                            break;
                        default:
                            header('Location: ' . BASE_URL . 'dashboard');
                    }
                    exit;
                } else {
                    $_SESSION['login_fails'] = $fails + 1;
                    $_SESSION['login_last_fail'] = time();
                    $error = "NIM atau password salah.";
                }
            }
        }
        
        // Diarahkan oleh requireLogin() saat sesi menunjuk akun yang tidak ada.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['expired'])) {
            $error = 'Sesi Anda tidak berlaku lagi. Silakan masuk kembali.';
        }
        
        include_once __DIR__ . '/../views/auth/login.php';
    }
    
    public function logout() {
        $userModel = new User();
        $userModel->logout();
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
    
    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            
            $userModel = new User();
            $userModel->createPasswordResetToken($email);
            
            // Pesan selalu sama supaya halaman ini tidak membocorkan email mana yang terdaftar.
            // Pengiriman email otomatis belum dikonfigurasi; tautan diserahkan manual oleh admin ospek.
            $message = "Permintaan reset dicatat dan diproses manual oleh admin ospek (pengiriman email otomatis belum aktif). Hubungi admin untuk menerima tautan reset.";
            
            include_once __DIR__ . '/../views/auth/forgot_password.php';
            return;
        }
        
        include_once __DIR__ . '/../views/auth/forgot_password.php';
    }
    
    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            header('Location: ' . BASE_URL . 'forgot-password');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            if ($new_password !== $confirm_password) {
                $error = "Password tidak cocok.";
                include_once __DIR__ . '/../views/auth/reset_password.php';
                return;
            }
            
            if (strlen($new_password) < 6) {
                $error = "Password minimal 6 karakter.";
                include_once __DIR__ . '/../views/auth/reset_password.php';
                return;
            }
            
            $userModel = new User();
            if ($userModel->resetPassword($token, $new_password)) {
                $success = "Password berhasil direset. Silakan login.";
                include_once __DIR__ . '/../views/auth/login.php';
            } else {
                $error = "Token tidak valid atau sudah kadaluarsa.";
                include_once __DIR__ . '/../views/auth/reset_password.php';
            }
            return;
        }
        
        include_once __DIR__ . '/../views/auth/reset_password.php';
    }
}
