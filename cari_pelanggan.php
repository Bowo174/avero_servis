<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/app_helper.php';
requireLogin();
require_once __DIR__ . '/koneksi.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
$query = trim((string) ($_GET['q'] ?? ''));
if (function_exists('mb_strlen') ? mb_strlen($query, 'UTF-8') < 2 : strlen($query) < 2) {
    echo json_encode([]);
    exit;
}
$like = '%' . $query . '%';
$stmt = mysqli_prepare($koneksi, 'SELECT id_pelanggan,nama,no_telp FROM pelanggan WHERE nama LIKE ? ORDER BY nama LIMIT 5');
mysqli_stmt_bind_param($stmt, 's', $like);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$customers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $customers[] = ['id' => (int) $row['id_pelanggan'], 'nama' => $row['nama'], 'no_telp' => $row['no_telp']];
}
echo json_encode($customers, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
