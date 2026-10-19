<?php
$title = 'Pengaturan';
include __DIR__ . '/../layouts/header.php';

$min_tampil = rtrim(rtrim(number_format((float)$min_kehadiran, 1, ',', '.'), '0'), ',');
?>
<div class="page-head">
    <div>
        <h1>Pengaturan</h1>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
    <h2>Ambang minimal kehadiran</h2>
    <p class="muted">
        Persentase kehadiran minimal dari seluruh sesi ospek yang dipakai sistem saat menghitung
        kelengkapan pengesahan. Nilai saat ini: <strong><?= e($min_tampil) ?>%</strong>.
    </p>

    <form method="post" action="<?= BASE_URL ?>admin/pengaturan">
        <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

        <div class="field">
            <label for="min_kehadiran_pengesahan">Minimal kehadiran (%)</label>
            <input type="number" id="min_kehadiran_pengesahan" name="min_kehadiran_pengesahan"
                   min="0" max="100" step="0.5" value="<?= e($min_kehadiran) ?>" required>
        </div>

        <button type="submit" class="btn">Simpan pengaturan</button>
    </form>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
