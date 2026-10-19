<?php
$title = 'Kelola Pengguna';
include __DIR__ . '/../layouts/header.php';

$is_superadmin = ($_SESSION['user_role'] ?? '') === 'superadmin';
$filter_aktif = [
    'fakultas_id' => $_GET['fakultas_id'] ?? '',
    'jurusan_id' => $_GET['jurusan_id'] ?? '',
    'kelompok_id' => $_GET['kelompok_id'] ?? '',
];
?>
<div class="page-head">
    <div>
        <h1>Kelola Pengguna</h1>
        <p class="page-sub">Data mahasiswa baru, panitia, dan admin. Total maba terfilter: <?= count($users) ?>.</p>
    </div>
    <?php if ($is_superadmin): ?>
    <div class="page-actions">
        <a class="btn" href="<?= BASE_URL ?>admin/tambah-user">Tambah pengguna</a>
        <a class="btn btn-outline" href="<?= BASE_URL ?>admin/import-maba">Import CSV maba</a>
    </div>
    <?php endif; ?>
</div>

<?php if (!$is_superadmin): ?>
<div class="alert alert-info">Penambahan dan import pengguna hanya dapat dilakukan superadmin. Anda tetap dapat melihat dan memfilter data.</div>
<?php endif; ?>

<div class="card">
    <form method="get" action="<?= BASE_URL ?>admin/users">
        <div class="form-grid">
            <div class="field">
                <label for="fakultas_id">Filter fakultas</label>
                <select id="fakultas_id" name="fakultas_id">
                    <option value="">Semua fakultas</option>
                    <?php foreach ($fakultas as $f): ?>
                    <option value="<?= (int)$f['id'] ?>" <?= (string)$filter_aktif['fakultas_id'] === (string)$f['id'] ? 'selected' : '' ?>><?= e($f['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="jurusan_id">Filter jurusan</label>
                <select id="jurusan_id" name="jurusan_id">
                    <option value="">Semua jurusan</option>
                    <?php foreach ($jurusan as $j): ?>
                    <option value="<?= (int)$j['id'] ?>" <?= (string)$filter_aktif['jurusan_id'] === (string)$j['id'] ? 'selected' : '' ?>><?= e($j['nama']) ?> &middot; <?= e($j['fakultas_nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="kelompok_id">Filter kelompok</label>
                <select id="kelompok_id" name="kelompok_id">
                    <option value="">Semua kelompok</option>
                    <?php foreach ($kelompok as $k): ?>
                    <option value="<?= (int)$k['id'] ?>" <?= (string)$filter_aktif['kelompok_id'] === (string)$k['id'] ? 'selected' : '' ?>><?= e($k['nama']) ?> &middot; <?= e($k['jurusan_nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field" style="align-self: end;">
                <button type="submit" class="btn btn-outline">Terapkan filter</button>
            </div>
        </div>
    </form>
</div>

<h2>Mahasiswa Baru</h2>
<?php if (empty($users)): ?>
<div class="empty">
    <strong>Tidak ada data mahasiswa baru<?= array_filter($filter_aktif) ? ' untuk filter ini' : '' ?>.</strong>
    <?php if (array_filter($filter_aktif)): ?>Kosongkan filter untuk melihat semua.<?php else: ?>Import CSV atau tambah satu per satu.<?php endif; ?>
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
                <th scope="col">Kelompok</th>
                <th scope="col">Kontak darurat</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= e($u['nim']) ?></td>
                <td><strong><?= e($u['nama']) ?></strong><br><span class="muted small"><?= e($u['email']) ?></span></td>
                <td><?= e($u['fakultas_nama'] ?? '-') ?></td>
                <td><?= e($u['jurusan_nama'] ?? '-') ?></td>
                <td><?= e($u['kelompok_nama'] ?? '-') ?></td>
                <td><?= $u['no_kontak_darurat'] ? e($u['no_kontak_darurat']) : '<span class="muted">Belum diisi</span>' ?></td>
                <td>
                    <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>admin/proses-pengesahan/<?= (int)$u['id'] ?>">Pengesahan</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<h2 class="mt-3">Panitia dan Admin</h2>
<?php if (empty($staff)): ?>
<div class="empty">
    <strong>Belum ada akun panitia atau admin.</strong>
    Tambahkan lewat tombol "Tambah pengguna" bila Anda superadmin.
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th scope="col">NIM/ID</th>
                <th scope="col">Nama</th>
                <th scope="col">Role</th>
                <th scope="col">Email</th>
                <th scope="col">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($staff as $s): ?>
            <tr>
                <td><?= e($s['nim']) ?></td>
                <td><strong><?= e($s['nama']) ?></strong></td>
                <td><span class="chip chip-neutral"><?= e(ucfirst($s['role'])) ?></span></td>
                <td><?= e($s['email']) ?></td>
                <td><?= (int)$s['is_active'] === 1 ? '<span class="chip chip-ok">Aktif</span>' : '<span class="chip chip-danger">Nonaktif</span>' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
