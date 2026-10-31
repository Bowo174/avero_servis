<?php
require_once "koneksi.php";

$token = $_GET["token"] ?? "";

$data = null;
$riwayat = [];

if ($token != "") {
    $sql = "SELECT
                servis.nomor_servis,
                servis.tanggal_masuk,
                servis.status_servis,
                servis.keluhan,
                pelanggan.nama,
                barang.nama_barang,
                barang.merk,
                barang.model
            FROM servis
            JOIN pelanggan
                ON servis.id_pelanggan = pelanggan.id_pelanggan
            JOIN barang
                ON servis.id_barang = barang.id_barang
            WHERE servis.qr_token = ?";

    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($hasil);
if ($data) {

    $id_servis = null;

    $sql_id = "SELECT id_servis
               FROM servis
               WHERE qr_token = ?";

    $stmt_id = mysqli_prepare($koneksi, $sql_id);
    mysqli_stmt_bind_param($stmt_id, "s", $token);
    mysqli_stmt_execute($stmt_id);

    $hasil_id = mysqli_stmt_get_result($stmt_id);
    $data_id = mysqli_fetch_assoc($hasil_id);

    if ($data_id) {
        $id_servis = $data_id["id_servis"];
    }

    if ($id_servis) {

        $sql_riwayat = "SELECT
                    riwayat_status.status,
                    riwayat_status.catatan,
                    riwayat_status.created_at,
                    users.nama AS nama_petugas
                FROM riwayat_status
                LEFT JOIN users
                    ON riwayat_status.id_user = users.id_user
                WHERE riwayat_status.id_servis = ?
                ORDER BY riwayat_status.created_at ASC";

        $stmt_riwayat = mysqli_prepare($koneksi, $sql_riwayat);
        mysqli_stmt_bind_param($stmt_riwayat, "i", $id_servis);
        mysqli_stmt_execute($stmt_riwayat);

        $hasil_riwayat = mysqli_stmt_get_result($stmt_riwayat);

        while ($row = mysqli_fetch_assoc($hasil_riwayat)) {
            $riwayat[] = $row;
        }
    }
}
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Servis - Avero Komputer</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
        }

        .header,
        .card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 15px;
        }

        .header {
            text-align: center;
        }

        .header h1 {
            margin: 0;
        }

        .status {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            padding: 15px;
            border-radius: 10px;
            background: #e0f2fe;
        }

        .row {
            margin: 10px 0;
        }

        .label {
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <h1>AVERO KOMPUTER</h1>
        <p>Komputer - Printer - Service - Network - CCTV</p>
        <p>📞 0852 1818 2280</p>
    </div>

   <?php if ($data) { ?>

    <div class="card">
        <div class="status">
            <?php echo htmlspecialchars($data["status_servis"]); ?>
        </div>
    </div>

    <div class="card">
        <h3>Informasi Servis</h3>

        <div class="row">
            <span class="label">Nomor Servis:</span>
            <?php echo htmlspecialchars($data["nomor_servis"]); ?>
        </div>

        <div class="row">
            <span class="label">Tanggal Masuk:</span>
            <?php echo htmlspecialchars($data["tanggal_masuk"]); ?>
        </div>

        <div class="row">
            <span class="label">Pelanggan:</span>
            <?php echo htmlspecialchars($data["nama"]); ?>
        </div>
    </div>

    <div class="card">
        <h3>Data Barang</h3>

        <div class="row">
            <span class="label">Barang:</span>
            <?php echo htmlspecialchars($data["nama_barang"]); ?>
        </div>

        <div class="row">
            <span class="label">Merk:</span>
            <?php echo htmlspecialchars($data["merk"]); ?>
        </div>

        <div class="row">
            <span class="label">Model:</span>
            <?php echo htmlspecialchars($data["model"]); ?>
        </div>
    </div>

    <div class="card">
        <h3>Keluhan</h3>

        <p>
            <?php echo nl2br(htmlspecialchars($data["keluhan"])); ?>
        </p>
    </div>

    <div class="card">

    <h3 style="
        margin-top:0;
        margin-bottom:20px;
    ">
        Riwayat Servis
    </h3>

    <?php if (count($riwayat) > 0) { ?>

        <?php foreach ($riwayat as $item) { ?>

            <div style="
                position:relative;
                margin:0 0 18px 0;
                padding-left:25px;
                border-left:2px solid #0ea5e9;
            ">

                <!-- TITIK TIMELINE -->
                <div style="
                    position:absolute;
                    left:-7px;
                    top:0;
                    width:10px;
                    height:10px;
                    background:#0ea5e9;
                    border-radius:50%;
                    border:3px solid white;
                "></div>

                <!-- STATUS -->
                <div style="
                    font-weight:bold;
                    font-size:16px;
                    margin-bottom:4px;
                ">
                    <?php echo htmlspecialchars($item["status"]); ?>
                </div>

                <!-- WAKTU -->
                <div style="
                    font-size:12px;
                    color:#64748b;
                    margin-bottom:3px;
                ">
                    🕐 <?php echo date("d-m-Y H:i", strtotime($item["created_at"])); ?>
                </div>

                <!-- PETUGAS -->
                <div style="
                    font-size:12px;
                    color:#64748b;
                ">
                    👤 <?php echo htmlspecialchars($item["nama_petugas"] ?? ""); ?>
                </div>

                <!-- CATATAN -->
                <?php if (!empty($item["catatan"])) { ?>

                    <div style="
                        margin-top:5px;
                        font-size:12px;
                        color:#475569;
                    ">
                        <?php echo htmlspecialchars($item["catatan"]); ?>
                    </div>

                <?php } ?>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>Belum ada riwayat servis.</p>

    <?php } ?>

</div>

<?php } ?>

</div>

</body>
</html>