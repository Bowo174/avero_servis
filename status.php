<?php

session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

require_once "koneksi.php";

$sql = "SELECT
            servis.nomor_servis,
            servis.tanggal_masuk,
            servis.status_servis,
            pelanggan.nama,
            barang.nama_barang,
            barang.merk,
            barang.model
        FROM servis
        JOIN pelanggan
            ON servis.id_pelanggan = pelanggan.id_pelanggan
        JOIN barang
            ON servis.id_barang = barang.id_barang
        ORDER BY servis.id_servis DESC";

$hasil = mysqli_query($koneksi, $sql);

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Status Servis - Avero Komputer</title>

</head>

<body>

<h1>🔧 Status Servis</h1>

<div style="overflow-x:auto;">

    <table style="width:100%; border-collapse:collapse; min-width:800px;">

        <tr style="background:#f8fafc;">

            <th style="padding:12px; text-align:left;">
                No. Servis
            </th>

            <th style="padding:12px; text-align:left;">
                Pelanggan
            </th>

            <th style="padding:12px; text-align:left;">
                Barang
            </th>

            <th style="padding:12px; text-align:left;">
                Status
            </th>

            <th style="padding:12px; text-align:left;">
                Tanggal
            </th>

        </tr>

        <?php while ($row = mysqli_fetch_assoc($hasil)) { ?>

            <tr style="border-top:1px solid #e2e8f0;">

                <td style="padding:12px;">
                    <?php echo htmlspecialchars($row["nomor_servis"]); ?>
                </td>

                <td style="padding:12px;">
                    <?php echo htmlspecialchars($row["nama"]); ?>
                </td>

                <td style="padding:12px;">
                    <?php echo htmlspecialchars($row["nama_barang"]); ?>
                    <?php echo " " . htmlspecialchars($row["merk"]); ?>
                    <?php echo " " . htmlspecialchars($row["model"]); ?>
                </td>

                <td style="padding:12px;">

    <?php
    $status = $row["status_servis"];

    $warna = "#64748b";

    if ($status == "Diterima") {
        $warna = "#f59e0b";
    } elseif ($status == "Pemeriksaan") {
        $warna = "#3b82f6";
    } elseif ($status == "Menunggu Persetujuan") {
        $warna = "#f97316";
    } elseif ($status == "Menunggu Sparepart") {
        $warna = "#8b5cf6";
    } elseif ($status == "Dalam Pengerjaan") {
        $warna = "#0ea5e9";
    } elseif ($status == "Selesai") {
        $warna = "#22c55e";
    } elseif ($status == "Sudah Diambil") {
        $warna = "#475569";
    }
    ?>

    <span style="
        display:inline-block;
        padding:6px 12px;
        border-radius:20px;
        background:<?php echo $warna; ?>;
        color:white;
        font-weight:bold;
        font-size:13px;
    ">
        <?php echo htmlspecialchars($status); ?>
    </span>

</td>
                <td style="padding:12px;">
                    <?php echo date("d-m-Y H:i", strtotime($row["tanggal_masuk"])); ?>
                </td>

            </tr>

        <?php } ?>

    </table>

</div>
</body>

</html>