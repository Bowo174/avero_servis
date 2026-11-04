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
$stmt = mysqli_prepare($koneksi, 'SELECT s.id_servis,s.nomor_servis,s.status_servis,p.nama,b.nama_barang FROM servis s JOIN pelanggan p ON p.id_pelanggan=s.id_pelanggan JOIN barang b ON b.id_barang=s.id_barang WHERE s.nomor_servis LIKE ? OR p.nama LIKE ? OR b.nama_barang LIKE ? ORDER BY s.id_servis DESC LIMIT 5');
mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$services = [];
while ($row = mysqli_fetch_assoc($result)) {
    $services[] = ['id' => (int) $row['id_servis'], 'nomor_servis' => $row['nomor_servis'], 'status_servis' => $row['status_servis'], 'nama' => $row['nama'], 'nama_barang' => $row['nama_barang']];
}
echo json_encode($services, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
