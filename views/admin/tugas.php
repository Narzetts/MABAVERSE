<?php
$title = 'Kelola Tugas';
include __DIR__ . '/../layouts/header.php';

$sekarang = date('Y-m-d H:i:s');
?>
<div class="page-head">
    <div>
        <h1>Kelola Tugas</h1>
    </div>
    <div class="page-actions">
        <a class="btn" href="<?= BASE_URL ?>admin/tambah-tugas">Tambah tugas</a>
    </div>
</div>

<?php if (empty($tugas_list)): ?>
<div class="empty">
    <strong>Belum ada tugas.</strong>
    Tambahkan tugas pertama dengan tombol "Tambah tugas" di atas.
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th scope="col">Tugas</th>
                <th scope="col">Tahap</th>
                <th scope="col">Target</th>
                <th scope="col">Deadline</th>
                <th scope="col">Format</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tugas_list as $tugas): ?>
            <tr>
                <td><strong><?= e($tugas['judul']) ?></strong></td>
                <td><?= e($tugas['tahap_judul']) ?></td>
                <td>
                    <?php if ($tugas['target_kelompok_id']): ?>
                    Kelompok: <?= e($tugas['target_kelompok_nama']) ?>
                    <?php elseif ($tugas['target_jurusan_id']): ?>
                    Jurusan: <?= e($tugas['target_jurusan_nama']) ?>
                    <?php elseif ($tugas['target_fakultas_id']): ?>
                    Fakultas: <?= e($tugas['target_fakultas_nama']) ?>
                    <?php else: ?>
                    Semua peserta
                    <?php endif; ?>
                </td>
                <td>
                    <?= e(format_tanggal($tugas['deadline'])) ?>
                    <?php if ($sekarang > $tugas['deadline']): ?><br><span class="chip chip-neutral">Berakhir</span><?php endif; ?>
                </td>
                <td><?= e($tugas['format_diizinkan']) ?><br><span class="muted small">maks <?= (int)$tugas['ukuran_max_mb'] ?> MB</span></td>
                <td>
                    <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>admin/rekap-tugas/<?= (int)$tugas['id'] ?>">Rekap</a>
                    <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>admin/edit-tugas/<?= (int)$tugas['id'] ?>">Edit</a>
                    <a class="btn btn-sm btn-ghost" href="<?= BASE_URL ?>admin/hapus-tugas/<?= (int)$tugas['id'] ?>"
                       data-confirm="Hapus tugas &quot;<?= e($tugas['judul']) ?>&quot;? Pengumpulan mahasiswa pada tugas ini ikut terhapus.">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
