<?php
$title = 'Dashboard Admin';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Dashboard Rekap</h1>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/export-absensi">Export absensi (CSV)</a>
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/export-tugas">Export tugas (CSV)</a>
    </div>
</div>

<div class="stats">
    <div class="stat">
        <p class="stat-label">Mahasiswa baru</p>
        <p class="stat-value"><?= (int)$stats['total_maba'] ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Panitia</p>
        <p class="stat-value"><?= (int)$stats['total_panitia'] ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Kehadiran keseluruhan</p>
        <p class="stat-value">
            <?= $stats['persentase_kehadiran'] !== null ? e($stats['persentase_kehadiran']) . '%' : '-' ?>
            <?php if ($stats['persentase_kehadiran'] === null): ?><small>belum ada sesi berjalan</small><?php endif; ?>
        </p>
    </div>
    <div class="stat">
        <p class="stat-label">Sesi terjadwal</p>
        <p class="stat-value"><?= (int)$stats['total_sesi'] ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Tugas</p>
        <p class="stat-value"><?= (int)$stats['total_tugas'] ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Pengumuman</p>
        <p class="stat-value"><?= (int)$stats['total_pengumuman'] ?></p>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-head">
            <h2>Kehadiran per sesi terakhir</h2>
        </div>
        <?php if (empty($tren_kehadiran)): ?>
        <p class="muted mb-0">Belum ada sesi yang sudah dimulai, jadi belum ada data kehadiran untuk ditampilkan.</p>
        <?php else: ?>
        <?php foreach ($tren_kehadiran as $tren): ?>
        <?php
        $target = max(1, (int)$tren['target']);
        $persen = round((int)$tren['hadir'] / $target * 100);
        ?>
        <div class="bar-row">
            <span class="bar-name"><?= e($tren['judul']) ?><br><span class="muted small"><?= e(format_tanggal($tren['tanggal'], false)) ?></span></span>
            <span class="bar-track">
                <span class="bar-fill" style="width: <?= min(100, $persen) ?>%"></span>
            </span>
            <span class="bar-value"><?= (int)$tren['hadir'] ?>/<?= (int)$tren['target'] ?> (<?= $persen ?>%)</span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Sesi berikutnya</h2>
            <a class="small" href="<?= BASE_URL ?>admin/sesi">Kelola sesi</a>
        </div>
        <?php if (empty($sesi_terdekat)): ?>
        <p class="muted mb-0">Belum ada sesi terjadwal ke depan. Tambahkan sesi di menu Sesi.</p>
        <?php else: ?>
        <ul class="meta-list">
            <?php foreach ($sesi_terdekat as $s): ?>
            <li>
                <span class="meta-label"><?= e(format_tanggal($s['tanggal'], false)) ?>, <?= e(date('H:i', strtotime($s['jam_mulai']))) ?></span>
                <?= e($s['judul']) ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
