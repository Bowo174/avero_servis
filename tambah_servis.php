<?php

session_start();
require_once "koneksi.php";

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
$nomor_servis = "AVR-" . str_pad(
    mysqli_num_rows(mysqli_query($koneksi, "SELECT id_servis FROM servis")) + 1,
    5,
    "0",
    STR_PAD_LEFT
);

    $nama = $_POST["nama"];
    $no_telp = $_POST["no_telp"];
    $alamat = $_POST["alamat"];

    $sql = "INSERT INTO pelanggan (nama, no_telp, alamat)
            VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $nama, $no_telp, $alamat);

    if (mysqli_stmt_execute($stmt)) {

    $id_pelanggan = mysqli_insert_id($koneksi);

$nama_barang = $_POST["nama_barang"];
$merk = $_POST["merk"];
$model = $_POST["model"];
$kelengkapan = $_POST["kelengkapan"];
$keluhan = $_POST["keluhan"];
$status_servis = "Diterima";

$sql_barang = "INSERT INTO barang 
    (id_pelanggan, nama_barang, merk, model, kelengkapan)
    VALUES (?, ?, ?, ?, ?)";

$stmt_barang = mysqli_prepare($koneksi, $sql_barang);

mysqli_stmt_bind_param(
    $stmt_barang,
    "issss",
    $id_pelanggan,
    $nama_barang,
    $merk,
    $model,
    $kelengkapan
);

mysqli_stmt_execute($stmt_barang);

$id_barang = mysqli_insert_id($koneksi);

$sql_servis = "INSERT INTO servis
    (nomor_servis, id_pelanggan, id_barang, keluhan, status_servis, qr_token)
    VALUES (?, ?, ?, ?, ?, ?)";

$qr_token = bin2hex(random_bytes(32));

$stmt_servis = mysqli_prepare($koneksi, $sql_servis);

mysqli_stmt_bind_param(
    $stmt_servis,
    "siisss",
    $nomor_servis,
    $id_pelanggan,
    $id_barang,
    $keluhan,
    $status_servis,
    $qr_token
);

mysqli_stmt_execute($stmt_servis);

$id_servis = mysqli_insert_id($koneksi);

$sql_riwayat = "INSERT INTO riwayat_status
    (id_servis, status, catatan)
    VALUES (?, ?, ?)";

$catatan = "Servis diterima oleh Avero Komputer.";

$stmt_riwayat = mysqli_prepare($koneksi, $sql_riwayat);

mysqli_stmt_bind_param(
    $stmt_riwayat,
    "iss",
    $id_servis,
    $status_servis,
    $catatan
);

mysqli_stmt_execute($stmt_riwayat);

$pesan = "🎉 Servis berhasil disimpan! Nomor Servis: " . $nomor_servis;


}
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Servis - Avero Komputer</title>

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
            max-width: 900px;
            margin: 20px auto;
            padding: 0 15px;
        }

        .card {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .card h2 {
            margin-top: 0;
            color: #1f2937;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .button {
            display: inline-block;
            background: #1f2937;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }

        .button:hover {
            background: #374151;
        }

        @media (max-width: 600px) {
            .row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <h1>Avero Komputer</h1>
    <p>Komputer - Printer - Service - Network - CCTV</p>
</div>

<div class="container">
<form method="POST">

    <div class="card">
        <h2>➕ Tambah Servis</h2>
        <p>Silakan masukkan data pelanggan dan barang yang akan diservis.</p>
    </div>

    <div class="card">
        <h2>👤 Data Pelanggan</h2>

        <label>Nama Pelanggan</label>
        <input type="text" name="nama" placeholder="Masukkan nama pelanggan" required>

        <label>No. WhatsApp</label>
        <input type="text" name="no_telp" placeholder="Contoh: 085218182280" required>

        <label>Alamat</label>
        <textarea name="alamat" placeholder="Masukkan alamat pelanggan"></textarea>
    </div>

    <div class="card">
        <h2>💻 Data Barang</h2>

        <label>Nama Barang</label>
        <input type="text" name="nama_barang" placeholder="Contoh: Laptop" required>

        <div class="row">
            <div>
                <label>Merk</label>
                <input type="text" name="merk" placeholder="Contoh: Acer">
            </div>

            <div>
                <label>Model / Tipe</label>
                <input type="text" name="model" placeholder="Contoh: Aspire 3">
            </div>
        </div>

        <label>Kelengkapan</label>
        <textarea name="kelengkapan" placeholder="Contoh: Charger, tas laptop, mouse"></textarea>

        <label>Foto Barang</label>
        <input type="file" accept="image/*" capture="environment">
    </div>

    <div class="card">
        <h2>📝 Detail Servis</h2>

        <label>Keluhan Pelanggan</label>
        <textarea name="keluhan" placeholder="Tuliskan keluhan atau kerusakan barang" required></textarea>

        <label>Status Awal</label>
        <select>
            <option>Diterima</option>
            <option>Pemeriksaan</option>
            <option>Menunggu Persetujuan</option>
            <option>Dalam Pengerjaan</option>
            <option>Selesai</option>
            <option>Sudah Diambil</option>
        </select>
    </div>

    <div class="card">
        <h2>📄 Syarat & Ketentuan</h2>

        <label>
            <input type="checkbox" style="width:auto;">
            Saya menyetujui syarat dan ketentuan servis.
        </label>
    </div>

    <div class="card">
        <button class="button" type="submit">
            💾 Simpan & Buat Tanda Terima
        </button>
    </div>

</div>

<?php if ($pesan != "") { echo "<div style='padding:15px; margin-top:20px; background:#d1fae5; border-radius:10px; color:#065f46; font-weight:bold;'>$pesan</div>"; } ?>

</from>

</body>
</html>