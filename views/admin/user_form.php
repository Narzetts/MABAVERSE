<?php
$title = 'Tambah Pengguna';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Tambah Pengguna</h1>
        <p class="page-sub">Untuk menambah banyak maba sekaligus, gunakan import CSV di halaman Pengguna.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/users">Kembali ke daftar pengguna</a>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
    <form method="post" action="<?= BASE_URL ?>admin/tambah-user">
        <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

        <div class="form-grid">
            <div class="field">
                <label for="nim">NIM / Nomor Pendaftaran</label>
                <input type="text" id="nim" name="nim" required>
            </div>

            <div class="field">
                <label for="nama">Nama lengkap</label>
                <input type="text" id="nama" name="nama" required>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="field">
                <label for="password">Password awal</label>
                <input type="password" id="password" name="password" required minlength="6">
                <p class="field-hint">Sampaikan password ini ke pengguna, minta segera menggantinya.</p>
            </div>

            <div class="field">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="maba">Mahasiswa Baru</option>
                    <option value="panitia">Panitia</option>
                    <option value="admin">Admin Ospek</option>
                </select>
            </div>

            <div class="field">
                <label for="fakultas_id">Fakultas</label>
                <select id="fakultas_id" name="fakultas_id">
                    <option value="">-</option>
                    <?php foreach ($fakultas as $f): ?>
                    <option value="<?= (int)$f['id'] ?>"><?= e($f['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="jurusan_id">Jurusan</label>
                <select id="jurusan_id" name="jurusan_id">
                    <option value="">-</option>
                    <?php foreach ($jurusan as $j): ?>
                    <option value="<?= (int)$j['id'] ?>"><?= e($j['nama']) ?> &middot; <?= e($j['fakultas_nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="kelompok_id">Kelompok</label>
                <select id="kelompok_id" name="kelompok_id">
                    <option value="">-</option>
                    <?php foreach ($kelompok as $k): ?>
                    <option value="<?= (int)$k['id'] ?>"><?= e($k['nama']) ?> &middot; <?= e($k['jurusan_nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <button type="submit" class="btn">Tambah pengguna</button>
    </form>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
