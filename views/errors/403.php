<?php
$title = $title ?? 'Akses Ditolak';
$message = $message ?? 'Anda tidak memiliki izin untuk mengakses halaman ini.';
include __DIR__ . '/../layouts/header.php';
?>
<div class="error-page">
    <p class="error-code">403</p>
    <h1><?= e($title) ?></h1>
    <p class="muted"><?= e($message) ?></p>
    <p class="mt-3">
        <a class="btn" href="<?= BASE_URL ?>">Kembali ke halaman utama</a>
    </p>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
