<?php
$tahap = $tahap ?? null; // Mode tambah: controller tidak mengirim data lama.
$title = isset($tahap) ? 'Edit Tahap' : 'Tambah Tahap';
include __DIR__ . '/../layouts/header.php';

$is_edit = isset($tahap);
$action_url = $is_edit ? BASE_URL . 'admin/edit-tahap/' . (int)$tahap['id'] : BASE_URL . 'admin/tambah-tahap';

$val = function ($key, $default = '') use ($tahap, $is_edit) {
    return $is_edit && isset($tahap[$key]) ? $tahap[$key] : $default;
};
?>
<div class="page-head">
    <div>
        <h1><?= $is_edit ? 'Edit Tahap' : 'Tambah Tahap' ?></h1>
        <p class="page-sub">Tahap adalah bagian besar timeline ospek, mis. "Ospek Kampus" atau "Pengumpulan Tugas Ospek Fakultas".</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/timeline">Kembali ke timeline</a>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
    <form method="post" action="<?= $action_url ?>">
        <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

        <div class="form-grid">
            <div class="field span-2">
                <label for="judul">Judul tahap</label>
                <input type="text" id="judul" name="judul" value="<?= e($val('judul')) ?>" required>
            </div>

            <div class="field span-2">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" placeholder="Penjelasan singkat kegiatan pada tahap ini"><?= e($val('deskripsi')) ?></textarea>
            </div>

            <div class="field">
                <label for="urutan">Urutan</label>
                <input type="number" id="urutan" name="urutan" min="1" value="<?= e($val('urutan', '1')) ?>" required>
                <p class="field-hint">Angka kecil tampil lebih dulu.</p>
            </div>

            <div class="field">
                <label for="target_level">Tingkat</label>
                <select id="target_level" name="target_level" required>
                    <?php foreach (['universitas', 'fakultas', 'jurusan'] as $lvl): ?>
                    <option value="<?= e($lvl) ?>" <?= $val('target_level') === $lvl ? 'selected' : '' ?>><?= e(ucfirst($lvl)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="tanggal_mulai">Mulai</label>
                <input type="datetime-local" id="tanggal_mulai" name="tanggal_mulai"
                       value="<?= e($is_edit ? date('Y-m-d\TH:i', strtotime($tahap['tanggal_mulai'])) : '') ?>" required>
            </div>

            <div class="field">
                <label for="tanggal_selesai">Selesai</label>
                <input type="datetime-local" id="tanggal_selesai" name="tanggal_selesai"
                       value="<?= e($is_edit ? date('Y-m-d\TH:i', strtotime($tahap['tanggal_selesai'])) : '') ?>" required>
            </div>

            <div class="field span-2">
                <label for="lokasi_text">Lokasi (teks)</label>
                <input type="text" id="lokasi_text" name="lokasi_text" value="<?= e($val('lokasi_text')) ?>" placeholder="Mis. Auditorium Utama">
            </div>
        </div>

        <button type="submit" class="btn"><?= $is_edit ? 'Simpan perubahan' : 'Tambah tahap' ?></button>
    </form>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
