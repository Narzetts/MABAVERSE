<?php
$title = 'Absensi: ' . $sesi['judul'];
include __DIR__ . '/../layouts/header.php';

$sudah_absen = count($absensi);
$belum_absen = max(0, (int)$stats['total_anggota'] - $sudah_absen);
?>
<div class="page-head">
    <div>
        <h1><?= e($sesi['judul']) ?></h1>
        <p class="page-sub">
            <?= e(format_tanggal($sesi['tanggal'], false)) ?>,
            <?= e(date('H:i', strtotime($sesi['jam_mulai']))) ?>&ndash;<?= e(date('H:i', strtotime($sesi['jam_selesai']))) ?> WIB
            &middot; <?= e($sesi['lokasi_text']) ?> &middot; radius <?= (int)$sesi['radius_meter'] ?> m
        </p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>panitia/absensi">Semua sesi</a>
    </div>
</div>

<div class="stats">
    <div class="stat">
        <p class="stat-label">Anggota</p>
        <p class="stat-value"><?= (int)$stats['total_anggota'] ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Sudah absen</p>
        <p class="stat-value"><?= $sudah_absen ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Di luar radius</p>
        <p class="stat-value"><?= (int)($stats['di_luar_radius'] ?? 0) ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Belum absen</p>
        <p class="stat-value"><?= $belum_absen ?></p>
    </div>
</div>

<?php if (empty($absensi)): ?>
<div class="empty">
    <strong>Belum ada absensi masuk dari kelompok Anda.</strong>
    Absensi anggota muncul di sini begitu mereka mengirim dari halaman absen masing-masing.
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th scope="col">NIM</th>
                <th scope="col">Nama</th>
                <th scope="col">Waktu (server)</th>
                <th scope="col">Jarak</th>
                <th scope="col">Status</th>
                <th scope="col">Foto</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($absensi as $a): ?>
            <?php list($status_label, $status_tone) = status_meta($a['status']); ?>
            <tr>
                <td><?= e($a['nim']) ?></td>
                <td><strong><?= e($a['nama']) ?></strong></td>
                <td><?= e(format_tanggal($a['waktu_submit_server'])) ?></td>
                <td><?= $a['jarak_dari_lokasi_meter'] !== null ? e(number_format((float)$a['jarak_dari_lokasi_meter'], 0, ',', '.')) . ' m' : '-' ?></td>
                <td><span class="chip chip-<?= $status_tone ?>"><?= e($status_label) ?></span></td>
                <td>
                    <?php if (file_exists(__DIR__ . '/../../' . $a['foto_path'])): ?>
                    <a href="<?= BASE_URL . e($a['foto_path']) ?>" target="_blank" rel="noopener">
                        <img class="foto-thumb" src="<?= BASE_URL . e($a['foto_path']) ?>" alt="Foto absensi <?= e($a['nama']) ?>">
                    </a>
                    <?php else: ?>
                    <span class="muted small">Tidak tersedia</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>panitia/verifikasi-absensi/<?= (int)$a['id'] ?>">Verifikasi</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
