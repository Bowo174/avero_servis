<?php
require_once "koneksi.php";

$nomor_servis = $_GET["nomor"] ?? "";

$sql = "SELECT 
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

    <style>
    @media print {

    body {
        margin: 0;
        padding: 0;
        background: white;
    }

    .box {
        page-break-inside: avoid;
    }

}
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            padding: 20px;
        }

        .nota {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 15px;
        }

        .header h1 {
            margin: 0;
        }

        .judul {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin: 25px 0;
        }

        .box {
            margin-bottom: 8px;
        }

        .box h3 {
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .row {
            margin: 4px 0;
        }

        .label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
        }
        @media print {

    @page {
        size: A4;
        margin: 0;
    }

    body {
        margin: 0;
        padding: 0;
        background: white;
    }

    .print-area {
    width: 210mm;
    margin: 0;
    padding: 0;
    position: relative;
}

.print-area::after {
    content: "";
    position: absolute;
    top: 148.5mm;
    left: 0;
    width: 100%;
    border-top: 1px dashed #777;
}

    .nota {
    width: 210mm;
    height: 148.5mm;
    max-width: none;
    margin: 0;
    padding: 3mm 6mm;
    box-sizing: border-box;
    border-radius: 0;
    page-break-inside: avoid;
    break-inside: avoid;
    position: relative;
    overflow: hidden;
    font-size: 10px;
}
        }

    </style>
</head>

<body>

<div class="print-area">

<div class="nota">

<div class="header">

    <!-- BLOK HEADER TETAP CENTER -->
    <div style="
        position:relative;
        width:100%;
        text-align:center;
        line-height:1;
    ">

        <!-- LOGO DI SAMPING NAMA, TIDAK MENGGESER CENTER -->
        <?php if (!empty($data_toko["logo"])) { ?>

            <img
                src="uploads/logo/<?php echo htmlspecialchars($data_toko["logo"]); ?>"
                alt="Logo Avero Komputer"
                style="
                    position:absolute;
                    width:55px;
                    height:55px;
                    object-fit:contain;
                    right:calc(50% + 160px);
                    top:0px;
                "
            >

        <?php } ?>

        <!-- NAMA TOKO -->
        <h1 style="
            margin:0 0 7px 0;
            font-size:28px;
            font-weight:800;
            letter-spacing:1px;
            line-height:1;
        ">
            AVERO KOMPUTER
        </h1>

        <!-- KETERANGAN TOKO -->
        <p style="
            margin:3px 0;
            font-size:14px;
            font-weight:normal;
            line-height:1;
        ">
            Komputer - Printer - Service - Network - CCTV
        </p>

        <!-- ALAMAT -->
        <p style="
            margin:3px 0;
            font-size:13px;
            font-weight:normal;
            line-height:1;
        ">
            <?php echo htmlspecialchars($data_toko["alamat"] ?? ""); ?>
        </p>

        <!-- NOMOR HP -->
        <p style="
            margin:3px 0 7px 0;
            font-size:13px;
            font-weight:normal;
            line-height:1;
        ">
            📞 <?php echo htmlspecialchars($data_toko["no_telp"] ?? ""); ?>
        </p>

    </div>

</div>

<div class="box">
      <div class="box" style="position:relative;">

    <!-- QR CODE DI KANAN, SEJAJAR NOMOR SERVIS -->
    <div style="
        position:absolute;
        top:-5px;
        right:0;
        width:105px;
        text-align:center;
    ">

        <p style="
            font-weight:bold;
            font-size:9px;
            margin:0 0 2px 0;
            line-height:1.1;
        ">
            SCAN CEK STATUS
        </p>

        <?php
        $url_status = "https://averokomputer.my.id/status_servis.php?token=" . $data["qr_token"];
        ?>

        <img
            src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo urlencode($url_status); ?>"
            alt="QR Code Status Servis"
            width="100"
            height="100"
        >

    </div>

    <div class="row" style="
        font-size:18px;
        font-weight:bold;
        margin-bottom:8px;
    ">
        <span class="label" style="display:inline-block; width:150px;">Nomor Servis</span> :
