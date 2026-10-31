<?php

session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

require_once "koneksi.php";

$sql = "SELECT *
        FROM pengaturan_toko
        ORDER BY id_pengaturan DESC
        LIMIT 1";

$hasil = mysqli_query($koneksi, $sql);

$data_toko = mysqli_fetch_assoc($hasil);

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nama_toko = $_POST["nama_toko"] ?? "";
    $alamat = $_POST["alamat"] ?? "";
    $no_telp = $_POST["no_telp"] ?? "";
    $syarat_ketentuan = $_POST["syarat_ketentuan"] ?? "";
    $footer = $_POST["footer"] ?? "";
    $nama_logo = $data_toko["logo"];

if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] == 0) {

    $nama_asli = $_FILES["logo"]["name"];
    $tmp_logo = $_FILES["logo"]["tmp_name"];

    $ekstensi = strtolower(
        pathinfo($nama_asli, PATHINFO_EXTENSION)
    );

    $ekstensi_valid = ["jpg", "jpeg", "png", "webp"];

    if (in_array($ekstensi, $ekstensi_valid)) {

        $nama_file = "logo_" . time() . "." . $ekstensi;

        $tujuan = "uploads/logo/" . $nama_file;

        if (move_uploaded_file($tmp_logo, $tujuan)) {

            $nama_logo = $nama_file;

        }

    }

}

    $sql_update = "UPDATE pengaturan_toko
               SET nama_toko = ?,
                   alamat = ?,
                   no_telp = ?,
                   syarat_ketentuan = ?,
                   footer = ?,
                   logo = ?
               WHERE id_pengaturan = ?";

    $stmt_update = mysqli_prepare($koneksi, $sql_update);

    mysqli_stmt_bind_param(
    $stmt_update,
    "ssssssi",
    $nama_toko,
    $alamat,
    $no_telp,
    $syarat_ketentuan,
    $footer,
    $nama_logo,
    $data_toko["id_pengaturan"]
);

    if (mysqli_stmt_execute($stmt_update)) {

        $pesan = "Pengaturan berhasil disimpan.";

        $data_toko["nama_toko"] = $nama_toko;
        $data_toko["alamat"] = $alamat;
        $data_toko["no_telp"] = $no_telp;
        $data_toko["syarat_ketentuan"] = $syarat_ketentuan;
        $data_toko["logo"] = $nama_logo;

    } else {

        $pesan = "Gagal menyimpan pengaturan.";

    }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pengaturan - Avero Komputer</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
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

    <h1>⚙️ Pengaturan</h1>

    <div class="card">

    <h2>🏪 Data Toko</h2>

    <form method="POST" enctype="multipart/form-data">

        <label>Nama Toko</label>

        <br><br>

        <input
            type="text"
            name="nama_toko"
            value="<?php echo htmlspecialchars($data_toko["nama_toko"]); ?>"
            style="width:100%; padding:10px; box-sizing:border-box;"
        >

        <br><br>

        <label>Alamat</label>

        <br><br>

        <textarea
    name="alamat"
    rows="3"
    style="width:100%; padding:10px; box-sizing:border-box;"
><?php echo htmlspecialchars($data_toko["alamat"]); ?></textarea>

        <br><br>

<label>Syarat & Ketentuan</label>

<br><br>

<textarea
    name="syarat_ketentuan"
    rows="8"
    style="width:100%; padding:10px; box-sizing:border-box;"
><?php echo htmlspecialchars($data_toko["syarat_ketentuan"]); ?>

</textarea>

<br><br>

<label>Footer Tanda Terima</label>

<br><br>

<textarea
    name="footer"
    rows="3"
    style="width:100%; padding:10px; box-sizing:border-box;"
><?php echo htmlspecialchars($data_toko["footer"] ?? ""); ?></textarea>

<br><br>

        <label>No. WhatsApp / Telepon</label>
        <br><br>

        <input
            type="text"
            name="no_telp"
            value="<?php echo htmlspecialchars($data_toko["no_telp"]); ?>"
            style="width:100%; padding:10px; box-sizing:border-box;"
        >
        <br><br>

<label>Logo Toko</label>

<br><br>

<input
    type="file"
    name="logo"
    accept="image/png,image/jpeg,image/webp"
>
<?php if (!empty($data_toko["logo"])) { ?>

    <br><br>

    <p>Logo saat ini:</p>

    <img
        src="uploads/logo/<?php echo htmlspecialchars($data_toko["logo"]); ?>"
        alt="Logo Avero Komputer"
        style="
            max-width:180px;
            max-height:100px;
            object-fit:contain;
            border:1px solid #e2e8f0;
            padding:10px;
            border-radius:8px;
        "
    >

<?php } ?>
        <br><br>

        <button
            type="submit"
            style="
                padding:10px 18px;
                background:#0ea5e9;
                color:white;
                border:none;
                border-radius:8px;
                cursor:pointer;
            "
        >
            💾 Simpan Pengaturan
        </button>

    </form>

</div>

</body>

</html>