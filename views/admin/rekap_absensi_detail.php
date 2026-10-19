<?php
$title = 'Rekap: ' . $sesi['judul'];
include __DIR__ . '/../layouts/header.php';

$filter_aktif = [
    'fakultas_id' => $_GET['fakultas_id'] ?? '',
    'kelompok_id' => $_GET['kelompok_id'] ?? '',
    'status' => $_GET['status'] ?? '',
];
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
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/export-absensi?sesi_id=<?= (int)$sesi['id'] ?>">Export sesi ini (CSV)</a>
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/rekap-absensi">Semua sesi</a>
    </div>
</div>

<div class="stats">
    <div class="stat">
        <p class="stat-label">Sudah absen</p>
        <p class="stat-value"><?= (int)($stats['total_mahasiswa'] - $stats['tidak_hadir']) ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Hadir</p>
        <p class="stat-value"><?= (int)$stats['hadir'] ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Di luar radius</p>
        <p class="stat-value"><?= (int)$stats['di_luar_radius'] ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Terlambat</p>
        <p class="stat-value"><?= (int)$stats['terlambat'] ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Izin</p>
        <p class="stat-value"><?= (int)$stats['izin'] ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Belum absen</p>
        <p class="stat-value"><?= (int)$stats['tidak_hadir'] ?></p>
    </div>
</div>

<div class="card">
    <form method="get" action="<?= BASE_URL ?>admin/rekap-absensi/<?= (int)$sesi['id'] ?>">
        <div class="form-grid">
            <div class="field">
                <label for="fakultas_id">Filter fakultas</label>
                <select id="fakultas_id" name="fakultas_id">
                    <option value="">Semua fakultas</option>
                    <?php foreach ($fakultas as $f): ?>
                    <option value="<?= (int)$f['id'] ?>" <?= (string)$filter_aktif['fakultas_id'] === (string)$f['id'] ? 'selected' : '' ?>><?= e($f['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="kelompok_id">Filter kelompok</label>
                <select id="kelompok_id" name="kelompok_id">
                    <option value="">Semua kelompok</option>
                    <?php foreach ($kelompok as $k): ?>
                    <option value="<?= (int)$k['id'] ?>" <?= (string)$filter_aktif['kelompok_id'] === (string)$k['id'] ? 'selected' : '' ?>><?= e($k['nama']) ?> &middot; <?= e($k['jurusan_nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="status">Filter status</label>
                <select id="status" name="status">
                    <option value="">Semua status</option>
                    <?php foreach (['hadir', 'di_luar_radius', 'terlambat', 'izin', 'tidak_hadir'] as $opt): ?>
                    <?php list($opt_label) = status_meta($opt); ?>
                    <option value="<?= e($opt) ?>" <?= $filter_aktif['status'] === $opt ? 'selected' : '' ?>><?= e($opt_label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field" style="align-self: end;">
                <button type="submit" class="btn btn-outline">Terapkan filter</button>
            </div>
        </div>
    </form>
</div>

<?php if (empty($absensi)): ?>
<div class="empty">
    <strong>Tidak ada data absensi<?= array_filter($filter_aktif) ? ' untuk filter ini' : '' ?>.</strong>
    <?php if (array_filter($filter_aktif)): ?>
    Kosongkan filter untuk melihat semua absensi pada sesi ini.
    <?php else: ?>
    Belum ada mahasiswa yang mengirim absensi pada sesi ini.
    <?php endif; ?>
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th scope="col">NIM</th>
                <th scope="col">Nama</th>
                <th scope="col">Kelompok</th>
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
                <td><?= e($a['kelompok_nama'] ?? '-') ?></td>
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
                    <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>admin/verifikasi-absensi/<?= (int)$a['id'] ?>">Verifikasi</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
