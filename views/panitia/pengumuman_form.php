<?php
$title = 'Buat Pengumuman Kelompok';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Buat Pengumuman Kelompok</h1>
        <p class="page-sub">Pengumuman ini hanya terlihat oleh anggota kelompok Anda.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>panitia/pengumuman">Kembali</a>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
    <form method="post" action="<?= BASE_URL ?>panitia/tambah-pengumuman">
        <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

        <div class="field">
            <label for="judul">Judul</label>
            <input type="text" id="judul" name="judul" required>
        </div>

        <div class="field">
            <label for="isi">Isi pengumuman</label>
            <textarea id="isi" name="isi" required placeholder="Tulis informasi lengkap: apa, kapan, di mana, dan apa yang harus dilakukan anggota."></textarea>
        </div>

        <div class="field">
            <label for="prioritas">Prioritas</label>
            <select id="prioritas" name="prioritas">
                <option value="normal">Normal</option>
                <option value="penting">Penting</option>
                <option value="mendesak">Mendesak</option>
            </select>
        </div>

        <p class="field-hint">Target otomatis: kelompok binaan Anda.</p>

        <button type="submit" class="btn">Kirim pengumuman</button>
    </form>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
