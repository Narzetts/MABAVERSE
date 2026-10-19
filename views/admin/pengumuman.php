<?php
$title = 'Kelola Pengumuman';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Kelola Pengumuman</h1>
    </div>
    <div class="page-actions">
        <a class="btn" href="<?= BASE_URL ?>admin/tambah-pengumuman">Tambah pengumuman</a>
    </div>
</div>

<?php if (empty($pengumuman)): ?>
<div class="empty">
    <strong>Belum ada pengumuman.</strong>
    Tambahkan pengumuman pertama dengan tombol di atas.
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
    <p class="action-row mb-0">
        <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>admin/edit-pengumuman/<?= (int)$p['id'] ?>">Edit</a>
        <a class="btn btn-sm btn-ghost" href="<?= BASE_URL ?>admin/hapus-pengumuman/<?= (int)$p['id'] ?>"
           data-confirm="Hapus pengumuman &quot;<?= e($p['judul']) ?>&quot;?">Hapus</a>
    </p>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
