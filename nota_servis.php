<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once 'app_helper.php';
requireLogin();
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
if (!$data) {
    http_response_code(404);
    exit('Data servis tidak ditemukan.');
}

$rincian_servis = [];
$total_rincian = 0;
if ($data) {
    $sql_rincian = "SELECT keterangan, jumlah, harga_satuan FROM detail_servis WHERE id_servis = ? ORDER BY id_detail_servis ASC";
    $stmt_rincian = mysqli_prepare($koneksi, $sql_rincian);
    mysqli_stmt_bind_param($stmt_rincian, "i", $data["id_servis"]);
    mysqli_stmt_execute($stmt_rincian);
    $hasil_rincian = mysqli_stmt_get_result($stmt_rincian);

    while ($baris_rincian = mysqli_fetch_assoc($hasil_rincian)) {
        $baris_rincian["total"] = (int) $baris_rincian["jumlah"] * (int) $baris_rincian["harga_satuan"];
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
    <link rel="stylesheet" href="style/nota.css?v=<?php echo filemtime(__DIR__ . '/style/nota.css'); ?>">
    <link rel="stylesheet" href="style/responsive.css?v=3">
</head>

<body><?php include 'navigasi.php'; ?>

    <div class="print-area">
        <div class="nota">
            <div class="nota-inner">
                <header class="shop-header">
                    <?php if (!empty($data_toko["logo"])) { ?>
                        <img class="shop-logo" src="uploads/logo/<?php echo htmlspecialchars($data_toko["logo"]); ?>"
                            alt="Logo Avero Komputer">
                    <?php } else { ?>
                        <span class="logo-placeholder"></span>
                    <?php } ?>
                    <div class="shop-info">
                        <h1>AVERO KOMPUTER</h1>
                        <p>Komputer - Printer - Service - Network - CCTV</p>
                        <p><?php echo htmlspecialchars($data_toko["alamat"] ?? ""); ?></p>
                        <p>No. Telp <?php echo htmlspecialchars($data_toko["no_telp"] ?? ""); ?></p>
                    </div>
                    <span class="header-spacer"></span>
                </header>

                <div class="service-meta">
                    <div><strong>Nomor
                            Servis</strong><span><?php echo htmlspecialchars($data["nomor_servis"]); ?></span></div>
                    <div><strong>Tanggal
                            Masuk</strong><span><?php echo date("d-m-Y H:i", strtotime($data["tanggal_masuk"])); ?></span>
                    </div>
                    <div><strong>Status</strong><span
                            class="status-label"><?php echo htmlspecialchars($data["status_servis"]); ?></span></div>
                </div>

                <div class="content-grid">
                    <section class="data-block">
                        <h2>Data Pelanggan</h2>
                        <div class="row"><span class="label">Nama</span><span class="separator">:</span><span
                                class="value"><strong><?php echo htmlspecialchars($data["nama"]); ?></strong></span>
                        </div>
                        <div class="row"><span class="label">WhatsApp</span><span class="separator">:</span><span
                                class="value"><?php echo htmlspecialchars($data["no_telp"]); ?></span></div>
                        <div class="row"><span class="label">Alamat</span><span class="separator">:</span><span
                                class="value"><?php echo nl2br(htmlspecialchars($data["alamat"])); ?></span></div>
                    </section>

                    <section class="data-block">
                        <h2>Data Barang</h2>
                        <div class="row"><span class="label">Barang</span><span class="separator">:</span><span
                                class="value"><strong><?php echo htmlspecialchars($data["nama_barang"]); ?></strong></span>
                        </div>
                        <div class="row"><span class="label">Kelengkapan</span><span class="separator">:</span><span
                                class="value"><?php echo htmlspecialchars($data["kelengkapan"]); ?></span></div>
                        <div class="row"><span class="label">Keluhan</span><span class="separator">:</span><span
                                class="value"><?php echo nl2br(htmlspecialchars($data["keluhan"] ?? "")); ?></span>
                        </div>
                    </section>
                </div>

                <section class="rincian-box">
                    <h2>Rincian Biaya</h2>
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
                                    <td class="num"><?php echo number_format((int) $baris_rincian["jumlah"], 0, ",", "."); ?>
                                    </td>
                                    <td class="num">Rp
                                        <?php echo number_format((int) $baris_rincian["harga_satuan"], 0, ",", "."); ?></td>
                                    <td class="num">Rp <?php echo number_format($baris_rincian["total"], 0, ",", "."); ?>
                                    </td>
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
                </section>

                <section class="syarat-box">
                    <h2>Syarat &amp; Ketentuan</h2>
                    <div class="syarat-text"><?php echo htmlspecialchars(trim($data_toko["syarat_ketentuan"] ?? "")); ?>
                    </div>
                </section>

                <div class="footer-text"><?php echo htmlspecialchars($data_toko["footer"] ?? ""); ?></div>

                <div class="signature-block">
                    <p>Hormat Kami</p>
                    <br>
                    <br>
                    <br>
                    <br>
                    <div class="signature-space"></div>
                    <div class="signature-line"></div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>