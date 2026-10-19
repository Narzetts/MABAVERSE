<?php
$title = 'Timeline Ospek';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Timeline Ospek</h1>
        <p class="page-sub">Rangkaian kegiatan dari pengambilan KTM sampai awal perkuliahan. Status diperbarui dari waktu server.</p>
    </div>
</div>

<?php if (empty($timeline)): ?>
<div class="empty">
    <strong>Timeline belum diisi.</strong>
    Admin ospek belum menambahkan tahap kegiatan. Coba cek lagi nanti.
</div>
<?php else: ?>
<ol class="timeline">
    <?php foreach ($timeline as $tahap): ?>
    <?php list($tahap_label, $tahap_tone) = status_meta($tahap['status']); ?>
    <li class="tl-item" data-status="<?= e($tahap['status']) ?>">
        <div class="tl-head">
            <h3><?= ($tahap['urutan']) ?>. <?= e($tahap['judul']) ?></h3>
            <span class="chip chip-<?= $tahap_tone ?>"><?= e($tahap_label) ?></span>
            <span class="muted small">Tingkat <?= e($tahap['target_level']) ?></span>
        </div>
        <p class="tl-meta">
            <?= e(format_tanggal($tahap['tanggal_mulai'])) ?>
            &ndash; <?= e(format_tanggal($tahap['tanggal_selesai'])) ?>
            <?php if ($tahap['lokasi_text']): ?> &middot; <?= e($tahap['lokasi_text']) ?><?php endif; ?>
        </p>
        <?php if ($tahap['deskripsi']): ?>
        <p><?= nl2br(e($tahap['deskripsi'])) ?></p>
        <?php endif; ?>

        <?php if (!empty($sesi_by_tahap[$tahap['id']])): ?>
        <ul class="tl-sesi">
            <?php foreach ($sesi_by_tahap[$tahap['id']] as $sesi): ?>
            <li>
                <a class="tl-sesi-link" href="<?= BASE_URL ?>detail-sesi/<?= (int)$sesi['id'] ?>">
                    <span class="tl-sesi-judul"><?= e($sesi['judul']) ?></span>
                    <span class="tl-sesi-waktu">
                        <?= e(format_tanggal($sesi['tanggal'], false)) ?>,
                        <?= e(date('H:i', strtotime($sesi['jam_mulai']))) ?>&ndash;<?= e(date('H:i', strtotime($sesi['jam_selesai']))) ?><br>
                        <?= e($sesi['lokasi_text']) ?>
                    </span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
</ol>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
