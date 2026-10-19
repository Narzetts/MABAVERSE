<?php
$title = $sesi['judul'];
include __DIR__ . '/../layouts/header.php';

$hari_ini = date('Y-m-d') === $sesi['tanggal'];
?>
<div class="page-head">
    <div>
        <h1><?= e($sesi['judul']) ?></h1>
        <p class="page-sub"><?= e($tahap['judul'] ?? '') ?> &middot; <?= e(format_tanggal($sesi['tanggal'], false)) ?></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>timeline">Kembali ke timeline</a>
    </div>
</div>

<div class="card">
    <ul class="meta-list">
        <li><span class="meta-label">Waktu</span>
            <?= e(date('H:i', strtotime($sesi['jam_mulai']))) ?> &ndash; <?= e(date('H:i', strtotime($sesi['jam_selesai']))) ?> WIB,
            <?= e(format_tanggal($sesi['tanggal'], false)) ?>
            <?php if ($hari_ini): ?><span class="chip chip-brand">Hari ini</span><?php endif; ?>
        </li>
        <li><span class="meta-label">Lokasi</span>
            <?= e($sesi['lokasi_text']) ?>
            <?php if ($sesi['lokasi_lat'] && $sesi['lokasi_lng']): ?>
            &middot; <a href="https://www.google.com/maps?q=<?= e($sesi['lokasi_lat']) ?>,<?= e($sesi['lokasi_lng']) ?>" target="_blank" rel="noopener">Buka di peta</a>
            <?php endif; ?>
        </li>
        <?php if ($sesi['pic']): ?>
        <li><span class="meta-label">PIC</span> <?= e($sesi['pic']) ?></li>
        <?php endif; ?>
        <?php if ($sesi['lokasi_lat'] && $sesi['lokasi_lng']): ?>
        <li><span class="meta-label">Radius absen</span> <?= (int)$sesi['radius_meter'] ?> meter dari titik lokasi</li>
        <?php endif; ?>
    </ul>
</div>

<?php if ($absensi_exists && $absensi_saya): ?>
<?php list($absen_label, $absen_tone) = status_meta($absensi_saya['status']); ?>
<div class="card">
    <div class="card-head">
        <h2>Absensi Anda</h2>
        <span class="chip chip-<?= $absen_tone ?>"><?= e($absen_label) ?></span>
    </div>
    <ul class="meta-list">
        <li><span class="meta-label">Waktu tercatat</span> <?= e(format_tanggal($absensi_saya['waktu_submit_server'])) ?> (waktu server)</li>
        <?php if ($absensi_saya['jarak_dari_lokasi_meter'] !== null): ?>
        <li><span class="meta-label">Jarak dari lokasi</span> <?= e(number_format((float)$absensi_saya['jarak_dari_lokasi_meter'], 0, ',', '.')) ?> meter</li>
        <?php endif; ?>
        <?php if ($absensi_saya['catatan']): ?>
        <li><span class="meta-label">Catatan panitia</span> <?= e($absensi_saya['catatan']) ?></li>
        <?php endif; ?>
    </ul>
    <p class="muted small mb-0 mt-1">Absensi sudah tercatat. Bila ada kekeliruan, hubungi panitia kelompok Anda untuk koreksi.</p>
</div>
<?php else: ?>
<div class="card">
    <div class="card-head">
        <h2>Absensi</h2>
        <span class="chip chip-neutral">Belum absen</span>
    </div>
    <p>Absensi memerlukan foto langsung dari kamera dan lokasi GPS Anda saat mendaftar. Waktu yang dicatat adalah waktu server, bukan waktu perangkat.</p>
    <?php if ($hari_ini): ?>
    <p class="action-row"><a class="btn" href="<?= BASE_URL ?>absen/<?= (int)$sesi['id'] ?>">Absen sekarang</a></p>
    <?php else: ?>
    <p class="muted small mb-0">Sesi ini bukan hari ini (<?= e(format_tanggal($sesi['tanggal'], false)) ?>). Anda tetap bisa membuka halaman absen, tetapi absen yang paling tepat dilakukan saat hari-H.</p>
    <p class="action-row mt-2"><a class="btn" href="<?= BASE_URL ?>absen/<?= (int)$sesi['id'] ?>">Buka halaman absen</a></p>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
