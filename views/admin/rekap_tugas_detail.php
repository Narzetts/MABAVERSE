<?php
$title = 'Rekap: ' . $tugas['judul'];
include __DIR__ . '/../layouts/header.php';

$jumlah_masuk = count($pengumpulan);
$jumlah_terlambat = 0;
$jumlah_diterima = 0;

foreach ($pengumpulan as $p) {
    if ($p['status'] === 'terlambat') {
        $jumlah_terlambat++;
    }
    if ($p['status'] === 'diterima') {
        $jumlah_diterima++;
    }
}
?>
<div class="page-head">
    <div>
        <h1><?= e($tugas['judul']) ?></h1>
        <p class="page-sub">
            <?= e($tugas['tahap_judul']) ?> &middot; Deadline <?= e(format_tanggal($tugas['deadline'])) ?>
            &middot;
            <?php if ($tugas['target_kelompok_id']): ?>
            Target kelompok <?= e($tugas['target_kelompok_nama']) ?>
            <?php elseif ($tugas['target_jurusan_id']): ?>
            Target jurusan <?= e($tugas['target_jurusan_nama']) ?>
            <?php elseif ($tugas['target_fakultas_id']): ?>
            Target fakultas <?= e($tugas['target_fakultas_nama']) ?>
            <?php else: ?>
            Target semua peserta
            <?php endif; ?>
        </p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/export-tugas?tugas_id=<?= (int)$tugas['id'] ?>">Export tugas ini (CSV)</a>
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/rekap-tugas">Semua tugas</a>
    </div>
</div>

<div class="stats">
    <div class="stat">
        <p class="stat-label">Pengumpulan masuk</p>
        <p class="stat-value"><?= $jumlah_masuk ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Terlambat</p>
        <p class="stat-value"><?= $jumlah_terlambat ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Diterima</p>
        <p class="stat-value"><?= $jumlah_diterima ?></p>
    </div>
</div>

<?php if (empty($pengumpulan)): ?>
<div class="empty">
    <strong>Belum ada pengumpulan.</strong>
    Data muncul begitu mahasiswa mengunggah file pada tugas ini.
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th scope="col">NIM</th>
                <th scope="col">Nama</th>
                <th scope="col">Dikumpulkan (server)</th>
                <th scope="col">Status</th>
                <th scope="col">Catatan</th>
                <th scope="col">File</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pengumpulan as $p): ?>
            <?php list($status_label, $status_tone) = status_meta($p['status']); ?>
            <tr>
                <td><?= e($p['nim']) ?></td>
                <td><strong><?= e($p['nama']) ?></strong></td>
                <td><?= e(format_tanggal($p['waktu_submit_server'])) ?></td>
                <td><span class="chip chip-<?= $status_tone ?>"><?= e($status_label) ?></span></td>
                <td><?= $p['catatan_panitia'] ? e($p['catatan_panitia']) : '<span class="muted">—</span>' ?></td>
                <td><a href="<?= BASE_URL . e($p['file_path']) ?>" target="_blank" rel="noopener">Lihat file</a></td>
                <td>
                    <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>admin/verifikasi-tugas/<?= (int)$p['id'] ?>">Review</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
