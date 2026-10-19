<?php
// Halaman error generik 500. Sengaja berdiri sendiri tanpa layout aplikasi:
// saat halaman ini ditampilkan (biasanya masalah database atau exception),
// komponen layout yang butuh database tidak boleh ikut dipanggil.
// Tidak boleh memuat pesan exception, stack trace, atau path server.
if (!headers_sent()) {
    http_response_code(500);
}

$base_url = defined('BASE_URL') ? BASE_URL : '/';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Terjadi Kesalahan | MABAVERSE</title>
<style>
    body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; max-width: 640px; margin: 3rem auto; padding: 0 1rem; color: #241F19; background: #F6F1E9; }
    h1 { font-size: 1.4rem; }
    p { line-height: 1.6; }
    .kode { font-size: 2.4rem; font-weight: 800; color: #7A1F2B; margin: 0; }
    .btn { display: inline-block; background: #7A1F2B; color: #fff; text-decoration: none; padding: 0.6rem 1rem; border-radius: 8px; }
</style>
</head>
<body>
<p class="kode">500</p>
<h1>Terjadi kesalahan tak terduga</h1>
<p>Permintaan Anda tidak dapat diproses saat ini. Silakan coba muat ulang beberapa saat lagi.
Bila masalah berlanjut, hubungi admin ospek dan sebutkan waktu kejadiannya.</p>
<p><a class="btn" href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>">Kembali ke halaman utama</a></p>
</body>
</html>
