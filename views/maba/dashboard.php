<?php
$title = 'Dashboard';
include __DIR__ . '/../layouts/header.php';

$hadir_total = (int)$absensi_stats['hadir'] + (int)$absensi_stats['di_luar_radius'] + (int)$absensi_stats['terlambat'];
$total_sesi = (int)$absensi_stats['total_sesi'];
$persen_hadir = $total_sesi > 0 ? round($hadir_total / $total_sesi * 100) : null;

$pengumuman_terbaru = array_slice($pengumuman, 0, 3);

list($pengesahan_label, $pengesahan_tone) = status_meta($pengesahan['status'] ?? 'belum_memenuhi');
?>
<div class="page-head">
    <div>
        <h1>Halo, <?= e($user['nama']) ?></h1>
        <p class="page-sub">
            <?= e($user['fakultas_nama'] ?? 'Fakultas belum diatur') ?>
            <?= $user['jurusan_nama'] ? ' — ' . e($user['jurusan_nama']) : '' ?>
            <?= $user['kelompok_nama'] ? ' — ' . e($user['kelompok_nama']) : '' ?>
        </p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>timeline">Lihat timeline</a>
    </div>
</div>

<?php if (!empty($upcoming_sesi)): ?>
<?php $sesi_berikut = $upcoming_sesi[0]; ?>
<div class="card">
    <div class="card-head">
        <h2>Jadwal terdekat</h2>
        <span class="chip chip-brand"><?= e($sesi_berikut['tahap_judul']) ?></span>
    </div>
    <h3><?= e($sesi_berikut['judul']) ?></h3>
    <ul class="meta-list">
        <li><span class="meta-label">Waktu</span> <?= e(format_tanggal($sesi_berikut['tanggal'] . ' ' . $sesi_berikut['jam_mulai'])) ?> s.d. <?= e(date('H:i', strtotime($sesi_berikut['jam_selesai']))) ?> WIB</li>
        <li><span class="meta-label">Lokasi</span> <?= e($sesi_berikut['lokasi_text']) ?></li>
        <?php if ($sesi_berikut['pic']): ?>
        <li><span class="meta-label">PIC</span> <?= e($sesi_berikut['pic']) ?></li>
        <?php endif; ?>
    </ul>
    <p class="mt-2 action-row">
        <a class="btn" href="<?= BASE_URL ?>detail-sesi/<?= (int)$sesi_berikut['id'] ?>">Buka sesi dan absen</a>
        <?php if (count($upcoming_sesi) > 1): ?>
        <span class="muted small"><?= count($upcoming_sesi) - 1 ?> sesi lain setelah ini</span>
        <?php endif; ?>
    </p>
</div>
<?php else: ?>
<div class="empty">
    <strong>Belum ada sesi yang terjadwal setelah waktu sekarang.</strong>
    Jadwal baru akan muncul di sini begitu admin menambahkannya. Sementara itu, cek timeline untuk rangkaian lengkap ospek.
</div>
<?php endif; ?>

<div class="grid-2 mt-2">
    <div class="card">
        <div class="card-head">
            <h2>Kehadiran saya</h2>
            <a class="small" href="<?= BASE_URL ?>timeline">Detail per sesi</a>
        </div>
        <?php if ($total_sesi > 0): ?>
        <p class="stat-value"><?= $persen_hadir ?>% <small>dari <?= $total_sesi ?> sesi</small></p>
        <div class="progress" role="img" aria-label="Kehadiran <?= (int)$persen_hadir ?> persen">
            <span style="width: <?= (int)$persen_hadir ?>%"></span>
        </div>
        <ul class="meta-list">
            <li><span class="meta-label">Hadir</span> <?= (int)$absensi_stats['hadir'] ?> sesi</li>
            <li><span class="meta-label">Di luar radius</span> <?= (int)$absensi_stats['di_luar_radius'] ?> sesi</li>
            <li><span class="meta-label">Terlambat</span> <?= (int)$absensi_stats['terlambat'] ?> sesi</li>
            <li><span class="meta-label">Belum absen</span> <?= (int)$absensi_stats['tidak_hadir'] ?> sesi</li>
        </ul>
        <?php else: ?>
        <p class="muted">Belum ada sesi yang diselenggarakan. Rekap kehadiran muncul setelah sesi pertama dimulai.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Status pengesahan</h2>
            <a class="small" href="<?= BASE_URL ?>status-pengesahan">Lihat checklist</a>
        </div>
        <p><span class="chip chip-<?= $pengesahan_tone ?>"><?= e($pengesahan_label) ?></span></p>
        <?php if ($pengesahan): ?>
        <p class="muted small mb-0">Terakhir diproses <?= e(format_tanggal($pengesahan['diproses_at'])) ?>.</p>
        <?php else: ?>
        <p class="muted small mb-0">Belum diproses admin. Checklist di halaman pengesahan dihitung langsung dari data Anda, jadi bisa dipantau kapan saja.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Pengumuman terbaru</h2>
        <a class="small" href="<?= BASE_URL ?>pengumuman">Semua pengumuman</a>
    </div>
    <?php if (empty($pengumuman_terbaru)): ?>
    <p class="muted mb-0">Belum ada pengumuman.</p>
    <?php else: ?>
    <?php foreach ($pengumuman_terbaru as $p): ?>
    <?php list($prio_label, $prio_tone) = status_meta($p['prioritas']); ?>
    <div class="pengumuman-item<?= (int)$p['is_read'] === 0 ? ' is-unread' : '' ?>">
        <div class="pengumuman-head">
            <h3><a href="<?= BASE_URL ?>detail-pengumuman/<?= (int)$p['id'] ?>"><?= e($p['judul']) ?></a></h3>
            <span>
                <?php if ($p['prioritas'] !== 'normal'): ?>
                <span class="chip chip-<?= $prio_tone ?>"><?= e($prio_label) ?></span>
                <?php endif; ?>
            </span>
        </div>
        <p class="muted small mb-0"><?= e(format_tanggal($p['created_at'])) ?> oleh <?= e($p['created_by_nama']) ?></p>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
