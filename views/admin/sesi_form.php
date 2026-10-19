<?php
$sesi = $sesi ?? null; // Mode tambah: controller tidak mengirim data lama.
$title = isset($sesi) ? 'Edit Sesi' : 'Tambah Sesi';
include __DIR__ . '/../layouts/header.php';

$is_edit = isset($sesi);
$tahap_id = $is_edit ? (int)$sesi['tahap_id'] : (int)$tahap['id'];
$tahap_judul = $is_edit ? null : $tahap['judul'];
$action_url = $is_edit ? BASE_URL . 'admin/edit-sesi/' . (int)$sesi['id'] : BASE_URL . 'admin/tambah-sesi/' . $tahap_id;

$val = function ($key, $default = '') use ($sesi, $is_edit) {
    return $is_edit && isset($sesi[$key]) ? $sesi[$key] : $default;
};
?>
<div class="page-head">
    <div>
        <h1><?= $is_edit ? 'Edit Sesi' : 'Tambah Sesi' ?></h1>
        <p class="page-sub">
            <?php if ($tahap_judul): ?>Tahap: <?= e($tahap_judul) ?><?php else: ?>Tahap #<?= $tahap_id ?><?php endif; ?>
        </p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/sesi/<?= $tahap_id ?>">Kembali ke daftar sesi</a>
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
                <label for="judul">Judul sesi</label>
                <input type="text" id="judul" name="judul" value="<?= e($val('judul')) ?>" required>
            </div>

            <div class="field">
                <label for="tanggal">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" value="<?= e($val('tanggal')) ?>" required>
            </div>

            <div class="field">
                <label for="pic">PIC (penanggung jawab)</label>
                <input type="text" id="pic" name="pic" value="<?= e($val('pic')) ?>" placeholder="Nama panitia penanggung jawab">
            </div>

            <div class="field">
                <label for="jam_mulai">Jam mulai</label>
                <input type="time" id="jam_mulai" name="jam_mulai" value="<?= e($val('jam_mulai')) ?>" required>
            </div>

            <div class="field">
                <label for="jam_selesai">Jam selesai</label>
                <input type="time" id="jam_selesai" name="jam_selesai" value="<?= e($val('jam_selesai')) ?>" required>
                <p class="field-hint">Absensi setelah jam selesai otomatis berstatus "Terlambat" dan tetap ditinjau panitia.</p>
            </div>

            <div class="field span-2">
                <label for="lokasi_text">Lokasi (teks)</label>
                <input type="text" id="lokasi_text" name="lokasi_text" value="<?= e($val('lokasi_text')) ?>" required placeholder="Mis. Lapangan Rektorat">
            </div>

            <div class="field">
                <label for="lokasi_lat">Latitude</label>
                <input type="text" id="lokasi_lat" name="lokasi_lat" value="<?= e($val('lokasi_lat')) ?>" inputmode="decimal" placeholder="-6.200000">
            </div>

            <div class="field">
                <label for="lokasi_lng">Longitude</label>
                <input type="text" id="lokasi_lng" name="lokasi_lng" value="<?= e($val('lokasi_lng')) ?>" inputmode="decimal" placeholder="106.816666">
            </div>

            <div class="field span-2">
                <button type="button" class="btn btn-outline" data-geolocate data-lat="#lokasi_lat" data-lng="#lokasi_lng" data-status="#geo-status">
                    Isi koordinat dari lokasi saya sekarang
                </button>
                <p id="geo-status" class="field-hint"></p>
                <p class="field-hint">Bila titik GPS kosong, absensi tetap tercatat tetapi tanpa perhitungan jarak. Isi titik lokasi sesi agar status "di luar radius" bisa dihitung.</p>
            </div>

            <div class="field">
                <label for="radius_meter">Radius absen (meter)</label>
                <input type="number" id="radius_meter" name="radius_meter" min="10" max="5000" value="<?= e($val('radius_meter', '150')) ?>" required>
            </div>
        </div>

        <button type="submit" class="btn"><?= $is_edit ? 'Simpan perubahan' : 'Tambah sesi' ?></button>
    </form>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
