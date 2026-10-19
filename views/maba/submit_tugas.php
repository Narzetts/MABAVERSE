<?php
$title = 'Kumpulkan Tugas';
include __DIR__ . '/../layouts/header.php';

$terkunci = $deadline_lewat && $current_submission;
?>
<div class="page-head">
    <div>
        <h1><?= e($tugas['judul']) ?></h1>
        <p class="page-sub"><?= e($tugas['tahap_judul'] ?? '') ?> &middot; Deadline <?= e(format_tanggal($tugas['deadline'])) ?> (waktu server)</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>tugas">Kembali ke daftar tugas</a>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
    <h2>Deskripsi tugas</h2>
    <p><?= nl2br(e($tugas['deskripsi'])) ?></p>
    <ul class="meta-list">
        <li><span class="meta-label">Format diizinkan</span> <?= e($tugas['format_diizinkan']) ?></li>
        <li><span class="meta-label">Ukuran maksimal</span> <?= (int)$tugas['ukuran_max_mb'] ?> MB</li>
        <li><span class="meta-label">Deadline</span> <?= e(format_tanggal($tugas['deadline'])) ?></li>
    </ul>
</div>

<?php if ($current_submission): ?>
<?php list($status_label, $status_tone) = status_meta($current_submission['status']); ?>
<div class="card">
    <div class="card-head">
        <h2>Status pengumpulan Anda</h2>
        <span class="chip chip-<?= $status_tone ?>"><?= e($status_label) ?></span>
    </div>
    <ul class="meta-list">
        <li><span class="meta-label">Dikumpulkan</span> <?= e(format_tanggal($current_submission['waktu_submit_server'])) ?> (waktu server)</li>
        <li><span class="meta-label">File</span> <a href="<?= BASE_URL . e($current_submission['file_path']) ?>" target="_blank" rel="noopener">Lihat file yang dikumpulkan</a></li>
        <?php if ($current_submission['catatan_panitia']): ?>
        <li><span class="meta-label">Catatan panitia</span> <?= e($current_submission['catatan_panitia']) ?></li>
        <?php endif; ?>
    </ul>
</div>
<?php endif; ?>

<?php if ($terkunci): ?>
<div class="alert alert-warn">
    Deadline sudah lewat. File yang sudah dikumpulkan tidak dapat diganti lagi. Bila panitia meminta revisi, ikuti arahan mereka.
</div>
<?php else: ?>
<div class="card">
    <h2><?= $current_submission ? 'Ganti file tugas' : 'Kumpulkan file tugas' ?></h2>
    <?php if ($deadline_lewat): ?>
    <div class="alert alert-warn">Deadline sudah lewat. Tugas masih dapat dikumpulkan, tetapi otomatis berstatus "Terlambat".</div>
    <?php endif; ?>
    <form method="post" action="<?= BASE_URL ?>submit-tugas/<?= (int)$tugas['id'] ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

        <div class="field">
            <label for="file_tugas">File tugas</label>
            <input type="file" id="file_tugas" name="file_tugas" required>
            <p class="field-hint">Format: <?= e($tugas['format_diizinkan']) ?>. Maksimal <?= (int)$tugas['ukuran_max_mb'] ?> MB. Tipe file divalidasi di server, bukan hanya di browser.</p>
        </div>

        <button type="submit" class="btn"><?= $current_submission ? 'Ganti file' : 'Kirim tugas' ?></button>
    </form>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
