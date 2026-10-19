<?php
$title = 'Absen: ' . $sesi['judul'];
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Absen Sesi</h1>
        <p class="page-sub"><?= e($sesi['judul']) ?> &middot; <?= e($tahap['judul'] ?? '') ?> &middot; <?= e(format_tanggal($sesi['tanggal'], false)) ?>, <?= e(date('H:i', strtotime($sesi['jam_mulai']))) ?>&ndash;<?= e(date('H:i', strtotime($sesi['jam_selesai']))) ?> WIB</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>detail-sesi/<?= (int)$sesi['id'] ?>">Kembali ke detail sesi</a>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($absensi_exists): ?>
<div class="card">
    <h2>Absensi sudah tercatat</h2>
    <p class="muted">Anda sudah melakukan absensi untuk sesi ini. Lihat detail sesi untuk status dan catatan panitia.</p>
    <p class="action-row mb-0"><a class="btn" href="<?= BASE_URL ?>detail-sesi/<?= (int)$sesi['id'] ?>">Lihat status absensi</a></p>
</div>
<?php else: ?>

<div class="alert alert-info">
    Waktu yang dicatat sistem adalah <strong>waktu server</strong> saat tombol kirim ditekan, bukan jam di perangkat Anda.
    Jam perangkat saat ini: <span id="device-clock" class="gps-coords"></span> (tampilan saja).
</div>

<form method="post" action="<?= BASE_URL ?>absen/<?= (int)$sesi['id'] ?>" enctype="multipart/form-data" id="absen-form">
    <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

    <div class="card">
        <h2>1. Foto langsung dari kamera</h2>
        <p class="muted small">Absensi menggunakan foto yang diambil saat ini juga. Foto dari galeri tidak diterima.</p>

        <p id="camera-note" class="small" aria-live="polite">Kamera langsung memerlukan izin. Bila kamera tidak muncul, gunakan tombol kamera perangkat di bawah.</p>

        <div class="camera-frame" id="camera-frame" hidden>
            <video id="camera-video" playsinline autoplay muted></video>
            <img class="preview" id="camera-preview" alt="Pratinjau foto absensi" style="display: none;">
        </div>

        <div id="camera-fallback">
            <div class="field">
                <label for="foto-input">Ambil foto dengan kamera perangkat</label>
                <input type="file" id="foto-input" name="foto" accept="image/*" capture="environment">
                <p class="field-hint">Tombol ini akan membuka kamera perangkat pada browser mobile.</p>
            </div>
        </div>

        <div class="camera-actions">
            <button type="button" class="btn" id="btn-capture" hidden>Ambil foto</button>
            <button type="button" class="btn btn-outline" id="btn-retake" hidden>Ambil ulang</button>
        </div>
    </div>

    <div class="card">
        <h2>2. Lokasi saat ini</h2>
        <div class="gps-box">
            <p><strong id="gps-status">Menunggu izin lokasi...</strong></p>
            <p class="muted small" id="gps-detail"></p>

            <input type="hidden" name="lat" id="lat-input" value="">
            <input type="hidden" name="lng" id="lng-input" value="">

            <div class="manual-loc" id="manual-loc">
                <div class="alert alert-warn">
                    Izin lokasi tidak diberikan atau lokasi gagal dibaca. Isi koordinat manual (bisa disalin dari aplikasi peta).
                    Absensi dengan koordinat manual akan ditandai untuk ditinjau panitia.
                </div>
                <div class="form-grid">
                    <div class="field">
                        <label for="lat-manual">Latitude</label>
                        <input type="text" id="lat-manual" inputmode="decimal" placeholder="-6.200000">
                    </div>
                    <div class="field">
                        <label for="lng-manual">Longitude</label>
                        <input type="text" id="lng-manual" inputmode="decimal" placeholder="106.816666">
                    </div>
                </div>
                <button type="button" class="btn btn-outline" id="btn-manual">Pakai koordinat manual</button>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>3. Kirim absensi</h2>
        <p class="muted small">
            Setelah dikirim, status kehadiran dihitung otomatis: jarak dari titik lokasi sesi (radius <?= (int)$sesi['radius_meter'] ?> meter)
            dan waktu kirim dibanding jadwal. Absensi di luar radius tetap tercatat dan ditinjau panitia.
        </p>
        <button type="submit" class="btn" id="btn-submit-absen" disabled>Kirim absensi</button>
        <p class="field-hint">Tombol aktif setelah foto terambil dan lokasi terbaca.</p>
    </div>
</form>

<?php
$page_script = 'absensi.js';
endif;
include __DIR__ . '/../layouts/footer.php';
?>
