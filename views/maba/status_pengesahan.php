<?php
$title = 'Status Pengesahan';
include __DIR__ . '/../layouts/header.php';

list($final_label, $final_tone) = status_meta($status['status']);

$tugas_kelompok = [
    'Tugas Ospek Kampus' => $status['detail']['tugas_kampus'],
    'Tugas Ospek Fakultas' => $status['detail']['tugas_fakultas'],
    'Tugas Ospek Jurusan' => $status['detail']['tugas_jurusan'],
];

// Ambang dari tabel pengaturan (fallback 75% untuk data lama).
$min_kehadiran = (float)($status['detail']['min_kehadiran'] ?? 75);
$min_tampil = rtrim(rtrim(number_format($min_kehadiran, 1, ',', '.'), '0'), ',');

$total_tugas = 0;
$total_kumpul = 0;
foreach ($tugas_kelompok as $tk) {
    $total_tugas += (int)$tk['total'];
    $total_kumpul += (int)$tk['sudah_submit'];
}

$persen_tugas = $total_tugas > 0 ? round($total_kumpul / $total_tugas * 100) : 100;
$persen_hadir = (float)$status['detail']['persentase_kehadiran'];
?>
<div class="page-head">
    <div>
        <h1>Status Pengesahan</h1>
        <p class="page-sub">Checklist di bawah dihitung langsung dari data absensi dan pengumpulan tugas Anda saat halaman ini dibuka.</p>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Status saat ini</h2>
        <span class="chip chip-<?= $final_tone ?>"><?= e($final_label) ?></span>
    </div>

    <?php if ($pengesahan): ?>
    <p class="muted small">
        Keputusan terakhir admin diproses <?= e(format_tanggal($pengesahan['diproses_at'])) ?>
        <?= $pengesahan['diproses_oleh_nama'] ? 'oleh ' . e($pengesahan['diproses_oleh_nama']) : '' ?>.
    </p>
    <?php else: ?>
    <p class="muted small">Admin belum memproses pengesahan Anda. Status di atas adalah hasil perhitungan otomatis dari data terkini.</p>
    <?php endif; ?>

    <?php if (!empty($status['kekurangan'])): ?>
    <div class="alert alert-warn" role="alert">
        <strong>Yang masih kurang:</strong>
        <ul class="mb-0">
            <?php foreach ($status['kekurangan'] as $k): ?>
            <li><?= e($k) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php else: ?>
    <div class="alert alert-success" role="status">Seluruh syarat yang bisa diverifikasi sistem sudah terpenuhi.</div>
    <?php endif; ?>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-head">
            <h2>Tugas</h2>
            <span class="muted small"><?= $total_kumpul ?> dari <?= $total_tugas ?> terkumpul</span>
        </div>
        <div class="progress" role="img" aria-label="Progres tugas <?= (int)$persen_tugas ?> persen">
            <span style="width: <?= (int)$persen_tugas ?>%"></span>
        </div>
        <ul class="checklist">
            <?php foreach ($tugas_kelompok as $nama => $tk): ?>
            <li>
                <span><?= e($nama) ?><br><span class="muted small"><?= (int)$tk['sudah_submit'] ?> dari <?= (int)$tk['total'] ?> tugas</span></span>
                <?php if ($tk['total'] == 0): ?>
                <span class="chip chip-neutral">Belum ada tugas</span>
                <?php elseif ($tk['lengkap']): ?>
                <span class="chip chip-ok">Lengkap</span>
                <?php else: ?>
                <span class="chip chip-warn">Kurang <?= (int)$tk['belum_submit'] ?></span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Kehadiran</h2>
            <span class="muted small"><?= e(rtrim(rtrim(number_format($persen_hadir, 1, ',', '.'), '0'), ',')) ?>%</span>
        </div>
        <div class="progress" role="img" aria-label="Kehadiran <?= (int)$persen_hadir ?> persen">
            <span style="width: <?= min(100, (int)$persen_hadir) ?>%"></span>
        </div>
        <ul class="meta-list">
            <li><span class="meta-label">Hadir</span> <?= (int)$status['detail']['absensi']['hadir'] ?> sesi</li>
            <li><span class="meta-label">Di luar radius</span> <?= (int)$status['detail']['absensi']['di_luar_radius'] ?> sesi</li>
            <li><span class="meta-label">Terlambat</span> <?= (int)$status['detail']['absensi']['terlambat'] ?> sesi</li>
            <li><span class="meta-label">Total sesi</span> <?= (int)$status['detail']['absensi']['total_sesi'] ?> sesi</li>
        </ul>
    </div>
</div>

<div class="card card-quiet">
    <h2>Catatan</h2>
    <ul class="meta-list">
        <li><span class="meta-label">KTM &amp; almamater</span> Pengambilan diverifikasi manual oleh panitia di lapangan, tidak dihitung otomatis oleh sistem.</li>
        <li><span class="meta-label">Ambang kehadiran</span> Minimal <?= e($min_tampil) ?>% dari total sesi.</li>
    </ul>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
