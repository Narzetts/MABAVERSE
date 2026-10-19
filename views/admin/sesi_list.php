<?php
$title = 'Kelola Sesi';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Kelola Sesi</h1>
    </div>
</div>

<?php if (empty($timeline)): ?>
<div class="empty">
    <strong>Belum ada tahap.</strong>
    Buat tahap dulu di menu Timeline, baru tambahkan sesi di dalamnya.
</div>
<?php else: ?>
<?php foreach ($timeline as $tahap): ?>
<div class="card">
    <div class="card-head">
        <h2><?= e($tahap['judul']) ?></h2>
        <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>admin/sesi/<?= (int)$tahap['id'] ?>">Kelola sesi tahap ini</a>
    </div>
    <p class="muted small mb-0">
        <?= e(format_tanggal($tahap['tanggal_mulai'], false)) ?> &ndash; <?= e(format_tanggal($tahap['tanggal_selesai'], false)) ?>
        &middot; tingkat <?= e($tahap['target_level']) ?>
    </p>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
