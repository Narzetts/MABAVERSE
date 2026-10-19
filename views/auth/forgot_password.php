<?php
$title = 'Lupa Password';
include __DIR__ . '/../layouts/header.php';
?>
<div class="auth-shell">
    <div class="auth-card">
        <h1>Lupa Password</h1>
        <p class="muted">Masukkan email yang terdaftar. Sistem akan membuat tautan reset password untuk Anda.</p>

        <?php if (!empty($message)): ?>
        <div class="alert alert-info" role="status"><?= e($message) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>forgot-password">
            <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autocomplete="email">
            </div>

            <button type="submit" class="btn btn-block">Ajukan reset password</button>
        </form>

        <p class="auth-foot muted small">
            Pengiriman email otomatis belum aktif. Permintaan reset Anda akan diproses manual oleh
            admin ospek: hubungi admin lewat kanal resmi kampus untuk menerima tautan resetnya.
        </p>
        <p class="auth-foot"><a href="<?= BASE_URL ?>login">Kembali ke halaman masuk</a></p>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
