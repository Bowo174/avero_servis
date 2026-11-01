<?php
function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function appUrl($path = '')
{
    $base = defined('AVERO_APP_BASE_URL') ? AVERO_APP_BASE_URL : '';
    if ($base === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $directory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $base = $scheme . '://' . $host . ($directory === '/' ? '' : $directory);
    }
    return rtrim($base, '/') . ($path === '' ? '' : '/' . ltrim($path, '/'));
}

function requireLogin()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        require_once __DIR__ . '/session_bootstrap.php';
    }
    if (empty($_SESSION['id_user'])) {
        header('Location: login.php');
        exit;
    }
}

function csrfToken()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        require_once __DIR__ . '/session_bootstrap.php';
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token = null)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        require_once __DIR__ . '/session_bootstrap.php';
    }
    $expected = $_SESSION['csrf_token'] ?? '';
    $provided = $token ?? ($_POST['csrf_token'] ?? '');
    return $expected !== '' && is_string($provided) && hash_equals($expected, $provided);
}

function csrfField()
{
    echo '<input type="hidden" name="csrf_token" value="' . h(csrfToken()) . '">';
}

function renderBackButton()
{
    $page = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if (in_array($page, ['index.php', 'login.php'], true)) return;
    echo '<a class="app-back" href="index.php">&larr; Kembali</a>';
}

function pageLimit()
{
    return 5;
}

function buildServiceStatusMessage($nama, $nomor, $barang, $status)
{
    $isiStatus = [
        'Diterima' => 'Barang sudah kami terima dan akan segera kami proses untuk pemeriksaan. Mohon menunggu update selanjutnya.',
        'Pemeriksaan' => 'Barang sedang dalam proses pemeriksaan oleh teknisi kami. Mohon menunggu informasi mengenai hasil pemeriksaan selanjutnya.',
        'Menunggu Persetujuan' => 'Hasil pengecekan barang telah selesai. Berdasarkan hasil pengecekan, diperlukan tindakan servis lebih lanjut. Saat ini kami menunggu persetujuan sebelum proses pengerjaan dilanjutkan.',
        'Menunggu Sparepart' => 'Sparepart yang diperlukan untuk proses servis sedang kami persiapkan. Setelah sparepart tersedia, proses pengerjaan akan segera kami lanjutkan. Mohon menunggu update selanjutnya dari kami.',
        'Sparepart Telah Tiba' => 'Sparepart yang diperlukan untuk proses servis telah tiba. Dimohon untuk segera membawa perangkat atau device anda ke toko, supaya proses pengerjaan segera kami lanjutkan.',
        'Dalam Pengerjaan' => 'Barang saat ini sedang dalam proses pengerjaan oleh teknisi Avero Komputer sesuai hasil pemeriksaan dan persetujuan yang telah diberikan. Mohon menunggu update selanjutnya dari kami.',
        'Selesai' => 'Servis barang telah selesai dan sudah dapat diambil. Silakan datang ke Avero Komputer untuk melakukan pengambilan barang. Jangan lupa membawa tanda terima servis sebagai bukti pengambilan.',
        'Sudah Diambil' => 'Servis barang telah selesai dan barang sudah diambil oleh pelanggan. Terima kasih telah mempercayakan kebutuhan servis kepada Avero Komputer.'
    ];

    if (!isset($isiStatus[$status])) {
        return '';
    }

    return "Assalamu'alaikum Wr. Wb. \n" .
        "Kami dari Avero Komputer ingin menginformasikan:\n\n" .
        "Nama       : {$nama}\n" .
        "No. Servis : {$nomor}\n" .
        "Barang     : {$barang}\n" .
        "Status     : {$status}\n\n" .
        $isiStatus[$status] . "\n\n" .
        "Terima kasih atas kesabaran dan kepercayaannya kepada Avero Komputer. \u{1F64F}";
}

function renderPagination($total, $page, $baseQuery, $pageKey = 'page')
{
    $pages = max(1, (int)ceil($total / pageLimit()));
    if ($pages <= 1) return;
    echo '<nav class="pagination" aria-label="Navigasi halaman">';
    for ($i = 1; $i <= $pages; $i++) {
        $query = $baseQuery;
        $query[$pageKey] = $i;
        echo '<a class="' . ($i === (int)$page ? 'active' : '') . '" href="?' . h(http_build_query($query)) . '">' . $i . '</a>';
    }
    echo '</nav>';
}
?>
