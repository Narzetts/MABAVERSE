<?php
$title = 'Tugas Kelompok';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Tugas Ospek</h1>
        <p class="page-sub">Pilih tugas untuk melihat pengumpulan dari anggota kelompok Anda.</p>
    </div>
</div>

<?php if (empty($tugas_list)): ?>
<div class="empty">
    <strong>Belum ada tugas.</strong>
    Tugas dibuat admin di menu Tugas. Setelah itu daftar di sini terisi otomatis.
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th scope="col">Tugas</th>
                <th scope="col">Tahap</th>
                <th scope="col">Deadline</th>
                <th scope="col">Target</th>
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
                    <?php if ($tugas['target_kelompok_id']): ?>Kelompok tertentu
                    <?php elseif ($tugas['target_jurusan_id']): ?>Jurusan tertentu
                    <?php elseif ($tugas['target_fakultas_id']): ?>Fakultas tertentu
                    <?php else: ?>Semua peserta<?php endif; ?>
                </td>
                <td>
                    <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>panitia/tugas/<?= (int)$tugas['id'] ?>">Lihat pengumpulan</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
