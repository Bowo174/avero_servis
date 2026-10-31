<?php

session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

require_once "koneksi.php";
$sql = "SELECT
            barang.id_barang,
            barang.nama_barang,
            barang.merk,
            barang.model,
            barang.kelengkapan,
            pelanggan.nama
        FROM barang
        JOIN pelanggan
            ON barang.id_pelanggan = pelanggan.id_pelanggan
        ORDER BY barang.id_barang DESC";

$hasil = mysqli_query($koneksi, $sql);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Barang - Avero Komputer</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        h1 {
            color: #0f172a;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
    </style>
</head>

<body>

<div class="container">

    <h1>📦 Data Barang</h1>

    <div class="card" style="overflow-x:auto;">

    <table style="width:100%; border-collapse:collapse; min-width:800px;">

        <tr style="background:#f8fafc;">

            <th style="padding:12px; text-align:left;">
                Barang
            </th>

            <th style="padding:12px; text-align:left;">
                Merk
            </th>

            <th style="padding:12px; text-align:left;">
                Model
            </th>

            <th style="padding:12px; text-align:left;">
                Pemilik
            </th>

            <th style="padding:12px; text-align:left;">
                Kelengkapan
            </th>

        </tr>

        <?php while ($row = mysqli_fetch_assoc($hasil)) { ?>

            <tr style="border-top:1px solid #e2e8f0;">

                <td style="padding:12px;">
                    <?php echo htmlspecialchars($row["nama_barang"]); ?>
                </td>

                <td style="padding:12px;">
                    <?php echo htmlspecialchars($row["merk"]); ?>
                </td>

                <td style="padding:12px;">
                    <?php echo htmlspecialchars($row["model"]); ?>
                </td>

                <td style="padding:12px;">
                    <?php echo htmlspecialchars($row["nama"]); ?>
                </td>

                <td style="padding:12px;">
                    <?php echo htmlspecialchars($row["kelengkapan"]); ?>
                </td>

            </tr>

        <?php } ?>

    </table>

</div>

</body>
</html>