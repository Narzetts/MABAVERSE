<?php
$title = isset($pengumuman) ? 'Edit Pengumuman' : 'Tambah Pengumuman';
include __DIR__ . '/../layouts/header.php';

$is_edit = isset($pengumuman);
$action_url = $is_edit ? BASE_URL . 'admin/edit-pengumuman/' . (int)$pengumuman['id'] : BASE_URL . 'admin/tambah-pengumuman';

// Nilai target saat ini untuk dropdown gabungan
if ($is_edit && $pengumuman['target_level'] === 'fakultas' && $pengumuman['target_id']) {
    $target_saat_ini = 'f:' . $pengumuman['target_id'];
} elseif ($is_edit && $pengumuman['target_level'] === 'jurusan' && $pengumuman['target_id']) {
    $target_saat_ini = 'j:' . $pengumuman['target_id'];
} elseif ($is_edit && $pengumuman['target_level'] === 'kelompok' && $pengumuman['target_id']) {
    $target_saat_ini = 'k:' . $pengumuman['target_id'];
} else {
    $target_saat_ini = 'semua';
}
?>
<div class="page-head">
    <div>
        <h1><?= $is_edit ? 'Edit Pengumuman' : 'Tambah Pengumuman' ?></h1>
        <p class="page-sub">Pengumuman tampil di halaman pengumuman maba sesuai target yang dipilih.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/pengumuman">Kembali</a>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
    <form method="post" action="<?= $action_url ?>">
        <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

        <div class="field">
            <label for="judul">Judul</label>
            <input type="text" id="judul" name="judul" value="<?= e($is_edit ? $pengumuman['judul'] : '') ?>" required>
        </div>

        <div class="field">
            <label for="isi">Isi pengumuman</label>
            <textarea id="isi" name="isi" required placeholder="Apa, kapan, di mana, dan apa yang harus dilakukan"><?= e($is_edit ? $pengumuman['isi'] : '') ?></textarea>
        </div>

        <div class="form-grid">
            <div class="field">
                <label for="prioritas">Prioritas</label>
                <select id="prioritas" name="prioritas">
                    <?php foreach (['normal', 'penting', 'mendesak'] as $opt): ?>
                    <option value="<?= e($opt) ?>" <?= $is_edit && $pengumuman['prioritas'] === $opt ? 'selected' : '' ?>><?= e(ucfirst($opt)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="target">Target penerima</label>
                <select id="target" name="target" required>
                    <option value="semua" <?= $target_saat_ini === 'semua' ? 'selected' : '' ?>>Semua peserta</option>
                    <optgroup label="Fakultas">
                        <?php foreach ($fakultas as $f): ?>
                        <option value="f:<?= (int)$f['id'] ?>" <?= $target_saat_ini === 'f:' . $f['id'] ? 'selected' : '' ?>><?= e($f['nama']) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Jurusan">
                        <?php foreach ($jurusan as $j): ?>
                        <option value="j:<?= (int)$j['id'] ?>" <?= $target_saat_ini === 'j:' . $j['id'] ? 'selected' : '' ?>><?= e($j['nama']) ?> &middot; <?= e($j['fakultas_nama']) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Kelompok">
                        <?php foreach ($kelompok as $k): ?>
                        <option value="k:<?= (int)$k['id'] ?>" <?= $target_saat_ini === 'k:' . $k['id'] ? 'selected' : '' ?>><?= e($k['nama']) ?> &middot; <?= e($k['jurusan_nama']) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>
        </div>

        <button type="submit" class="btn"><?= $is_edit ? 'Simpan perubahan' : 'Kirim pengumuman' ?></button>
    </form>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
