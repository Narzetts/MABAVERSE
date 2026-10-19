<?php
$title = 'Kelola Timeline';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Kelola Timeline</h1>
    </div>
    <div class="page-actions">
        <a class="btn" href="<?= BASE_URL ?>admin/tambah-tahap">Tambah tahap</a>
    </div>
</div>

<p id="reorder-status" class="muted small" aria-live="polite"></p>

<?php if (empty($timeline)): ?>
<div class="empty">
    <strong>Timeline masih kosong.</strong>
    Tambahkan tahap pertama dengan tombol "Tambah tahap" di atas.
</div>
<?php else: ?>
<div class="table-wrap">
    <table data-reorder data-csrf="<?= e(generateCSRFToken()) ?>" data-endpoint="<?= BASE_URL ?>api/reorderTahap">
        <thead>
            <tr>
                <th scope="col">Urutan</th>
                <th scope="col">Tahap</th>
                <th scope="col">Tingkat</th>
                <th scope="col">Jadwal</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($timeline as $tahap): ?>
            <?php list($status_label, $status_tone) = status_meta($tahap['status']); ?>
            <tr data-id="<?= (int)$tahap['id'] ?>">
                <td>
                    <div class="action-row">
                        <strong><?= (int)$tahap['urutan'] ?></strong>
                        <button type="button" class="btn btn-sm btn-ghost" data-move="up" aria-label="Naikkan urutan <?= e($tahap['judul']) ?>">&uarr;</button>
                        <button type="button" class="btn btn-sm btn-ghost" data-move="down" aria-label="Turunkan urutan <?= e($tahap['judul']) ?>">&darr;</button>
                    </div>
                </td>
                <td>
                    <strong><?= e($tahap['judul']) ?></strong>
                    <?php if ($tahap['lokasi_text']): ?><br><span class="muted small"><?= e($tahap['lokasi_text']) ?></span><?php endif; ?>
                </td>
                <td><?= e($tahap['target_level']) ?></td>
                <td>
                    <?= e(format_tanggal($tahap['tanggal_mulai'], false)) ?> &ndash;<br>
                    <?= e(format_tanggal($tahap['tanggal_selesai'], false)) ?>
                </td>
                <td><span class="chip chip-<?= $status_tone ?>"><?= e($status_label) ?></span></td>
                <td>
                    <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>admin/edit-tahap/<?= (int)$tahap['id'] ?>">Edit</a>
                    <a class="btn btn-sm btn-ghost" href="<?= BASE_URL ?>admin/hapus-tahap/<?= (int)$tahap['id'] ?>"
                       data-confirm="Hapus tahap &quot;<?= e($tahap['judul']) ?>&quot;? Semua sesi dan tugas di dalamnya ikut terhapus.">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
