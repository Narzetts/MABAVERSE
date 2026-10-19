<?php
$title = 'Anggota Kelompok';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Anggota Kelompok</h1>
        <p class="page-sub">Data anggota kelompok binaan Anda. Perubahan data dilakukan admin.</p>
    </div>
</div>

<?php if (empty($maba)): ?>
<div class="empty">
    <strong>Belum ada anggota.</strong>
    Anggota muncul setelah admin mengimpor data maba dan menempatkannya ke kelompok ini.
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th scope="col">NIM</th>
                <th scope="col">Nama</th>
                <th scope="col">Jurusan</th>
                <th scope="col">Kontak darurat</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($maba as $m): ?>
            <tr>
                <td><?= e($m['nim']) ?></td>
                <td><strong><?= e($m['nama']) ?></strong></td>
                <td><?= e($m['jurusan_nama'] ?? '-') ?></td>
                <td><?= $m['no_kontak_darurat'] ? e($m['no_kontak_darurat']) : '<span class="muted">Belum diisi</span>' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
