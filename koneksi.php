<?php
date_default_timezone_set('Asia/Jakarta');
$requestHost = strtolower((string) ($_SERVER['SERVER_NAME'] ?? ''));
$localHost = in_array($requestHost, ['localhost', '127.0.0.1', '::1'], true);
if (!$localHost) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

$configPath = __DIR__ . '/config.local.php';
$config = null;
if (is_file($configPath)) {
    $config = require $configPath;
} else {
    $isLocalDevelopment = PHP_SAPI === 'cli' || $localHost;
    if (!$isLocalDevelopment) {
        http_response_code(500);
        error_log('Avero Service: config.local.php belum dibuat.');
        exit('Konfigurasi aplikasi belum lengkap. Hubungi administrator.');
    }
    $config = [
        'db_host' => 'localhost',
        'db_user' => 'root',
        'db_password' => '',
        'db_name' => 'avero_servis',
        'app_base_url' => 'http://localhost/avero_servis',
        'whatsapp_webhook_verify_token' => '',
        'whatsapp_app_secret' => ''
    ];
}

if (!is_array($config)) {
    http_response_code(500);
    error_log('Avero Service: format config.local.php tidak valid.');
    exit('Konfigurasi aplikasi tidak valid. Hubungi administrator.');
}

define('AVERO_APP_BASE_URL', rtrim((string) ($config['app_base_url'] ?? ''), '/'));

$host = (string) ($config['db_host'] ?? '');
$user = (string) ($config['db_user'] ?? '');
$password = (string) ($config['db_password'] ?? '');
$database = (string) ($config['db_name'] ?? '');

mysqli_report(MYSQLI_REPORT_OFF);
$koneksi = mysqli_connect($host, $user, $password, $database);
if (!$koneksi) {
    error_log('Avero Service database connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    exit($localHost ? 'Koneksi database gagal. Periksa konfigurasi MySQL lokal.' : 'Aplikasi sedang mengalami gangguan. Hubungi administrator.');
}

mysqli_set_charset($koneksi, 'utf8mb4');
$koneksi->query("SET time_zone = '+07:00'");
