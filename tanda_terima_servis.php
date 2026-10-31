<?php
require_once "koneksi.php";

$nomor_servis = $_GET["nomor"] ?? "";

$sql = "SELECT 
            servis.id_servis,
            servis.nomor_servis,
            servis.tanggal_masuk,
            servis.status_servis,
            servis.keluhan,
            servis.qr_token,
            pelanggan.nama,
            pelanggan.no_telp,
            pelanggan.alamat,
            barang.nama_barang,
            barang.merk,
            barang.model,
            barang.kelengkapan
        FROM servis
        JOIN pelanggan 
            ON servis.id_pelanggan = pelanggan.id_pelanggan
        JOIN barang 
            ON servis.id_barang = barang.id_barang
        WHERE servis.nomor_servis = ?";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "s", $nomor_servis);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($hasil);

$rincian_servis = [];
$total_rincian = 0;
if ($data) {
    $sql_rincian = "SELECT keterangan, jumlah, harga_satuan FROM detail_servis WHERE id_servis = ? ORDER BY id_detail_servis ASC";
    $stmt_rincian = mysqli_prepare($koneksi, $sql_rincian);
    mysqli_stmt_bind_param($stmt_rincian, "i", $data["id_servis"]);
    mysqli_stmt_execute($stmt_rincian);
    $hasil_rincian = mysqli_stmt_get_result($stmt_rincian);

    while ($baris_rincian = mysqli_fetch_assoc($hasil_rincian)) {
        $baris_rincian["total"] = (int)$baris_rincian["jumlah"] * (int)$baris_rincian["harga_satuan"];
        $total_rincian += $baris_rincian["total"];
        $rincian_servis[] = $baris_rincian;
    }
}


/* Ambil pengaturan toko */

$sql_toko = "SELECT
                nama_toko,
                alamat,
                no_telp,
                logo,
                syarat_ketentuan,
                footer
             FROM pengaturan_toko
             ORDER BY id_pengaturan DESC
             LIMIT 1";

$hasil_toko = mysqli_query($koneksi, $sql_toko);

$data_toko = mysqli_fetch_assoc($hasil_toko);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanda Terima Servis</title>
    <!-- <link rel="stylesheet" href="style/tandaTerimastyle.css?v=<?php echo filemtime(__DIR__ . '/style/tandaTerimastyle.css'); ?>"> -->
     <link rel="stylesheet" href="style/tandapenerima.css?v=<?php echo filemtime(__DIR__ . '/style/tandapenerima.css'); ?>">
</head>

<body>

