<?php
$title = 'Pengesahan';
include __DIR__ . '/../layouts/header.php';

$filter_status = $_GET['status'] ?? '';
$filter_fakultas = $_GET['fakultas_id'] ?? '';
$jumlah_disahkan = 0;

foreach ($list as $row) {
    if ($row['status'] === 'disahkan') {
        $jumlah_disahkan++;
    }
}
?>
<div class="page-head">
    <div>
        <h1>Pengesahan Ospek</h1>
    </div>
    <div class="page-actions">
        <form method="post" action="<?= BASE_URL ?>admin/proses-semua-pengesahan" class="inline-form">
            <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">
            <button type="submit" class="btn" data-confirm="Hitung ulang pengesahan seluruh mahasiswa baru sekarang?">Proses semua sekarang</button>
        </form>
    </div>
</div>

<div class="stats">
    <div class="stat">
        <p class="stat-label">Total terdaftar di halaman ini</p>
        <p class="stat-value"><?= count($list) ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Disahkan</p>
        <p class="stat-value"><?= $jumlah_disahkan ?></p>
    </div>
    <div class="stat">
        <p class="stat-label">Belum memenuhi</p>
        <p class="stat-value"><?= count($list) - $jumlah_disahkan ?></p>
    </div>
</div>

<div class="card">
    <form method="get" action="<?= BASE_URL ?>admin/pengesahan">
        <div class="form-grid">
            <div class="field">
                <label for="status">Filter status</label>
                <select id="status" name="status">
                    <option value="">Semua status</option>
                    <option value="disahkan" <?= $filter_status === 'disahkan' ? 'selected' : '' ?>>Disahkan</option>
                    <option value="belum_memenuhi" <?= $filter_status === 'belum_memenuhi' ? 'selected' : '' ?>>Belum memenuhi</option>
                </select>
            </div>
            <div class="field">
                <label for="fakultas_id">Filter fakultas</label>
                <select id="fakultas_id" name="fakultas_id">
                    <option value="">Semua fakultas</option>
                    <?php foreach ($fakultas as $f): ?>
                    <option value="<?= (int)$f['id'] ?>" <?= (string)$filter_fakultas === (string)$f['id'] ? 'selected' : '' ?>><?= e($f['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field" style="align-self: end;">
                <button type="submit" class="btn btn-outline">Terapkan filter</button>
            </div>
        </div>
    </form>
</div>

<?php if (empty($list)): ?>
<div class="empty">
    <strong>Belum ada pengesahan yang diproses<?= ($filter_status || $filter_fakultas) ? ' untuk filter ini' : '' ?>.</strong>
    Klik "Proses semua sekarang" untuk menghitung status seluruh maba, atau proses per mahasiswa dari halaman Pengguna.
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th scope="col">NIM</th>
                <th scope="col">Nama</th>
                <th scope="col">Fakultas</th>
                <th scope="col">Jurusan</th>
                <th scope="col">Status</th>
                <th scope="col">Kekurangan</th>
                <th scope="col">Diproses</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($list as $row): ?>
            <?php list($status_label, $status_tone) = status_meta($row['status']); ?>
            <tr>
                <td><?= e($row['nim']) ?></td>
                <td><strong><?= e($row['nama']) ?></strong></td>
                <td><?= e($row['fakultas_nama'] ?? '-') ?></td>
                <td><?= e($row['jurusan_nama'] ?? '-') ?></td>
                <td><span class="chip chip-<?= $status_tone ?>"><?= e($status_label) ?></span></td>
                <td><?= $row['detail_kekurangan'] ? e($row['detail_kekurangan']) : '<span class="muted">—</span>' ?></td>
                <td><?= e(format_tanggal($row['diproses_at'])) ?></td>
                <td>
                    <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>admin/proses-pengesahan/<?= (int)$row['user_id'] ?>">Tinjau ulang</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
