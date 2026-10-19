<?php
$title = 'Masuk';
include __DIR__ . '/../layouts/header.php';
?>
<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-brand">
            <span class="brand">MABAVERSE<span class="brand-sub">Portal Ospek Mahasiswa Baru</span></span>
        </div>

        <?php if (!empty($error)): ?>
        <div class="alert alert-error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
        <div class="alert alert-success" role="status"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>login">
            <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

            <div class="field">
                <label for="nim">NIM / Nomor Pendaftaran</label>
                <input type="text" id="nim" name="nim" required autofocus autocomplete="username">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-block">Masuk</button>
        </form>

        <p class="auth-foot">
            <a href="<?= BASE_URL ?>forgot-password">Lupa password?</a>
        </p>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
