<?php
$title = 'Import Maba (CSV)';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Import Mahasiswa Baru</h1>
        <p class="page-sub">Unggah file CSV untuk menambahkan banyak maba sekaligus.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/users">Kembali ke daftar pengguna</a>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($result !== null): ?>
<div class="alert <?= $result['imported'] > 0 ? 'alert-success' : 'alert-warn' ?>" role="status">
    <strong><?= (int)$result['imported'] ?> baris berhasil diimpor.</strong>
</div>
<?php if (!empty($result['errors'])): ?>
<div class="card">
    <h2>Baris yang gagal (<?= count($result['errors']) ?>)</h2>
    <ul>
        <?php foreach ($result['errors'] as $err): ?>
        <li><?= e($err) ?></li>
        <?php endforeach; ?>
    </ul>
    <p class="muted small mb-0">Penyebab paling umum: NIM atau email sudah terdaftar (kolom NIM dan email harus unik).</p>
</div>
<?php endif; ?>
<?php endif; ?>

<div class="grid-2">
    <div class="card">
        <h2>Unggah file</h2>
        <form method="post" action="<?= BASE_URL ?>admin/import-maba" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

            <div class="field">
                <label for="csv">File CSV</label>
                <input type="file" id="csv" name="csv" accept=".csv" required>
            </div>

            <button type="submit" class="btn">Mulai import</button>
        </form>
    </div>

    <div class="card card-quiet">
        <h2>Format file</h2>
        <p>Baris pertama adalah header (dilewati). Urutan kolom per baris:</p>
        <p><code>nim,nama,email,password,fakultas_id,jurusan_id,kelompok_id,no_kontak_darurat</code></p>
        <ul class="meta-list">
            <li><span class="meta-label">wajib</span> nim, nama, email</li>
            <li><span class="meta-label">opsional</span> password (default <code>mabaverse123</code>), id fakultas/jurusan/kelompok, kontak darurat</li>
            <li><span class="meta-label">contoh baris</span> <code>2026999,Budi Santoso,budi@mail.com,rahasia123,1,1,1,08123456789</code></li>
        </ul>
        <p class="muted small mb-0">ID fakultas, jurusan, dan kelompok bisa dilihat di halaman Pengguna atau dari tabel masing-masing.</p>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
