<?php
$title = 'Profil';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Profil</h1>
        <p class="page-sub">Perbarui data kontak dan foto. NIM, fakultas, jurusan, dan kelompok hanya bisa diubah admin.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>ubah-password">Ubah password</a>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
<div class="alert alert-success" role="status"><?= e($success) ?></div>
<?php endif; ?>

<div class="grid-2">
    <div class="card">
        <h2>Data akun</h2>
        <ul class="meta-list">
            <li><span class="meta-label">NIM</span> <?= e($user['nim']) ?></li>
            <li><span class="meta-label">Fakultas</span> <?= e($user['fakultas_nama'] ?? '-') ?></li>
            <li><span class="meta-label">Jurusan</span> <?= e($user['jurusan_nama'] ?? '-') ?></li>
            <li><span class="meta-label">Kelompok</span> <?= e($user['kelompok_nama'] ?? 'Belum diatur') ?></li>
        </ul>
    </div>

    <div class="card">
        <h2>Foto profil</h2>
        <?php if ($user['foto_profil'] && file_exists(__DIR__ . '/../../' . $user['foto_profil'])): ?>
        <img src="<?= BASE_URL . e($user['foto_profil']) ?>" alt="Foto profil <?= e($user['nama']) ?>" style="width: 96px; height: 96px; object-fit: cover; border-radius: var(--radius); border: 1px solid var(--line);">
        <?php else: ?>
        <p class="muted small">Belum ada foto. Avatar inisial dipakai sebagai pengganti:</p>
        <span style="display: inline-flex; width: 96px; height: 96px; align-items: center; justify-content: center; background: var(--brand-soft); color: var(--brand); font-size: 2.2rem; font-weight: 800; border-radius: var(--radius); border: 1px solid var(--line);">
            <?= e(strtoupper(mb_substr($user['nama'], 0, 1))) ?>
        </span>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h2>Ubah data</h2>
    <form method="post" action="<?= BASE_URL ?>profil" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

        <div class="form-grid">
            <div class="field">
                <label for="nama">Nama lengkap</label>
                <input type="text" id="nama" name="nama" value="<?= e($user['nama']) ?>" required>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($user['email']) ?>" required>
            </div>

            <div class="field">
                <label for="no_kontak_darurat">Nomor kontak darurat</label>
                <input type="text" id="no_kontak_darurat" name="no_kontak_darurat" inputmode="tel" value="<?= e($user['no_kontak_darurat'] ?? '') ?>" placeholder="Nomor yang bisa dihubungi saat darurat">
            </div>

            <div class="field">
                <label for="foto_profil">Ganti foto profil</label>
                <input type="file" id="foto_profil" name="foto_profil" accept="image/jpeg,image/png">
                <p class="field-hint">JPEG/PNG, maksimal 2 MB, minimal 100x100 piksel.</p>
            </div>
        </div>

        <button type="submit" class="btn">Simpan perubahan</button>
    </form>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
