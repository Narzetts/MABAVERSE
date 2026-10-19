<?php
$title = 'Pengumpulan: ' . $tugas['judul'];
include __DIR__ . '/../layouts/header.php';

$sudah_kumpul = count($pengumpulan);
$belum_kumpul = max(0, (int)$total_anggota - $sudah_kumpul);
?>
<div class="page-head">
    <div>
        <h1><?= e($tugas['judul']) ?></h1>
        <p class="page-sub">
            <?= e($tugas['tahap_judul']) ?> &middot; Deadline <?= e(format_tanggal($tugas['deadline'])) ?>
        </p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>panitia/tugas">Semua tugas</a>
    </div>
</div>

<div class="stats">
    <div class="stat">
        <p class="stat-label">Anggota kelompok</p>
        <p class="stat-value"><?= (int)$total_anggota ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Sudah mengumpulkan</p>
        <p class="stat-value"><?= $sudah_kumpul ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Belum mengumpulkan</p>
        <p class="stat-value"><?= $belum_kumpul ?></p>
    </div>
</div>

<?php if (empty($pengumpulan)): ?>
<div class="empty">
    <strong>Belum ada pengumpulan dari kelompok Anda.</strong>
    Data muncul begitu anggota mengunggah file di halaman tugas masing-masing.
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
                <td><a href="<?= BASE_URL . e($p['file_path']) ?>" target="_blank" rel="noopener">Lihat file</a></td>
                <td>
                    <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>panitia/verifikasi-tugas/<?= (int)$p['id'] ?>">Review</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
