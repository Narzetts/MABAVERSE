<?php
// Installer MABAVERSE: membuat database, menjalankan schema, lalu mengisi data contoh.
// Jalankan dari browser: http://localhost/MABAVERSE/install.php
// Hapus atau blokir file ini setelah instalasi pada lingkungan produksi.

require_once __DIR__ . '/config/database.php';

// Kunci installer: setelah instalasi sukses, file ini menolak semua akses
// sampai config/installed.lock dihapus manual oleh operator.
$lock_file = __DIR__ . '/config/installed.lock';
if (file_exists($lock_file)) {
    die('Instalasi MABAVERSE sudah terkunci (file config/installed.lock ada), installer menolak dijalankan. '
        . 'Hapus file config/installed.lock secara manual bila benar-benar ingin memasang ulang, '
        . 'dan pastikan install.php sudah dihapus dari server produksi.');
}

$messages = [];
$errors = [];

function splitSqlStatements(string $sql): array {
    $statements = [];
    $current = '';
    $inString = false;
    $panjang = strlen($sql);

    for ($i = 0; $i < $panjang; $i++) {
        $char = $sql[$i];

        if ($inString) {
            $current .= $char;
            if ($char === '\\' && $i + 1 < $panjang) {
                // Escape: ikutkan karakter berikutnya apa adanya.
                $i++;
                $current .= $sql[$i];
            } elseif ($char === "'") {
                $inString = false;
            }
        } else {
            if ($char === "'") {
                $inString = true;
                $current .= $char;
            } elseif ($char === ';') {
                $statements[] = $current;
                $current = '';
            } else {
                $current .= $char;
            }
        }
    }

    if (trim($current) !== '') {
        $statements[] = $current;
    }

    return $statements;
}

function runSqlFile(PDO $pdo, string $path, array &$messages, array &$errors) {
    $sql = file_get_contents($path);
    if ($sql === false) {
        $errors[] = "Tidak bisa membaca file: " . basename($path);
        return;
    }

    $statements = splitSqlStatements($sql);
    $berhasil = 0;
    foreach ($statements as $statement) {
        // Buang baris komentar, tapi jangan buang statement yang didahului komentar.
        $lines = [];
        foreach (explode("\n", $statement) as $line) {
            if (strpos(trim($line), '--') !== 0) {
                $lines[] = $line;
            }
        }
        $clean = trim(implode("\n", $lines));
        if ($clean === '') {
            continue;
        }

        try {
            $pdo->exec($clean);
            $berhasil++;
        } catch (PDOException $ex) {
            $errors[] = 'Gagal: ' . mb_substr($clean, 0, 80) . '... (' . $ex->getMessage() . ')';
        }
    }

    $messages[] = basename($path) . ': ' . $berhasil . ' perintah berhasil dijalankan.';
}

$mode = $_GET['mode'] ?? '';
$sudah_jalan = false;

if ($mode === 'install' || $mode === 'reset') {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        if ($mode === 'reset') {
            $pdo->exec('DROP DATABASE IF EXISTS ' . DB_NAME);
            $messages[] = 'Database lama dihapus.';
        }

        runSqlFile($pdo, __DIR__ . '/database/schema.sql', $messages, $errors);
        runSqlFile($pdo, __DIR__ . '/database/seed.sql', $messages, $errors);

        if (empty($errors)) {
            file_put_contents($lock_file, 'Terpasang pada ' . date('Y-m-d H:i:s') . "\n");
            $messages[] = 'Installer dikunci: file config/installed.lock dibuat.';
        }

        $sudah_jalan = true;
    } catch (PDOException $ex) {
        $errors[] = 'Koneksi database gagal: ' . $ex->getMessage()
            . ' — periksa kredensial di config/config.local.php (atau default config/config.php) dan pastikan MySQL berjalan.';
    }
}

function cekTabel() {
    try {
        // Koneksi tanpa nama database supaya tidak mati saat database belum ada.
        $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $ada = $pdo->query('SHOW DATABASES LIKE ' . $pdo->quote(DB_NAME))->fetchColumn();
        if (!$ada) {
            return ['ok' => false, 'pesan' => 'Database ' . DB_NAME . ' belum ada.'];
        }

        $pdo->exec('USE `' . DB_NAME . '`');
        $tabel = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $maba = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'maba'")->fetchColumn();
        return ['ok' => true, 'tabel' => count($tabel), 'maba' => $maba];
    } catch (Throwable $e) {
        return ['ok' => false, 'pesan' => $e->getMessage()];
    }
}

$status = cekTabel();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Instalasi MABAVERSE</title>
<style>
    body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; max-width: 720px; margin: 2rem auto; padding: 0 1rem; color: #241f1c; background: #F6F1E9; }
    h1 { font-size: 1.5rem; }
    .box { background: #fff; border: 1px solid #e2d9cc; border-radius: 10px; padding: 1rem 1.25rem; margin: 1rem 0; }
    .ok { border-left: 4px solid #2e7d32; }
    .err { border-left: 4px solid #b3261e; }
    ul { margin: 0.5rem 0 0; padding-left: 1.2rem; }
    li { margin: 0.25rem 0; }
    code { background: #efe8dd; padding: 0.1rem 0.35rem; border-radius: 4px; }
    .btn { display: inline-block; background: #7A1F2B; color: #fff; text-decoration: none; padding: 0.6rem 1rem; border-radius: 8px; margin-right: 0.5rem; }
    .btn-muted { background: #5b534b; }
</style>
</head>
<body>
<h1>Instalasi MABAVERSE</h1>

<?php if ($status['ok']): ?>
<div class="box ok">
    <strong>Database terdeteksi.</strong>
    <?= (int)$status['tabel'] ?> tabel, <?= (int)$status['maba'] ?> akun mahasiswa baru.
    Anda bisa langsung <a href="login">masuk</a>, atau jalankan ulang instalasi di bawah.
</div>
<?php else: ?>
<div class="box">
    <strong>Database belum terpasang.</strong>
    Klik <em>Pasang sekarang</em> untuk membuat database <code>mabaverse</code>, menjalankan skema, lalu mengisi data contoh.
</div>
<?php endif; ?>

<?php foreach ($messages as $m): ?>
<div class="box ok"><?= htmlspecialchars($m) ?></div>
<?php endforeach; ?>

<?php foreach ($errors as $err): ?>
<div class="box err"><?= htmlspecialchars($err) ?></div>
<?php endforeach; ?>

<p>
    <a class="btn" href="install.php?mode=install">Pasang sekarang</a>
    <a class="btn btn-muted" href="install.php?mode=reset"
       onclick="return confirm('Hapus seluruh database mabaverse lalu pasang ulang dari nol?');">Reset database</a>
</p>

<p>Setelah selesai, login dengan salah satu akun contoh (password semuanya <code>admin123</code>):</p>
<ul>
    <li><code>ADMIN001</code> — superadmin</li>
    <li><code>ADM001</code> — admin ospek</li>
    <li><code>PAN001</code> — panitia kelompok A</li>
    <li><code>2026001</code> — mahasiswa baru</li>
</ul>

<p>Amankan server produksi: <strong>hapus file <code>install.php</code> secara manual</strong> setelah instalasi selesai. Sebelum dihapus, installer sudah otomatis menolak akses karena <code>config/installed.lock</code> aktif — kunci itu hanya perlu dihapus bila operator memang ingin memasang ulang dari nol.</p>
</body>
</html>
