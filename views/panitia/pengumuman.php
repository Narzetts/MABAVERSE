<?php
$title = 'Pengumuman Kelompok';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Pengumuman</h1>
        <p class="page-sub">Pengumuman kelompok yang Anda buat sendiri maupun dari admin.</p>
    </div>
    <div class="page-actions">
        <a class="btn" href="<?= BASE_URL ?>panitia/tambah-pengumuman">Buat pengumuman kelompok</a>
    </div>
</div>

<?php if (empty($pengumuman)): ?>
<div class="empty">
    <strong>Belum ada pengumuman.</strong>
    Buat pengumuman pertama untuk kelompok Anda lewat tombol di atas.
</div>
<?php else: ?>
<?php foreach ($pengumuman as $p): ?>
<?php
list($prio_label, $prio_tone) = status_meta($p['prioritas']);
$target_text = [
    'semua' => 'Semua peserta',
    'fakultas' => 'Fakultas tertentu',
    'jurusan' => 'Jurusan tertentu',
    'kelompok' => 'Kelompok tertentu',
][$p['target_level']] ?? $p['target_level'];
?>
<div class="pengumuman-item">
    <div class="pengumuman-head">
        <h2><?= e($p['judul']) ?></h2>
        <span>
            <?php if ($p['prioritas'] !== 'normal'): ?>
            <span class="chip chip-<?= $prio_tone ?>"><?= e($prio_label) ?></span>
            <?php endif; ?>
            <span class="chip chip-neutral"><?= e($target_text) ?></span>
        </span>
    </div>
    <p class="muted small mb-0"><?= e(format_tanggal($p['created_at'])) ?> oleh <?= e($p['created_by_nama']) ?></p>
    <p class="pengumuman-isi"><?= nl2br(e($p['isi'])) ?></p>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
