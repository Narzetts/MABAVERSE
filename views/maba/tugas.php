<?php
$title = 'Tugas Ospek';
include __DIR__ . '/../layouts/header.php';

// Petakan pengumpulan per tugas untuk menampilkan status per baris
$pengumpulan_by_tugas = [];
foreach ($riwayat as $r) {
    $pengumpulan_by_tugas[$r['tugas_id']] = $r;
}

$sekarang = date('Y-m-d H:i:s');
?>
<div class="page-head">
    <div>
        <h1>Tugas Ospek</h1>
        <p class="page-sub">Tugas yang ditujukan untuk Anda, beserta status pengumpulannya. Deadline dikunci oleh waktu server.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>riwayat-tugas">Riwayat pengumpulan</a>
    </div>
</div>

<?php if (empty($tugas_list)): ?>
<div class="empty">
    <strong>Belum ada tugas untuk Anda.</strong>
    Tugas muncul di sini setelah admin menambahkannya untuk angkatan, fakultas, jurusan, atau kelompok Anda.
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th scope="col">Tugas</th>
                <th scope="col">Deadline</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tugas_list as $tugas): ?>
            <?php
            $kumpul = $pengumpulan_by_tugas[$tugas['id']] ?? null;
            $status = $kumpul['status'] ?? 'belum_dikumpulkan';
            list($status_label, $status_tone) = status_meta($status);
            $lewat = $sekarang > $tugas['deadline'] && !$kumpul;
            ?>
            <tr>
                <td>
                    <strong><?= e($tugas['judul']) ?></strong><br>
                    <span class="muted small"><?= e($tugas['tahap_judul']) ?></span>
                </td>
                <td>
                    <?= e(format_tanggal($tugas['deadline'])) ?>
                    <?php if ($lewat): ?><br><span class="chip chip-warn">Lewat deadline</span><?php endif; ?>
                </td>
                <td><span class="chip chip-<?= $status_tone ?>"><?= e($status_label) ?></span></td>
                <td>
                    <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>submit-tugas/<?= (int)$tugas['id'] ?>">
                        <?= $kumpul ? 'Ganti file' : 'Kumpulkan' ?>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<p class="field-hint mt-1">File hanya dapat diganti sebelum deadline. Setelah deadline, pengumpulan masih bisa dilakukan tetapi berstatus "Terlambat".</p>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
