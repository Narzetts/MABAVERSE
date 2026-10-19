<?php
$title = 'Ubah Password';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Ubah Password</h1>
        <p class="page-sub">Gunakan password yang tidak dipakai di layanan lain.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>profil">Kembali ke profil</a>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
<div class="alert alert-success" role="status"><?= e($success) ?></div>
<?php endif; ?>

<div class="card">
    <form method="post" action="<?= BASE_URL ?>ubah-password">
        <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

        <div class="field">
            <label for="old_password">Password lama</label>
            <input type="password" id="old_password" name="old_password" required autocomplete="current-password">
        </div>

        <div class="field">
            <label for="new_password">Password baru</label>
            <input type="password" id="new_password" name="new_password" required minlength="6" autocomplete="new-password">
            <p class="field-hint">Minimal 6 karakter.</p>
        </div>

        <div class="field">
            <label for="confirm_password">Ulangi password baru</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6" autocomplete="new-password">
        </div>

        <button type="submit" class="btn">Simpan password baru</button>
    </form>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
