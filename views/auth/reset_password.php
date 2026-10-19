<?php
$title = 'Reset Password';
$token = $token ?? ($_GET['token'] ?? '');
include __DIR__ . '/../layouts/header.php';
?>
<div class="auth-shell">
    <div class="auth-card">
        <h1>Reset Password</h1>
        <p class="muted">Masukkan password baru untuk akun Anda. Tautan reset berlaku 1 jam.</p>

        <?php if (!empty($error)): ?>
        <div class="alert alert-error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>reset-password?token=<?= e($token) ?>">
            <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

            <div class="field">
                <label for="new_password">Password baru</label>
                <input type="password" id="new_password" name="new_password" required minlength="6" autocomplete="new-password">
                <p class="field-hint">Minimal 6 karakter.</p>
            </div>

            <div class="field">
                <label for="confirm_password">Ulangi password baru</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6" autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-block">Simpan password baru</button>
        </form>

        <p class="auth-foot"><a href="<?= BASE_URL ?>login">Kembali ke halaman masuk</a></p>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
