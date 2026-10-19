<?php
$title = 'Sesi: ' . $tahap['judul'];
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Sesi: <?= e($tahap['judul']) ?></h1>
        <p class="page-sub"><?= e(format_tanggal($tahap['tanggal_mulai'], false)) ?> &ndash; <?= e(format_tanggal($tahap['tanggal_selesai'], false)) ?></p>
    </div>
    <div class="page-actions">
        <a class="btn" href="<?= BASE_URL ?>admin/tambah-sesi/<?= (int)$tahap['id'] ?>">Tambah sesi</a>
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/sesi">Semua tahap</a>
    </div>
</div>

<?php if (empty($sesi_list)): ?>
<div class="empty">
    <strong>Belum ada sesi pada tahap ini.</strong>
    Tambahkan sesi dengan tombol "Tambah sesi". Sesi menentukan kapan absensi dibuka dan radius lokasinya.
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th scope="col">Sesi</th>
                <th scope="col">Jadwal</th>
                <th scope="col">Lokasi</th>
                <th scope="col">Radius</th>
                <th scope="col">PIC</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sesi_list as $sesi): ?>
            <tr>
                <td><strong><?= e($sesi['judul']) ?></strong></td>
                <td>
                    <?= e(format_tanggal($sesi['tanggal'], false)) ?><br>
                    <?= e(date('H:i', strtotime($sesi['jam_mulai']))) ?>&ndash;<?= e(date('H:i', strtotime($sesi['jam_selesai']))) ?>
                </td>
                <td>
                    <?= e($sesi['lokasi_text']) ?>
                    <?php if ($sesi['lokasi_lat'] && $sesi['lokasi_lng']): ?>
                    <br><a class="small" href="https://www.google.com/maps?q=<?= e($sesi['lokasi_lat']) ?>,<?= e($sesi['lokasi_lng']) ?>" target="_blank" rel="noopener">Lihat peta</a>
                    <?php else: ?>
                    <br><span class="muted small">Tanpa titik GPS</span>
                    <?php endif; ?>
                </td>
                <td><?= (int)$sesi['radius_meter'] ?> m</td>
                <td><?= $sesi['pic'] ? e($sesi['pic']) : '<span class="muted">-</span>' ?></td>
                <td>
                    <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>admin/edit-sesi/<?= (int)$sesi['id'] ?>">Edit</a>
                    <a class="btn btn-sm btn-ghost" href="<?= BASE_URL ?>admin/hapus-sesi/<?= (int)$sesi['id'] ?>"
                       data-confirm="Hapus sesi &quot;<?= e($sesi['judul']) ?>&quot;? Absensi pada sesi ini ikut terhapus.">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
