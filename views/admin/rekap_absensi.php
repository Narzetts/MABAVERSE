<?php
$title = 'Rekap Absensi';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Rekap Absensi</h1>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/export-absensi">Export semua (CSV)</a>
    </div>
</div>

<?php if (empty($timeline)): ?>
<div class="empty">
    <strong>Belum ada sesi.</strong>
    Buat tahap dan sesi dulu di menu Timeline dan Sesi. Rekap terisi otomatis dari absensi mahasiswa.
</div>
<?php else: ?>
<?php foreach ($timeline as $tahap): ?>
<?php list($tahap_label, $tahap_tone) = status_meta($tahap['status']); ?>
<div class="card">
    <div class="card-head">
        <h2><?= e($tahap['judul']) ?></h2>
        <span class="chip chip-<?= $tahap_tone ?>"><?= e($tahap_label) ?></span>
    </div>

    <?php if (empty($sesi_by_tahap[$tahap['id']])): ?>
    <p class="muted mb-0">Belum ada sesi pada tahap ini.</p>
    <?php else: ?>
    <ul class="tl-sesi">
        <?php foreach ($sesi_by_tahap[$tahap['id']] as $sesi): ?>
        <li>
            <a class="tl-sesi-link" href="<?= BASE_URL ?>admin/rekap-absensi/<?= (int)$sesi['id'] ?>">
                <span class="tl-sesi-judul"><?= e($sesi['judul']) ?></span>
                <span class="tl-sesi-waktu">
                    <?= e(format_tanggal($sesi['tanggal'], false)) ?>,
                    <?= e(date('H:i', strtotime($sesi['jam_mulai']))) ?>&ndash;<?= e(date('H:i', strtotime($sesi['jam_selesai']))) ?>
                </span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
