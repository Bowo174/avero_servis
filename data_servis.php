<?php

session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

require_once "koneksi.php";

$sql = "    SELECT
                servis.id_servis,
                servis.nomor_servis,
                servis.tanggal_masuk,
                servis.status_servis,
                pelanggan.nama,
                pelanggan.no_telp,
                barang.nama_barang,
                barang.merk,
                barang.model
            FROM servis
            JOIN pelanggan
            ON servis.id_pelanggan = pelanggan.id_pelanggan 
            JOIN barang
            ON servis.id_barang = barang.id_barang
            ORDER BY servis.id_servis DESC
        ";

$hasil = mysqli_query($koneksi, $sql);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Servis - Avero Komputer</title>

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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>

<body>

    <div class="container">

        <h1>📋 Data Servis</h1>

        <div class="card" style="overflow-x:auto;">

            <table style="width:100%; border-collapse:collapse; min-width:800px;">

                <tr style="background:#f8fafc;">
                    <th style="padding:12px; text-align:left;">No. Servis</th>
                    <th style="padding:12px; text-align:left;">Pelanggan</th>
                    <th style="padding:12px; text-align:left;">Barang</th>
                    <th style="padding:12px; text-align:left;">Status</th>
                    <th style="padding:12px; text-align:left;">Tanggal Masuk</th>
                    <th style="padding:12px; text-align:left;">Aksi</th>
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
                            <?php
                            echo htmlspecialchars(
                                $row["nama_barang"] . " " .
                                    $row["merk"] . " " .
                                    $row["model"]
                            );
                            ?>
                        </td>

                        <td style="padding:12px;">
                            <?php echo htmlspecialchars($row["status_servis"]); ?>
                        </td>

                        <td style="padding:12px;">
                            <?php
                            echo date(
                                "d-m-Y H:i",
                                strtotime($row["tanggal_masuk"])
                            );
                            ?>
                        </td>
                        <td style="padding:12px; display:flex; gap:8px; align-items:center; flex-wrap:nowrap;">
                            
   <?php
echo "<!-- STATUS: [" . $row["status_servis"] . "] -->";
if ($row["status_servis"] == "Diterima") {
    $pesan_status = "Barang sudah kami terima dan akan segera kami proses untuk pemeriksaan. Mohon menunggu update selanjutnya.";
} elseif ($row["status_servis"] == "Pemeriksaan") {
    $pesan_status = "Barang sedang dalam proses pemeriksaan oleh teknisi kami. Mohon menunggu informasi mengenai hasil pemeriksaan selanjutnya.";
} elseif ($row["status_servis"] == "Menunggu Persetujuan") {
    $pesan_status = "Hasil pengecekan barang telah selesai. Berdasarkan hasil pengecekan, diperlukan tindakan servis lebih lanjut. Saat ini kami menunggu persetujuan sebelum proses pengerjaan dilanjutkan.";
} elseif ($row["status_servis"] == "Menunggu Sparepart") {
    $pesan_status = "Sparepart yang diperlukan untuk proses servis sedang kami persiapkan. Setelah sparepart tersedia, proses pengerjaan akan segera kami lanjutkan. Mohon menunggu update selanjutnya dari kami.";
} elseif ($row["status_servis"] == "Sparepart Telah Tiba") {
    $pesan_status = "Sparepart yang diperlukan untuk proses servis telah tiba. Dimohon untuk segera membawa perangkat atau device anda ke toko, supaya proses pengerjaan segera kami lanjutkan.";
}  elseif ($row["status_servis"] == "Dalam Pengerjaan") {
    $pesan_status = "Barang saat ini sedang dalam proses pengerjaan oleh teknisi Avero Komputer sesuai hasil pemeriksaan dan persetujuan yang telah diberikan. Mohon menunggu update selanjutnya dari kami.";
} elseif ($row["status_servis"] == "Selesai") {
    $pesan_status = "Servis barang telah selesai dan sudah dapat diambil. Silakan datang ke Avero Komputer untuk melakukan pengambilan barang. Jangan lupa membawa tanda terima servis sebagai bukti pengambilan.";
} elseif ($row["status_servis"] == "Sudah Diambil") {
    $pesan_status = "Servis barang telah selesai dan barang sudah diambil oleh pelanggan. Terima kasih telah mempercayakan kebutuhan servis kepada Avero Komputer.";
} else {
    $pesan_status = "Mohon menunggu informasi dan update proses servis selanjutnya dari kami.";
}
$pesan_whatsapp =
    "Assalamu'alaikum Wr. Wb. " .
    "\xF0\x9F\x98\x8A\n\n" .
    "Kami dari Avero Komputer ingin menginformasikan:\n\n" .
    "Nama       : " . $row["nama"] . "\n" .
    "No. Servis : " . $row["nomor_servis"] . "\n" .
    "Barang     : " . $row["nama_barang"] . " " . $row["merk"] . " " . $row["model"] . "\n" .
    "Status     : " . $row["status_servis"] . "\n\n" .
    $pesan_status . "\n\n" .
    "Terima kasih atas kesabaran dan kepercayaannya kepada Avero Komputer. " .
    "\xF0\x9F\x99\x8F";
    ?>
                            <a
                                href="https://web.whatsapp.com/send?phone=+62<?php echo ltrim(preg_replace('/[^0-9]/', '', $row["no_telp"]), '0'); ?>&text=<?php echo urlencode($pesan_whatsapp); ?>"
                                target="_blank"
                                style="
                                    display:inline-block;
                                    padding:5px 7px;
                                    background:#25D366;
                                    color:white;
                                    text-decoration:none;
                                    border-radius:6px;
                                    white-space:nowrap;
                                "> 💬 WhatsApp
                            </a>

                            <a
                                href="tanda_terima_servis.php?nomor=<?php echo urlencode($row["nomor_servis"]); ?>"
                                style="
                                    display:inline-block;
                                    padding:5px 7px;
                                    background:#0ea5e9;     
                                    color:white;
                                    text-decoration:none;
                                    border-radius:6px;
                                    white-space:nowrap;
                                "> 👁️ Lihat
                            </a>

                            <a
                                href="ubah_status.php?nomor=<?php echo urlencode($row["nomor_servis"]); ?>"
                                style="
                                    display:inline-block;
                                    padding:5px 7px;
                                    background:#16a34a;
                                    color:white;
                                    text-decoration:none;
                                    border-radius:6px;
                                    white-space:nowrap;
                                "> 🔧 Status
                            </a>
                        </td>
                    </tr>

                <?php } ?>

            </table>
        </div>
    </div>
</body>

</html>