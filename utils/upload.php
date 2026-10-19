<?php
require_once __DIR__ . '/../config/config.php';

class UploadHandler {
    
    // Ekstensi file selalu ditentukan dari MIME hasil verifikasi server,
    // bukan dari nama file kiriman user. Nama asli file tidak pernah dipercaya.
    private static function ekstensiDariMime($mime) {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];
        return $map[$mime] ?? null;
    }
    
    // Baca tipe MIME dari isi file (bukan header Content-Type dari browser).
    private static function mimeTerkandung($path) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $path);
        finfo_close($finfo);
        return $mime;
    }
    
    private static function simpanFile($file, $target_dir, $prefix, $extension) {
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0775, true);
        }
        
        $filename = $prefix . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            return $filename;
        }
        
        throw new Exception('Gagal mengupload file.');
    }
    
    public static function handleFotoAbsensi($file) {
        if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
            throw new Exception('Tipe file tidak diizinkan. Hanya gambar JPEG/PNG.');
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('Ukuran file terlalu besar. Maksimal 10MB.');
        }
        
        $info = getimagesize($file['tmp_name']);
        if (!$info) {
            throw new Exception('File bukan gambar valid.');
        }
        
        $extension = self::ekstensiDariMime($info['mime']);
        if ($extension === null) {
            throw new Exception('Tipe gambar tidak didukung. Gunakan JPEG/PNG.');
        }
        
        $filename = self::simpanFile($file, UPLOAD_PATH . 'absensi/', 'absensi', $extension);
        return 'uploads/absensi/' . $filename;
    }
    
    public static function handleFileTugas($file, $allowed_types = null, $max_bytes = null) {
        $allowed = $allowed_types ?: ALLOWED_DOCUMENT_TYPES;
        $batas = $max_bytes ?: MAX_FILE_SIZE;
        
        if ($file['size'] > $batas) {
            $batas_mb = rtrim(rtrim(number_format($batas / 1048576, 1, ',', '.'), '0'), ',');
            throw new Exception('Ukuran file terlalu besar. Maksimal ' . $batas_mb . 'MB.');
        }
        
        // Verifikasi tipe dari isi file; header Content-Type browser tidak dipakai.
        $mime_type = self::mimeTerkandung($file['tmp_name']);
        if (!in_array($mime_type, $allowed)) {
            throw new Exception('Tipe file tidak diizinkan.');
        }
        
        $extension = self::ekstensiDariMime($mime_type);
        if ($extension === null) {
            throw new Exception('Tipe file tidak didukung sistem.');
        }
        
        $filename = self::simpanFile($file, UPLOAD_PATH . 'tugas/', 'tugas', $extension);
        return 'uploads/tugas/' . $filename;
    }
    
    public static function handleFotoProfil($file) {
        if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
            throw new Exception('Tipe file tidak diizinkan. Hanya gambar JPEG/PNG.');
        }
        
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception('Ukuran file terlalu besar. Maksimal 2MB.');
        }
        
        $info = getimagesize($file['tmp_name']);
        if (!$info) {
            throw new Exception('File bukan gambar valid.');
        }
        
        if ($info[0] < 100 || $info[1] < 100) {
            throw new Exception('Ukuran gambar minimal 100x100 pixel.');
        }
        
        $extension = self::ekstensiDariMime($info['mime']);
        if ($extension === null) {
            throw new Exception('Tipe gambar tidak didukung. Gunakan JPEG/PNG.');
        }
        
        $filename = self::simpanFile($file, UPLOAD_PATH . 'profil/', 'profil', $extension);
        self::compressImage(UPLOAD_PATH . 'profil/' . $filename);
        return 'uploads/profil/' . $filename;
    }
    
    // Belum dipakai alur inti saat ini; tetap diperketat agar aman bila nanti dipakai.
    public static function handleDokumen($file, $category) {
        // Kategori menjadi nama folder: batasi ke huruf/angka/underscore/strip.
        $category = preg_replace('/[^a-z0-9_-]/i', '', (string)$category);
        if ($category === '') {
            throw new Exception('Kategori dokumen tidak valid.');
        }
        
        $allowed = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png'
        ];
        
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('Ukuran file terlalu besar. Maksimal 10MB.');
        }
        
        $mime_type = self::mimeTerkandung($file['tmp_name']);
        if (!in_array($mime_type, $allowed)) {
            throw new Exception('Tipe file tidak diizinkan.');
        }
        
        $extension = self::ekstensiDariMime($mime_type);
        if ($extension === null) {
            throw new Exception('Tipe file tidak didukung sistem.');
        }
        
        $filename = self::simpanFile($file, UPLOAD_PATH . $category . '/', $category, $extension);
        return 'uploads/' . $category . '/' . $filename;
    }
    
    private static function compressImage($source) {
        // Kompresi butuh ekstensi GD. Bila server tidak memuat GD, foto tetap
        // tersimpan tanpa kompresi — jangan sampai fatal error hanya karena itu.
        if (!extension_loaded('gd')) {
            return;
        }
        
        $quality = 80;
        $info = getimagesize($source);
        
        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
            imagejpeg($image, $source, $quality);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
            imagepng($image, $source, 9);
        }
        
        if (isset($image)) {
            imagedestroy($image);
        }
    }
    
    public static function deleteFile($filepath) {
        $full_path = __DIR__ . '/../' . $filepath;
        if (file_exists($full_path) && !is_dir($full_path)) {
            return unlink($full_path);
        }
        return false;
    }
}
