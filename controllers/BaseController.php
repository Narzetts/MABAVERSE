<?php
class BaseController {
    
    public function forbidden() {
        http_response_code(403);
        $title = 'Akses Ditolak';
        $message = 'Anda tidak memiliki izin untuk mengakses halaman ini.';
        include_once __DIR__ . '/../views/errors/403.php';
    }
    
    public function notFound() {
        http_response_code(404);
        $title = 'Halaman Tidak Ditemukan';
        $message = 'Halaman yang Anda cari tidak tersedia atau sudah dipindahkan.';
        include_once __DIR__ . '/../views/errors/404.php';
    }
}
