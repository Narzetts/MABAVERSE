<?php
$title = 'Riwayat Tugas';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Riwayat Pengumpulan Tugas</h1>
        <p class="page-sub">Semua tugas yang pernah Anda kumpulkan, dengan waktu server dan catatan panitia.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>tugas">Daftar tugas</a>
    </div>
</div>

<?php if (empty($riwayat)): ?>
<div class="empty">
    <strong>Belum ada pengumpulan.</strong>
    Setelah Anda mengumpulkan tugas pertama, riwayatnya muncul di sini.
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th scope="col">Tugas</th>
                <th scope="col">Tahap</th>
                <th scope="col">Dikumpulkan</th>
                <th scope="col">Status</th>
                <th scope="col">Catatan</th>
                <th scope="col">File</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($riwayat as $row): ?>
            <?php list($status_label, $status_tone) = status_meta($row['status']); ?>
            <tr>
                <td><strong><?= e($row['tugas_judul']) ?></strong></td>
                <td><?= e($row['tahap_judul']) ?></td>
                <td><?= e(format_tanggal($row['waktu_submit_server'])) ?></td>
                <td><span class="chip chip-<?= $status_tone ?>"><?= e($status_label) ?></span></td>
                <td><?= $row['catatan_panitia'] ? e($row['catatan_panitia']) : '<span class="muted">—</span>' ?></td>
                <td><a href="<?= BASE_URL . e($row['file_path']) ?>" target="_blank" rel="noopener">Lihat</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
