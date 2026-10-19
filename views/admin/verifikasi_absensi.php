<?php
$title = 'Verifikasi Absensi';
include __DIR__ . '/../layouts/header.php';

list($status_label, $status_tone) = status_meta($absensi['status']);
?>
<div class="page-head">
    <div>
        <h1>Verifikasi Absensi</h1>
        <p class="page-sub"><?= e($absensi['nama']) ?> (<?= e($absensi['nim']) ?>) &middot; <?= e($absensi['sesi_judul']) ?></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/rekap-absensi/<?= (int)$absensi['sesi_id'] ?>">Kembali ke rekap sesi</a>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<div class="grid-2">
    <div class="card">
        <h2>Bukti absensi</h2>
        <?php if (file_exists(__DIR__ . '/../../' . $absensi['foto_path'])): ?>
        <a href="<?= BASE_URL . e($absensi['foto_path']) ?>" target="_blank" rel="noopener">
            <img src="<?= BASE_URL . e($absensi['foto_path']) ?>" alt="Foto absensi <?= e($absensi['nama']) ?>" style="max-width: 100%; border-radius: var(--radius); border: 1px solid var(--line);">
        </a>
        <p class="field-hint">Klik foto untuk membuka ukuran penuh.</p>
        <?php else: ?>
        <p class="muted">File foto tidak ditemukan di server. Minta mahasiswa mengirim ulang atau catat temuan ini di catatan verifikasi.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Data absensi</h2>
        <ul class="meta-list">
            <li><span class="meta-label">Status saat ini</span> <span class="chip chip-<?= $status_tone ?>"><?= e($status_label) ?></span></li>
            <li><span class="meta-label">Waktu (server)</span> <?= e(format_tanggal($absensi['waktu_submit_server'])) ?></li>
            <li><span class="meta-label">Jarak dari titik</span>
                <?= $absensi['jarak_dari_lokasi_meter'] !== null ? e(number_format((float)$absensi['jarak_dari_lokasi_meter'], 0, ',', '.')) . ' meter (radius ' . (int)$absensi['radius_meter'] . ' m)' : 'Tidak dihitung (titik lokasi sesi belum diisi)' ?>
            </li>
            <li><span class="meta-label">Koordinat</span>
                <a href="https://www.google.com/maps?q=<?= e($absensi['lat']) ?>,<?= e($absensi['lng']) ?>" target="_blank" rel="noopener">
                    <?= e(number_format((float)$absensi['lat'], 6)) ?>, <?= e(number_format((float)$absensi['lng'], 6)) ?>
                </a>
            </li>
            <?php if ($absensi['diverifikasi_oleh']): ?>
            <li><span class="meta-label">Terakhir diverifikasi</span> <?= e(format_tanggal($absensi['updated_at'])) ?></li>
            <?php endif; ?>
            <?php if ($absensi['catatan']): ?>
            <li><span class="meta-label">Catatan</span> <?= e($absensi['catatan']) ?></li>
            <?php endif; ?>
        </ul>

        <h3 class="mt-2">Koreksi status</h3>
        <form method="post" action="<?= BASE_URL ?>admin/verifikasi-absensi/<?= (int)$absensi['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <?php foreach (['hadir', 'di_luar_radius', 'terlambat', 'izin', 'tidak_hadir'] as $opt): ?>
                    <?php list($opt_label) = status_meta($opt); ?>
                    <option value="<?= e($opt) ?>" <?= $absensi['status'] === $opt ? 'selected' : '' ?>><?= e($opt_label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="catatan">Catatan (alasan koreksi)</label>
                <textarea id="catatan" name="catatan" placeholder="Mis. koordinat manual, foto buram, izin via surat"><?= e($absensi['catatan'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn">Simpan verifikasi</button>
            <p class="field-hint">Perubahan status tercatat di audit log beserta waktu dan pelakunya.</p>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
