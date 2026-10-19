<?php
$title = $pengumuman['judul'];
include __DIR__ . '/../layouts/header.php';

list($prio_label, $prio_tone) = status_meta($pengumuman['prioritas']);
$target_text = [
    'semua' => 'Semua peserta',
    'fakultas' => 'Fakultas tertentu',
    'jurusan' => 'Jurusan tertentu',
    'kelompok' => 'Kelompok tertentu',
][$pengumuman['target_level']] ?? $pengumuman['target_level'];
?>
<div class="page-head">
    <div>
        <h1><?= e($pengumuman['judul']) ?></h1>
        <p class="page-sub">
            <?= e(format_tanggal($pengumuman['created_at'])) ?> oleh <?= e($pengumuman['created_by_nama']) ?>
        </p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>pengumuman">Semua pengumuman</a>
    </div>
</div>

<div class="card">
    <p class="mb-2">
        <?php if ($pengumuman['prioritas'] !== 'normal'): ?>
        <span class="chip chip-<?= $prio_tone ?>"><?= e($prio_label) ?></span>
        <?php endif; ?>
        <span class="chip chip-neutral">Tujuan: <?= e($target_text) ?></span>
    </p>
    <div class="pengumuman-isi"><?= nl2br(e($pengumuman['isi'])) ?></div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
