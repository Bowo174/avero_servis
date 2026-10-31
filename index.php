<?php
require_once "koneksi.php";
?>

<?php

session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avero Komputer - Dashboard</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .header {
            background: #1f2937;
            color: white;
            padding: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0 0;
        }

        .container {
            padding: 20px;
        }

        .welcome {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }

        .menu a {
            background: white;
            padding: 25px 15px;
            text-align: center;
            text-decoration: none;
            color: #1f2937;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            font-weight: bold;
        }

        .menu a:hover {
            background: #e5e7eb;
        }
    </style>
</head>

<body>

<div class="header">
    <h1>Avero Komputer</h1>
    <p>Komputer - Printer - Service - Network - CCTV</p>
</div>

<div class="container">

    <div class="welcome">
        <h2>Dashboard</h2>
        <p>Selamat datang di Sistem Servis Avero Komputer.</p>
    </div>

    <div class="menu">
        <a href="tambah_servis.php"➕<br><br>Tambah Servis</a>
        <a href="data_servis.php"📋<br><br>Data Servis</a>
        <a href="pelanggan.php"👤<br><br>Pelanggan</a>
        <a href="barang.php">💻<br><br>Barang</a>
        <a href="status.php">🔄<br><br>Status Servis</a>
        <a href="pengaturan.php">⚙️<br><br>Pengaturan</a>
    </div>

</div>

</body>
</html>