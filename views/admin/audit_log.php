<?php
$title = 'Audit Log';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <h1>Audit Log</h1>
        <p class="page-sub">Riwayat aksi sensitif: login, ubah status absensi dan tugas, pengesahan, reset password. Maksimal 500 entri terakhir.</p>
    </div>
</div>

<?php if (empty($logs)): ?>
<div class="empty">
    <strong>Belum ada catatan.</strong>
    Entri muncul begitu ada aksi sensitif yang tercatat, mis. login atau verifikasi absensi.
</div>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th scope="col">Waktu</th>
                <th scope="col">Pelaku</th>
                <th scope="col">Aksi</th>
                <th scope="col">Target</th>
                <th scope="col">IP</th>
                <th scope="col">Detail</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= e(format_tanggal($log['created_at'])) ?></td>
                <td><?= e($log['user_nama']) ?></td>
                <td><strong><?= e($log['aksi']) ?></strong></td>
                <td><?= e($log['target_tabel']) ?><?= $log['target_id'] ? ' #' . (int)$log['target_id'] : '' ?></td>
                <td><?= e($log['ip_address']) ?></td>
                <td>
                    <?php if ($log['data_lama'] || $log['data_baru']): ?>
                    <details>
                        <summary>Lihat</summary>
                        <?php if ($log['data_lama']): ?><p class="small mb-0"><strong>Sebelum:</strong> <?= e($log['data_lama']) ?></p><?php endif; ?>
                        <?php if ($log['data_baru']): ?><p class="small mb-0"><strong>Sesudah:</strong> <?= e($log['data_baru']) ?></p><?php endif; ?>
                    </details>
                    <?php else: ?>
                    <span class="muted">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
