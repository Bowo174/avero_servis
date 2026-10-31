<?php

require_once "koneksi.php";

$nama = "Admin Avero";
$username = "admin";
$password = "Avero123!";
$role = "Admin";

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

    echo "Akun admin berhasil dibuat.<br>";
    echo "Username: admin<br>";
    echo "Password: Avero123!";

} else {

    echo "Gagal membuat akun admin: " . mysqli_error($koneksi);

}

?>