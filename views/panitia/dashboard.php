<?php
$title = 'Dashboard Panitia';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Kelompok Binaan</h1>
        <p class="page-sub">
            <?php if ($kelompok): ?>
            <?= e($kelompok['nama']) ?> &middot; <?= e($kelompok['jurusan_nama']) ?> &middot; <?= count($maba_kelompok) ?> anggota
            <?php else: ?>
            Anda belum terhubung ke kelompok mana pun
            <?php endif; ?>
        </p>
    </div>
    <div class="page-actions">
        <?php if ($kelompok): ?>
        <a class="btn btn-outline" href="<?= BASE_URL ?>panitia/kelompok">Daftar anggota</a>
        <?php endif; ?>
    </div>
</div>

<?php if (!$kelompok): ?>
<div class="empty">
    <strong>Kelompok belum diatur.</strong>
    Admin belum menautkan akun Anda sebagai pendamping kelompok di tabel kelompok (kolom panitia_id). Hubungi admin ospek.
</div>
<?php else: ?>

<div class="stats">
    <div class="stat">
        <p class="stat-label">Anggota kelompok</p>
        <p class="stat-value"><?= count($maba_kelompok) ?></p>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Jadwal terdekat</h2>
        <a class="small" href="<?= BASE_URL ?>panitia/absensi">Verifikasi absensi</a>
    </div>
    <?php if (empty($sesi_terdekat)): ?>
    <p class="muted mb-0">Belum ada sesi terjadwal.</p>
    <?php else: ?>
    <ul class="meta-list">
        <?php foreach ($sesi_terdekat as $s): ?>
        <li>
            <span class="meta-label"><?= e(format_tanggal($s['tanggal'], false)) ?></span>
            <?= e($s['judul']) ?>, <?= e(date('H:i', strtotime($s['jam_mulai']))) ?>&ndash;<?= e(date('H:i', strtotime($s['jam_selesai']))) ?>
            &middot; <?= e($s['lokasi_text']) ?>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-head">
        <h2>Anggota</h2>
        <a class="small" href="<?= BASE_URL ?>panitia/kelompok">Lihat semua</a>
    </div>
    <?php if (empty($maba_kelompok)): ?>
    <p class="muted mb-0">Belum ada mahasiswa yang masuk ke kelompok ini.</p>
    <?php else: ?>
    <ul class="meta-list">
        <?php foreach (array_slice($maba_kelompok, 0, 6) as $m): ?>
        <li><span class="meta-label"><?= e($m['nim']) ?></span> <?= e($m['nama']) ?></li>
        <?php endforeach; ?>
    </ul>
    <?php if (count($maba_kelompok) > 6): ?>
    <p class="muted small mt-1 mb-0">Dan <?= count($maba_kelompok) - 6 ?> anggota lainnya.</p>
    <?php endif; ?>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
