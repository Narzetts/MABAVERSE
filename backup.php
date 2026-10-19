<?php
// Backup database MABAVERSE ke folder backups/ menggunakan mysqldump.
// Hanya bisa dijalankan dari command line:
//   C:\xampp\php\php.exe C:\xampp\htdocs\MABAVERSE\backup.php
// Untuk backup harian otomatis, daftarkan lewat cron / Windows Task Scheduler
// (contoh perintah lengkap ada di README.md bagian "Backup harian").

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Script backup hanya dapat dijalankan melalui command line.\n");
}

require_once __DIR__ . '/config/config.php';

$backup_dir = __DIR__ . '/backups';
if (!is_dir($backup_dir) && !@mkdir($backup_dir, 0775, true)) {
    fwrite(STDERR, "Gagal membuat folder backups/.\n");
    exit(1);
}

// Lokasi mysqldump: default bawaan XAMPP di Windows, atau perintah mysqldump
// di PATH untuk Linux/Mac. Bisa ditimpa lewat environment MYSQLDUMP_PATH.
$mysqldump = getenv('MYSQLDUMP_PATH');
if (!$mysqldump) {
    $mysqldump = (stripos(PHP_OS, 'WIN') === 0)
        ? 'C:\\xampp\\mysql\\bin\\mysqldump.exe'
        : 'mysqldump';
}

$file = $backup_dir . DIRECTORY_SEPARATOR . 'mabaverse_' . date('Ymd_His') . '.sql';

$command = escapeshellarg($mysqldump)
    . ' --host=' . escapeshellarg(DB_HOST)
    . ' --user=' . escapeshellarg(DB_USER);
if (DB_PASS !== '') {
    $command .= ' --password=' . escapeshellarg(DB_PASS);
}
$command .= ' --single-transaction --default-character-set=' . escapeshellarg(DB_CHARSET)
    . ' ' . escapeshellarg(DB_NAME)
    . ' > ' . escapeshellarg($file);

exec($command, $output, $exit_code);

if ($exit_code === 0 && is_file($file) && filesize($file) > 0) {
    fwrite(STDOUT, 'Backup berhasil: ' . $file . ' (' . number_format(filesize($file) / 1024, 1) . " KB)\n");
    exit(0);
}

// File kosong/parsial dari percobaan yang gagal dibuang supaya tidak terlihat
// seperti backup yang sah.
@unlink($file);
fwrite(STDERR, "Backup gagal (exit code {$exit_code}).\n" . implode("\n", $output) . "\n");
exit(1);
