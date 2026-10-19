<?php
$title = $title ?? 'Halaman Tidak Ditemukan';
$message = $message ?? 'Halaman yang Anda cari tidak tersedia atau sudah dipindahkan.';
include __DIR__ . '/../layouts/header.php';
?>
<div class="error-page">
    <p class="error-code">404</p>
    <h1><?= e($title) ?></h1>
    <p class="muted"><?= e($message) ?></p>
    <p class="mt-3">
        <a class="btn" href="<?= BASE_URL ?>">Kembali ke halaman utama</a>
    </p>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
