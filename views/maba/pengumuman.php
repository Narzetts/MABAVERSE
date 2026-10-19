<?php
$title = 'Pengumuman';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Pengumuman</h1>
        <p class="page-sub">Pengumuman dari panitia dan admin. Yang belum dibaca ditandai garis samping.</p>
    </div>
</div>

<?php if (empty($pengumuman)): ?>
<div class="empty">
    <strong>Belum ada pengumuman.</strong>
    Semua pengumuman untuk angkatan, fakultas, jurusan, dan kelompok Anda akan tampil di sini.
</div>
<?php else: ?>

<div class="card">
    <div class="form-grid">
        <div class="field mb-0">
            <label for="cari-pengumuman">Cari pengumuman</label>
            <input type="text" id="cari-pengumuman" data-filter-input=".filter-item" placeholder="Kata kunci judul atau isi">
        </div>
        <div class="field mb-0">
            <label for="filter-prioritas">Filter prioritas</label>
            <select id="filter-prioritas" data-filter-priority>
                <option value="">Semua prioritas</option>
                <option value="mendesak">Mendesak</option>
                <option value="penting">Penting</option>
                <option value="normal">Normal</option>
            </select>
        </div>
    </div>
</div>

<div id="filter-kosong" class="empty" style="display: none;">
    <strong>Tidak ada hasil yang cocok.</strong>
    Ubah kata kunci atau filter prioritas.
</div>

<?php foreach ($pengumuman as $p): ?>
<?php list($prio_label, $prio_tone) = status_meta($p['prioritas']); ?>
<div class="pengumuman-item filter-item<?= (int)$p['is_read'] === 0 ? ' is-unread' : '' ?>" data-prioritas="<?= e($p['prioritas']) ?>">
    <div class="pengumuman-head">
        <h2><a href="<?= BASE_URL ?>detail-pengumuman/<?= (int)$p['id'] ?>"><?= e($p['judul']) ?></a></h2>
        <span>
            <?php if ($p['prioritas'] !== 'normal'): ?>
            <span class="chip chip-<?= $prio_tone ?>"><?= e($prio_label) ?></span>
            <?php endif; ?>
        </span>
    </div>
    <p class="muted small mb-0">
        <?= e(format_tanggal($p['created_at'])) ?> oleh <?= e($p['created_by_nama']) ?>
        <?php if ((int)$p['is_read'] === 0): ?>
        &middot; <strong>Belum dibaca</strong>
        <?php endif; ?>
    </p>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
