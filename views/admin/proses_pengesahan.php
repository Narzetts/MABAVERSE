<?php
$title = 'Proses Pengesahan';
include __DIR__ . '/../layouts/header.php';

if (!$user || !$kelengkapan) {
    echo '<div class="empty"><strong>Data mahasiswa tidak ditemukan.</strong> Kembali ke daftar pengesahan untuk memilih mahasiswa lain.</div>';
    include __DIR__ . '/../layouts/footer.php';
    return;
}

list($hitung_label, $hitung_tone) = status_meta($kelengkapan['status']);

$tugas_kelompok = [
    'Tugas Ospek Kampus' => $kelengkapan['detail']['tugas_kampus'],
    'Tugas Ospek Fakultas' => $kelengkapan['detail']['tugas_fakultas'],
    'Tugas Ospek Jurusan' => $kelengkapan['detail']['tugas_jurusan'],
];

// Ambang dari tabel pengaturan (fallback 75% untuk data lama).
$min_kehadiran = (float)($kelengkapan['detail']['min_kehadiran'] ?? 75);
$min_tampil = rtrim(rtrim(number_format($min_kehadiran, 1, ',', '.'), '0'), ',');
?>
<div class="page-head">
    <div>
        <h1>Proses Pengesahan</h1>
        <p class="page-sub"><?= e($user['nama']) ?> (<?= e($user['nim']) ?>) &middot; <?= e($user['fakultas_nama'] ?? '-') ?> &middot; <?= e($user['jurusan_nama'] ?? '-') ?></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/pengesahan">Kembali ke daftar</a>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<div class="grid-2">
    <div class="card">
        <div class="card-head">
            <h2>Hasil hitung sistem</h2>
            <span class="chip chip-<?= $hitung_tone ?>"><?= e($hitung_label) ?></span>
        </div>

        <?php if (!empty($kelengkapan['kekurangan'])): ?>
        <div class="alert alert-warn" role="alert">
            <strong>Belum terpenuhi:</strong>
            <ul class="mb-0">
                <?php foreach ($kelengkapan['kekurangan'] as $k): ?>
                <li><?= e($k) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php else: ?>
        <div class="alert alert-success" role="status">Semua syarat yang bisa diverifikasi sistem terpenuhi dari data saat ini.</div>
        <?php endif; ?>

        <ul class="checklist">
            <?php foreach ($tugas_kelompok as $nama => $tk): ?>
            <li>
                <span><?= e($nama) ?><br><span class="muted small"><?= (int)$tk['sudah_submit'] ?> dari <?= (int)$tk['total'] ?> tugas terkumpul</span></span>
                <?php if ($tk['total'] == 0): ?>
                <span class="chip chip-neutral">Tidak ada tugas</span>
                <?php elseif ($tk['lengkap']): ?>
                <span class="chip chip-ok">Lengkap</span>
                <?php else: ?>
                <span class="chip chip-warn">Kurang <?= (int)$tk['belum_submit'] ?></span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
            <li>
                <span>Kehadiran<br><span class="muted small">Minimal <?= e($min_tampil) ?>% dari total sesi</span></span>
                <span class="chip chip-<?= (float)$kelengkapan['detail']['persentase_kehadiran'] >= $min_kehadiran ? 'ok' : 'warn' ?>">
                    <?= e($kelengkapan['detail']['persentase_kehadiran']) ?>%
                </span>
            </li>
        </ul>
    </div>

    <div class="card">
        <h2>Keputusan admin</h2>
        <form method="post" action="<?= BASE_URL ?>admin/proses-pengesahan/<?= (int)$user['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

            <p>Klik simpan untuk memakai hasil hitung sistem, atau gunakan override bila ada pertimbangan khusus.</p>

            <div class="checkbox-line">
                <input type="checkbox" id="override" name="override" value="yes">
                <label for="override">Saya ingin melakukan override terhadap hasil sistem</label>
            </div>

            <div class="field">
                <label for="override_status">Status override</label>
                <select id="override_status" name="override_status">
                    <option value="disahkan">Disahkan</option>
                    <option value="belum_memenuhi">Belum memenuhi</option>
                </select>
            </div>

            <div class="field">
                <label for="catatan_override">Catatan override</label>
                <textarea id="catatan_override" name="catatan_override" placeholder="Alasan override, mis. verifikasi manual KTM dan almamater di lapangan"></textarea>
                <p class="field-hint">Override tercatat di audit log lengkap dengan catatan ini.</p>
            </div>

            <button type="submit" class="btn">Simpan keputusan</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
