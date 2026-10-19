<?php
$title = 'Review Tugas';
include __DIR__ . '/../layouts/header.php';

list($status_label, $status_tone) = status_meta($pengumpulan['status']);
?>
<div class="page-head">
    <div>
        <h1>Review Tugas</h1>
        <p class="page-sub"><?= e($pengumpulan['nama']) ?> (<?= e($pengumpulan['nim']) ?>) &middot; <?= e($pengumpulan['tugas_judul']) ?></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/rekap-tugas/<?= (int)$pengumpulan['tugas_id'] ?>">Kembali ke rekap tugas</a>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<div class="grid-2">
    <div class="card">
        <h2>File yang dikumpulkan</h2>
        <ul class="meta-list">
            <li><span class="meta-label">Waktu submit</span> <?= e(format_tanggal($pengumpulan['waktu_submit_server'])) ?> (waktu server)</li>
            <li><span class="meta-label">Status</span> <span class="chip chip-<?= $status_tone ?>"><?= e($status_label) ?></span></li>
            <li><span class="meta-label">File</span> <a href="<?= BASE_URL . e($pengumpulan['file_path']) ?>" target="_blank" rel="noopener">Buka file</a></li>
        </ul>
    </div>

    <div class="card">
        <h2>Beri status dan catatan</h2>
        <form method="post" action="<?= BASE_URL ?>admin/verifikasi-tugas/<?= (int)$pengumpulan['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <?php foreach (['terkumpul', 'revisi', 'diterima', 'terlambat'] as $opt): ?>
                    <?php list($opt_label) = status_meta($opt); ?>
                    <option value="<?= e($opt) ?>" <?= $pengumpulan['status'] === $opt ? 'selected' : '' ?>><?= e($opt_label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="catatan">Catatan untuk mahasiswa</label>
                <textarea id="catatan" name="catatan" placeholder="Mis. perbaiki halaman kesimpulan, kirim ulang sebelum deadline"><?= e($pengumpulan['catatan_panitia'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn">Simpan review</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
