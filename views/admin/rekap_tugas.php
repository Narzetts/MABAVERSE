<?php
$title = 'Rekap Tugas';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Rekap Tugas</h1>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/export-tugas">Export semua (CSV)</a>
    </div>
</div>

<?php if (empty($tugas_list)): ?>
<div class="empty">
    <strong>Belum ada tugas.</strong>
    Buat tugas dulu di menu Tugas. Rekap terisi otomatis dari pengumpulan mahasiswa.
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th scope="col">Tugas</th>
                <th scope="col">Tahap</th>
                <th scope="col">Deadline</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tugas_list as $tugas): ?>
            <tr>
                <td><strong><?= e($tugas['judul']) ?></strong></td>
                <td><?= e($tugas['tahap_judul']) ?></td>
                <td><?= e(format_tanggal($tugas['deadline'])) ?></td>
                <td>
                    <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>admin/rekap-tugas/<?= (int)$tugas['id'] ?>">Lihat rekap</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
