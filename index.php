<?php
require_once __DIR__ . '/config/config.php';

// --- URL parsing ---------------------------------------------------------
$segments = [];
if (isset($_GET['url']) && trim($_GET['url'], '/') !== '') {
    $segments = array_map(function ($s) {
        return preg_replace('/[^A-Za-z0-9_\-]/', '', $s);
    }, explode('/', trim($_GET['url'], '/')));
    $segments = array_values(array_filter($segments, function ($s) {
        return $s !== '';
    }));
}

// Route table: key => [controller, method, methodWithArg (optional)]
$routes = [
    // Publik / auth
    'login'                  => ['AuthController', 'login'],
    'logout'                 => ['AuthController', 'logout'],
    'forgot-password'        => ['AuthController', 'forgotPassword'],
    'reset-password'         => ['AuthController', 'resetPassword'],
    'forbidden'              => ['BaseController', 'forbidden'],
    '404'                    => ['BaseController', 'notFound'],

    // Maba
    'dashboard'              => ['MabaController', 'dashboard'],
    'timeline'               => ['MabaController', 'timeline'],
    'detail-sesi'            => ['MabaController', null, 'detailSesi'],
    'absen'                  => ['MabaController', null, 'absen'],
    'tugas'                  => ['MabaController', 'tugas'],
    'submit-tugas'           => ['MabaController', null, 'submitTugas'],
    'riwayat-tugas'          => ['MabaController', 'riwayatTugas'],
    'pengumuman'             => ['MabaController', 'pengumuman'],
    'detail-pengumuman'      => ['MabaController', null, 'detailPengumuman'],
    'status-pengesahan'      => ['MabaController', 'statusPengesahan'],
    'profil'                 => ['MabaController', 'profil'],
    'ubah-password'          => ['MabaController', 'ubahPassword'],

    // Panitia
    'panitia'                => ['PanitiaController', 'dashboard'],
    'panitia/dashboard'      => ['PanitiaController', 'dashboard'],
    'panitia/kelompok'       => ['PanitiaController', 'kelompok'],
    'panitia/absensi'        => ['PanitiaController', 'absensi', 'detailAbsensi'],
    'panitia/verifikasi-absensi' => ['PanitiaController', null, 'verifikasiAbsensi'],
    'panitia/tugas'          => ['PanitiaController', 'tugas', 'detailTugas'],
    'panitia/verifikasi-tugas'   => ['PanitiaController', null, 'verifikasiTugas'],
    'panitia/pengumuman'     => ['PanitiaController', 'pengumuman'],
    'panitia/tambah-pengumuman'  => ['PanitiaController', 'tambahPengumuman'],

    // Admin
    'admin'                  => ['AdminController', 'dashboard'],
    'admin/dashboard'        => ['AdminController', 'dashboard'],
    'admin/timeline'         => ['AdminController', 'timeline'],
    'admin/tambah-tahap'     => ['AdminController', 'tambahTahap'],
    'admin/edit-tahap'       => ['AdminController', null, 'editTahap'],
    'admin/hapus-tahap'      => ['AdminController', null, 'hapusTahap'],
    'admin/sesi'             => ['AdminController', 'sesi'],
    'admin/tambah-sesi'      => ['AdminController', null, 'tambahSesi'],
    'admin/edit-sesi'        => ['AdminController', null, 'editSesi'],
    'admin/hapus-sesi'       => ['AdminController', null, 'hapusSesi'],
    'admin/tugas'            => ['AdminController', 'tugas'],
    'admin/tambah-tugas'     => ['AdminController', 'tambahTugas'],
    'admin/edit-tugas'       => ['AdminController', null, 'editTugas'],
    'admin/hapus-tugas'      => ['AdminController', null, 'hapusTugas'],
    'admin/rekap-absensi'    => ['AdminController', 'rekapAbsensi'],
    'admin/verifikasi-absensi'   => ['AdminController', null, 'verifikasiAbsensi'],
    'admin/rekap-tugas'      => ['AdminController', 'rekapTugas'],
    'admin/verifikasi-tugas' => ['AdminController', null, 'verifikasiTugas'],
    'admin/pengumuman'       => ['AdminController', 'pengumuman'],
    'admin/tambah-pengumuman'    => ['AdminController', 'tambahPengumuman'],
    'admin/edit-pengumuman'  => ['AdminController', null, 'editPengumuman'],
    'admin/hapus-pengumuman' => ['AdminController', null, 'hapusPengumuman'],
    'admin/users'            => ['AdminController', 'users'],
    'admin/tambah-user'      => ['AdminController', 'tambahUser'],
    'admin/import-maba'      => ['AdminController', 'importMaba'],
    'admin/pengesahan'       => ['AdminController', 'pengesahan'],
    'admin/proses-pengesahan'    => ['AdminController', null, 'prosesPengesahan'],
    'admin/proses-semua-pengesahan' => ['AdminController', 'prosesSemuaPengesahan'],
    'admin/export-absensi'   => ['AdminController', 'exportAbsensi'],
    'admin/export-tugas'     => ['AdminController', 'exportTugas'],
    'admin/audit-log'        => ['AdminController', 'auditLog'],
    'admin/reset-permintaan' => ['AdminController', 'resetPermintaan'],
    'admin/pengaturan'       => ['AdminController', 'pengaturan'],
];

// --- Default entry point -------------------------------------------------
if (empty($segments)) {
    if (isset($_SESSION['user_id'])) {
        switch ($_SESSION['user_role']) {
            case 'maba':
                header('Location: ' . BASE_URL . 'dashboard');
                break;
            case 'panitia':
                header('Location: ' . BASE_URL . 'panitia/dashboard');
                break;
            default:
                header('Location: ' . BASE_URL . 'admin/dashboard');
        }
    } else {
        header('Location: ' . BASE_URL . 'login');
    }
    exit;
}

// --- Route matching (dua segmen dulu, lalu satu segmen) ------------------
$route = null;
$arg = null;

if (count($segments) >= 2) {
    $key = $segments[0] . '/' . $segments[1];
    if (isset($routes[$key])) {
        $route = $routes[$key];
        $arg = $segments[2] ?? null;
    }
}

if ($route === null && isset($routes[$segments[0]])) {
    $route = $routes[$segments[0]];
    $arg = $segments[1] ?? null;
}

// --- API -----------------------------------------------------------------
if ($route === null && $segments[0] === 'api') {
    require_once __DIR__ . '/controllers/ApiController.php';
    $api = new ApiController();
    $method = $segments[1] ?? null;
    if ($method !== null && method_exists($api, $method)) {
        $api->$method();
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Endpoint tidak ditemukan']);
    }
    exit;
}

// --- Dispatch ------------------------------------------------------------
if ($route === null) {
    require_once __DIR__ . '/controllers/BaseController.php';
    (new BaseController())->notFound();
    exit;
}

list($controllerClass, $method, $methodWithArg) = array_pad($route, 3, null);

if ($arg !== null && $methodWithArg !== null) {
    $method = $methodWithArg;
}

$controllerFile = __DIR__ . '/controllers/' . $controllerClass . '.php';

if ($method === null || !file_exists($controllerFile)) {
    require_once __DIR__ . '/controllers/BaseController.php';
    (new BaseController())->notFound();
    exit;
}

require_once $controllerFile;
$controller = new $controllerClass();

if (!method_exists($controller, $method)) {
    require_once __DIR__ . '/controllers/BaseController.php';
    (new BaseController())->notFound();
    exit;
}

if ($arg !== null) {
    $controller->$method($arg);
} else {
    $controller->$method();
}
