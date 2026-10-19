<?php
$title = 'Permintaan Reset Password';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Permintaan Reset Password</h1>
    </div>
</div>

<?php if (empty($tokens)): ?>
<div class="empty">
    <strong>Tidak ada permintaan reset yang aktif.</strong>
    Token yang sudah kedaluwarsa atau sudah dipakai tidak ditampilkan di sini.
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th scope="col">NIM</th>
                <th scope="col">Nama</th>
                <th scope="col">Dibuat</th>
                <th scope="col">Kedaluwarsa</th>
                <th scope="col">Tautan reset</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tokens as $t): ?>
            <?php $tautan = BASE_URL . 'reset-password?token=' . $t['token']; ?>
            <tr>
                <td><?= e($t['nim']) ?></td>
                <td><strong><?= e($t['nama']) ?></strong></td>
                <td><?= e(format_tanggal($t['created_at'])) ?></td>
                <td><?= e(format_tanggal($t['expires_at'])) ?></td>
                <td>
                    <input type="text" readonly value="<?= e($tautan) ?>"
                           aria-label="Tautan reset untuk <?= e($t['nama']) ?>">
                    <p class="field-hint">Klik tiga kali pada tautan untuk memilih seluruhnya, lalu salin dan kirim ke mahasiswa.</p>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
