<?php
require_once __DIR__ . '/app_helper.php';
require_once "koneksi.php";

$token = $_GET["token"] ?? "";

$data = null;
$riwayat = [];
$riwayatTotal = 0;
$riwayatPage = max(1, (int) ($_GET['page'] ?? 1));
$riwayatLimit = pageLimit();

if ($token != "") {
    $sql = "SELECT
                servis.nomor_servis,
                servis.tanggal_masuk,
                servis.status_servis,
                servis.keluhan,
                pelanggan.nama,
                barang.nama_barang
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

            $stmt_count = mysqli_prepare($koneksi, 'SELECT COUNT(*) total FROM riwayat_status WHERE id_servis=?');
            mysqli_stmt_bind_param($stmt_count, 'i', $id_servis);
            mysqli_stmt_execute($stmt_count);
            $riwayatTotal = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_count))['total'] ?? 0);
            $riwayatOffset = ($riwayatPage - 1) * $riwayatLimit;
            $sql_riwayat = "SELECT
                    riwayat_status.status,
                    riwayat_status.catatan,
                    riwayat_status.created_at,
                    users.nama AS nama_petugas
                FROM riwayat_status
                LEFT JOIN users
                    ON riwayat_status.id_user = users.id_user
                WHERE riwayat_status.id_servis = ?
                ORDER BY riwayat_status.created_at ASC LIMIT ? OFFSET ?";

            $stmt_riwayat = mysqli_prepare($koneksi, $sql_riwayat);
            mysqli_stmt_bind_param($stmt_riwayat, "iii", $id_servis, $riwayatLimit, $riwayatOffset);
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

    <link rel="stylesheet" href="style/status_servis.css?v=2">
    <link rel="stylesheet" href="style/responsive.css?v=3">
</head>

<body><?php include 'navigasi.php'; ?>

    <div class="container">

        <div class="header">
            <h1>AVERO KOMPUTER</h1>
            <p>Komputer - Printer - Service - Network - CCTV</p>
            <p>0852 1818 2280</p>
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

            </div>

            <div class="card">
                <h3>Keluhan</h3>

                <p>
                    <?php echo nl2br(htmlspecialchars($data["keluhan"])); ?>
                </p>
            </div>

            <div class="card">

                <h3 class="inline-cbf69058">
                    Riwayat Servis
                </h3>

                <?php if (count($riwayat) > 0) { ?>

                    <?php foreach ($riwayat as $item) { ?>

                        <div class="inline-b60d0ba1">

                            <!-- TITIK TIMELINE -->
                            <div class="inline-9873bb35"></div>

                            <!-- STATUS -->
                            <div class="inline-14fe32e7">
                                <?php echo htmlspecialchars($item["status"]); ?>
                            </div>

                            <!-- WAKTU -->
                            <div class="inline-196fc7ee">
                                ðŸ• <?php echo date("d-m-Y H:i", strtotime($item["created_at"])); ?>
                            </div>

                            <!-- PETUGAS -->
                            <div class="inline-ad66386e">
                                ðŸ‘¤ <?php echo htmlspecialchars($item["nama_petugas"] ?? ""); ?>
                            </div>

                            <!-- CATATAN -->
                            <?php if (!empty($item["catatan"])) { ?>

                                <div class="inline-945dfac3">
                                    <?php echo htmlspecialchars($item["catatan"]); ?>
                                </div>

                            <?php } ?>

                        </div>

                    <?php } ?>

                <?php } else { ?>

                    <p>Belum ada riwayat servis.</p>

                <?php } ?>

                <?php renderPagination($riwayatTotal, $riwayatPage, ['token' => $token]); ?>

            </div>

        <?php } ?>

    </div>

</body>

</html>
