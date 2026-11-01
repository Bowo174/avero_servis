<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once 'koneksi.php';

$nama = trim((string) (getenv('AVERO_ADMIN_NAME') ?: 'Admin Avero'));
$username = trim((string) getenv('AVERO_ADMIN_USER'));
$password = (string) getenv('AVERO_ADMIN_PASSWORD');
$role = 'Admin';
if ($username === '' || strlen($password) < 12) {
    fwrite(STDERR, "Set AVERO_ADMIN_USER dan AVERO_ADMIN_PASSWORD (minimal 12 karakter) sebelum menjalankan script ini.\n");
    exit(1);
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users
        (nama, username, password, role, aktif)
        VALUES (?, ?, ?, ?, 1)";

$stmt = mysqli_prepare($koneksi, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ssss",
    $nama,
    $username,
    $password_hash,
    $role
);

if (mysqli_stmt_execute($stmt)) {
    echo "Akun admin berhasil dibuat.\n";
} else {
    fwrite(STDERR, "Akun admin gagal dibuat. Pastikan username belum digunakan.\n");
    exit(1);
}