<?php echo htmlspecialchars($data["nomor_servis"]); ?>
    </div>

        <div class="row">
            <span class="label">Tanggal Masuk</span> :
            <?php echo date("d-m-Y H:i", strtotime($data["tanggal_masuk"])); ?>
        </div>

        <div class="row" style="
    margin-top:8px;
">
   <span class="label">Status</span> :

    <span style="
        display:inline-block;
        margin-left:5px;
        padding:5px 12px;
        border-radius:20px;
        background:#0ea5e9;
        color:white;
        font-weight:bold;
        font-size:13px;
    ">
        <?php echo htmlspecialchars($data["status_servis"]); ?>
    </span>
</div>

    </div>

    <div class="box">

        <h3>Data Pelanggan</h3>

       <div class="row" style="
    font-weight:bold;
">
    <span class="label">Nama</span> :
    <?php echo htmlspecialchars($data["nama"]); ?>
</div>

        <div class="row">
            <span class="label">WhatsApp</span> :
            <?php echo htmlspecialchars($data["no_telp"]); ?>
        </div>

        <div class="row">
    <span class="label">Alamat</span> :
    <span style="
        display:inline-block;
        max-width:80%;
        vertical-align:top;
        line-height:1.4;
    ">
        <?php echo nl2br(htmlspecialchars($data["alamat"])); ?>
    </span>
</div>

    </div>

    <div class="box">

        <h3>Data Barang</h3>

        <div class="row" style="
    font-weight:bold;
">
    <span class="label">Barang</span> :
    <?php echo htmlspecialchars($data["nama_barang"]); ?>
</div>

        <div class="row">
            <span class="label">Merk</span> :
            <?php echo htmlspecialchars($data["merk"]); ?>
        </div>

        <div class="row">
            <span class="label">Model/Tipe</span> :
            <?php echo htmlspecialchars($data["model"]); ?>
        </div>

        <div class="row">
            <span class="label">Kelengkapan</span> :
            <?php echo htmlspecialchars($data["kelengkapan"]); ?>
        </div>
        <div class="row">
    <span class="label">Keluhan</span> :

    <span style="
        display:inline-block;
        max-width:80%;
        vertical-align:top;
        line-height:1.4;
    ">
        <?php echo nl2br(htmlspecialchars($data["keluhan"] ?? "")); ?>
    </span>
</div>

    </div>

    <div class="box">
    
        <div style="
    margin-top:3px;
    padding:8px 8px;
    border:1px solid #e2e8f0;
    border-radius:8px;
    background:#f8fafc;
">

    <h3 style="
    margin-top:0;
    margin-bottom:0px;
">
    Syarat & Ketentuan
</h3>

<div style="
    white-space:pre-line;
    font-size:12px;
    line-height:1;
">
        <?php echo htmlspecialchars($data_toko["syarat_ketentuan"] ?? ""); ?>
    </div>

</div>

<div style="
    margin-top:5px;
    padding-bottom:2px;
    text-align:center;
    font-size:11px;
    color:#64748b;
">
    <?php echo htmlspecialchars($data_toko["footer"] ?? ""); ?>
</div>
<script>

window.addEventListener("beforeprint", function () {

    const area = document.querySelector(".print-area");
    const nota = document.querySelector(".nota");

    if (!area || !nota) {
        return;
    }

    // Cegah nota kedua dibuat lebih dari satu kali
    if (document.querySelector(".nota-kedua")) {
        return;
    }

    // Gandakan nota pertama
    const notaKedua = nota.cloneNode(true);

    // Beri tanda agar mudah dikenali
    notaKedua.classList.add("nota-kedua");

    // Masukkan setelah nota pertama
    area.appendChild(notaKedua);

});

window.addEventListener("afterprint", function () {

    const notaKedua = document.querySelector(".nota-kedua");

    if (notaKedua) {
        notaKedua.remove();
    }

});

</script>
</body>
</html>