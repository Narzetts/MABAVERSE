<?php
if (!defined('BASE_URL')) {
    die('Akses langsung tidak diizinkan.');
}

$user_id = $_SESSION['user_id'] ?? null;
$user_nama = $_SESSION['user_nama'] ?? '';
$user_role = $_SESSION['user_role'] ?? '';
$title = $title ?? 'MABAVERSE';

$current_path = isset($_GET['url']) ? trim($_GET['url'], '/') : '';

$role_label = [
    'maba' => 'Mahasiswa Baru',
    'panitia' => 'Panitia',
    'admin' => 'Admin Ospek',
    'superadmin' => 'Superadmin',
][$user_role] ?? '';

// Badge pengumuman belum dibaca (khusus maba)
$unread_pengumuman = 0;
if ($user_role === 'maba') {
    require_once __DIR__ . '/../../models/Pengumuman.php';
    $pengumumanModel = new Pengumuman();
    $unread_pengumuman = (int)$pengumumanModel->getUnreadCount(
        $user_id,
        $_SESSION['user_fakultas_id'] ?? null,
        $_SESSION['user_jurusan_id'] ?? null,
        $_SESSION['user_kelompok_id'] ?? null
    );
}

$nav_items = [];
if ($user_role === 'maba') {
    $nav_items = [
        ['Dashboard', 'dashboard', ['dashboard']],
        ['Timeline', 'timeline', ['timeline', 'detail-sesi', 'absen']],
        ['Tugas', 'tugas', ['tugas', 'submit-tugas', 'riwayat-tugas']],
        ['Pengumuman', 'pengumuman', ['pengumuman', 'detail-pengumuman']],
        ['Pengesahan', 'status-pengesahan', ['status-pengesahan']],
    ];
} elseif ($user_role === 'panitia') {
    $nav_items = [
        ['Dashboard', 'panitia/dashboard', ['panitia', 'panitia/dashboard']],
        ['Kelompok', 'panitia/kelompok', ['panitia/kelompok']],
        ['Absensi', 'panitia/absensi', ['panitia/absensi', 'panitia/verifikasi-absensi']],
        ['Tugas', 'panitia/tugas', ['panitia/tugas', 'panitia/verifikasi-tugas']],
        ['Pengumuman', 'panitia/pengumuman', ['panitia/pengumuman', 'panitia/tambah-pengumuman']],
    ];
} elseif (in_array($user_role, ['admin', 'superadmin'], true)) {
    $nav_items = [
        ['Dashboard', 'admin/dashboard', ['admin', 'admin/dashboard']],
        ['Timeline', 'admin/timeline', ['admin/timeline', 'admin/tambah-tahap', 'admin/edit-tahap']],
        ['Sesi', 'admin/sesi', ['admin/sesi', 'admin/tambah-sesi', 'admin/edit-sesi']],
        ['Tugas', 'admin/tugas', ['admin/tugas', 'admin/tambah-tugas', 'admin/edit-tugas']],
        ['Absensi', 'admin/rekap-absensi', ['admin/rekap-absensi', 'admin/verifikasi-absensi']],
        ['Rekap Tugas', 'admin/rekap-tugas', ['admin/rekap-tugas', 'admin/verifikasi-tugas']],
        ['Pengumuman', 'admin/pengumuman', ['admin/pengumuman', 'admin/tambah-pengumuman', 'admin/edit-pengumuman']],
        ['Pengesahan', 'admin/pengesahan', ['admin/pengesahan', 'admin/proses-pengesahan']],
    ];

    $nav_items[] = ['Reset Password', 'admin/reset-permintaan', ['admin/reset-permintaan']];
    $nav_items[] = ['Pengaturan', 'admin/pengaturan', ['admin/pengaturan']];

    if ($user_role === 'superadmin') {
        $nav_items[] = ['Pengguna', 'admin/users', ['admin/users', 'admin/tambah-user', 'admin/import-maba', 'admin/audit-log']];
    }
}

$flash_success = $_GET['success'] ?? null;
$flash_error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> | MABAVERSE</title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
<script nonce="<?= defined('CSP_NONCE') ? e(CSP_NONCE) : '' ?>">
(function () {
    try {
        var t = localStorage.getItem('mabaverse-theme');
        if (t === 'dark' || t === 'light') {
            document.documentElement.setAttribute('data-theme', t);
        }
    } catch (err) {}
})();
</script>
</head>
<body class="role-<?= e($user_role !== '' ? $user_role : 'public') ?>">
<a class="skip-link" href="#konten">Lompat ke konten utama</a>

<header class="topbar">
    <div class="topbar-inner">
        <a class="brand" href="<?= BASE_URL ?>">MABAVERSE<span class="brand-sub">Portal Ospek Mahasiswa Baru</span></a>

        <?php if ($user_id): ?>
        <div class="topbar-actions">
            <span class="user-chip">
                <span class="user-chip-nama"><?= e($user_nama) ?></span>
                <span class="user-chip-role"><?= e($role_label) ?></span>
            </span>
            <?php if ($user_role === 'maba'): ?>
            <a class="btn btn-sm btn-ghost" href="<?= BASE_URL ?>profil">Profil</a>
            <?php endif; ?>
            <button type="button" class="btn btn-sm btn-ghost" id="theme-toggle" aria-pressed="false">Mode gelap</button>
            <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>logout">Keluar</a>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($user_id && $nav_items): ?>
    <nav class="mainnav" aria-label="Navigasi utama">
        <?php foreach ($nav_items as $item): ?>
            <?php
            list($label, $href, $prefixes) = $item;
            $is_active = in_array($current_path, $prefixes, true);
            ?>
            <a class="mainnav-link<?= $is_active ? ' is-active' : '' ?>"
               href="<?= BASE_URL . $href ?>"
               <?= $is_active ? 'aria-current="page"' : '' ?>>
                <?= e($label) ?>
                <?php if ($href === 'pengumuman' && $unread_pengumuman > 0): ?>
                <span class="nav-badge" aria-label="<?= (int)$unread_pengumuman ?> pengumuman belum dibaca"><?= (int)$unread_pengumuman ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>
</header>

<?php if ($user_id && $user_role === 'maba' && $nav_items): ?>
<nav class="bottomnav" aria-label="Navigasi utama (layar kecil)">
    <?php foreach ($nav_items as $item): ?>
        <?php
        list($label, $href, $prefixes) = $item;
        $is_active = in_array($current_path, $prefixes, true);
        ?>
        <a class="bottomnav-link<?= $is_active ? ' is-active' : '' ?>"
           href="<?= BASE_URL . $href ?>"
           <?= $is_active ? 'aria-current="page"' : '' ?>>
            <span><?= e($label) ?></span>
            <?php if ($href === 'pengumuman' && $unread_pengumuman > 0): ?>
            <span class="nav-badge" aria-label="<?= (int)$unread_pengumuman ?> pengumuman belum dibaca"><?= (int)$unread_pengumuman ?></span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</nav>
<?php endif; ?>

<main id="konten" class="container">
    <?php if ($flash_success): ?>
    <div class="alert alert-success" role="status"><?= e($flash_success) ?></div>
    <?php endif; ?>
    <?php if ($flash_error): ?>
    <div class="alert alert-error" role="alert"><?= e($flash_error) ?></div>
    <?php endif; ?>