<div class="print-area">
<div class="nota">
<div class="nota-inner">

    <!-- ================= KOLOM KIRI: INFO TOKO + QR + STATUS ================= -->
    <div class="sidebar">

        <div class="brand">

            <?php if (!empty($data_toko["logo"])) { ?>
                <img
                    class="logo"
                    src="uploads/logo/<?php echo htmlspecialchars($data_toko["logo"]); ?>"
                    alt="Logo Avero Komputer"
                >
            <?php } ?>

            <h1>AVERO KOMPUTER</h1>
            <p>Komputer - Printer - Service - Network - CCTV</p>
            <p><?php echo htmlspecialchars($data_toko["alamat"] ?? ""); ?></p>
            <p>📞 <?php echo htmlspecialchars($data_toko["no_telp"] ?? ""); ?></p>

        </div>

        <div class="qr-box">
            <p>SCAN CEK STATUS</p>

            <?php
            $url_status = "https://averokomputer.my.id/status_servis.php?token=" . $data["qr_token"];
            ?>

            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo urlencode($url_status); ?>"
                alt="QR Code Status Servis"
            >
        </div>

        <div class="status-box">

            <div class="info-row">
                <span class="label-mini">Nomor Servis</span>
                <strong><?php echo htmlspecialchars($data["nomor_servis"]); ?></strong>
            </div>

            <div class="info-row">
                <span class="label-mini">Tanggal Masuk</span>
                <strong><?php echo date("d-m-Y H:i", strtotime($data["tanggal_masuk"])); ?></strong>
            </div>

            <div class="info-row">
                <span class="label-mini">Status</span>
                <span class="badge"><?php echo htmlspecialchars($data["status_servis"]); ?></span>
            </div>

        </div>

    </div>

    <!-- ================= KOLOM KANAN: DETAIL SERVIS ================= -->
    <div class="main">

        <h2 class="judul">Tanda Terima Service</h2>

        <div class="content-grid">

            <div class="data-block">
                <h3>Data Pelanggan</h3>

                <div class="row">
                    <span class="label">Nama</span>
                    <span class="separator">:</span>
                    <strong class="val"><?php echo htmlspecialchars($data["nama"]); ?></strong>
                </div>
                <hr>
                <div class="row">
                    <span class="label">WhatsApp</span>
                    <span class="separator">:</span>
                    <?php echo htmlspecialchars($data["no_telp"]); ?>
                </div>
                <hr>
                <div class="row">
                    <span class="label">Alamat</span>
                    <span class="separator">:</span>
                    <?php echo nl2br(htmlspecialchars($data["alamat"])); ?>
                </div>
            </div>
            <hr>
            <div class="data-block">
                <h3>Data Barang</h3>

                <div class="row">
                    <span class="label">Barang</span>
                    <span class="separator">:</span>
                    <strong class="val"><?php echo htmlspecialchars($data["nama_barang"]); ?></strong>
                </div>
                <hr>
                <div class="row">
                    <span class="label">Merk</span>
                    <span class="separator">:</span>
                    <?php echo htmlspecialchars($data["merk"]); ?>
                </div>
                <hr>
                <div class="row">
                    <span class="label">Model/Tipe</span>
                    <span class="separator">:</span>
                    <?php echo htmlspecialchars($data["model"]); ?>
                </div>
                <hr>
                <div class="row">
                    <span class="label">Kelengkapan</span>
                    <span class="separator">:</span>
                    <?php echo htmlspecialchars($data["kelengkapan"]); ?>
                </div>
                <hr>
                <div class="row">
                    <span class="label">Keluhan</span>
                    <span class="separator">:</span>
                    <?php echo nl2br(htmlspecialchars($data["keluhan"] ?? "")); ?>
                </div>
            </div>

        </div>

        <!-- ================= TABEL RINCIAN BIAYA (masih kosong) ================= -->
        <!-- <div class="rincian-box">
            <h3>Rincian Biaya</h3>

            <table class="rincian-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Keterangan</th>
                        <th class="num">Jumlah</th>
                        <th class="num">Harga Satuan</th>
                        <th class="num">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rincian_servis as $index => $baris_rincian) { ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($baris_rincian["keterangan"]); ?></td>
                            <td class="num"><?php echo number_format((int)$baris_rincian["jumlah"], 0, ",", "."); ?></td>
                            <td class="num">Rp <?php echo number_format((int)$baris_rincian["harga_satuan"], 0, ",", "."); ?></td>
                            <td class="num">Rp <?php echo number_format($baris_rincian["total"], 0, ",", "."); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4">TOTAL</td>
                        <td class="num">Rp <?php echo number_format($total_rincian, 0, ",", "."); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div> -->

        <div class="syarat-box">
            <h3>Syarat & Ketentuan</h3>
            <div class="syarat-text"><?php echo htmlspecialchars(trim($data_toko["syarat_ketentuan"] ?? "")); ?></div>
        </div>

        <div class="footer-text">
            <?php echo htmlspecialchars($data_toko["footer"] ?? ""); ?>
        </div>
        <br>
        <br>
        <div class="signature-block" style="text-align: right;">
            <p>Hormat Kami</p>
            <br>
            <br>
            <div class="signature-line"></div>
        </div>

    </div>

</div>
</div>
</div>

</body>
</html>
