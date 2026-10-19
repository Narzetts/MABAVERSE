<?php
$tugas = $tugas ?? null; // Mode tambah: controller tidak mengirim data lama.
$title = isset($tugas) ? 'Edit Tugas' : 'Tambah Tugas';
include __DIR__ . '/../layouts/header.php';

$is_edit = isset($tugas);
$action_url = $is_edit ? BASE_URL . 'admin/edit-tugas/' . (int)$tugas['id'] : BASE_URL . 'admin/tambah-tugas';

$val = function ($key, $default = '') use ($tugas, $is_edit) {
    return $is_edit && isset($tugas[$key]) ? $tugas[$key] : $default;
};

// Nilai saat ini untuk dropdown target gabungan
if ($is_edit && $tugas['target_kelompok_id']) {
    $target_saat_ini = 'k:' . $tugas['target_kelompok_id'];
} elseif ($is_edit && $tugas['target_jurusan_id']) {
    $target_saat_ini = 'j:' . $tugas['target_jurusan_id'];
} elseif ($is_edit && $tugas['target_fakultas_id']) {
    $target_saat_ini = 'f:' . $tugas['target_fakultas_id'];
} else {
    $target_saat_ini = 'semua';
}
?>
<div class="page-head">
    <div>
        <h1><?= $is_edit ? 'Edit Tugas' : 'Tambah Tugas' ?></h1>
        <p class="page-sub">
            <?php if ($is_edit): ?>Tahap: <?= e($tugas['tahap_judul']) ?> (tahap tidak dapat dipindah saat edit)<?php else: ?>Tugas terikat pada satu tahap ospek.<?php endif; ?>
        </p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/tugas">Kembali ke daftar tugas</a>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
    <form method="post" action="<?= $action_url ?>">
        <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

        <div class="form-grid">
            <?php if (!$is_edit): ?>
            <div class="field">
                <label for="tahap_id">Tahap</label>
                <select id="tahap_id" name="tahap_id" required>
                    <?php foreach ($timeline as $t): ?>
                    <option value="<?= (int)$t['id'] ?>"><?= e($t['judul']) ?> (<?= e($t['target_level']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="field">
                <label for="judul">Judul tugas</label>
                <input type="text" id="judul" name="judul" value="<?= e($val('judul')) ?>" required>
            </div>

            <div class="field span-2">
                <label for="deskripsi">Deskripsi dan instruksi</label>
                <textarea id="deskripsi" name="deskripsi" required placeholder="Jelaskan isi tugas, format jawaban, dan ketentuan lain"><?= e($val('deskripsi')) ?></textarea>
            </div>

            <div class="field">
                <label for="deadline">Deadline (waktu server)</label>
                <input type="datetime-local" id="deadline" name="deadline"
                       value="<?= e($is_edit ? date('Y-m-d\TH:i', strtotime($tugas['deadline'])) : '') ?>" required>
                <p class="field-hint">Pengumpulan setelah waktu ini otomatis berstatus "Terlambat".</p>
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

            <div class="field">
                <label for="format_diizinkan">Format diizinkan</label>
                <input type="text" id="format_diizinkan" name="format_diizinkan" value="<?= e($val('format_diizinkan', 'pdf,jpg,png,doc,docx')) ?>">
                <p class="field-hint">Pisahkan dengan koma. Format yang didukung sistem: pdf, jpg, jpeg, png, doc, docx.</p>
            </div>

            <div class="field">
                <label for="ukuran_max_mb">Ukuran maksimal (MB)</label>
                <input type="number" id="ukuran_max_mb" name="ukuran_max_mb" min="1" max="50" value="<?= e($val('ukuran_max_mb', '10')) ?>" required>
            </div>
        </div>

        <button type="submit" class="btn"><?= $is_edit ? 'Simpan perubahan' : 'Tambah tugas' ?></button>
    </form>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
