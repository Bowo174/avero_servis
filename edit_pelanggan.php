<?php

require_once "koneksi.php";

$id_pelanggan = $_GET["id"] ?? "";

if ($id_pelanggan == "") {
    die("ID pelanggan tidak ditemukan.");
}

$sql = "SELECT
            id_pelanggan,
            nama,
            alamat,
            no_telp
        FROM pelanggan
        WHERE id_pelanggan = ?";

$stmt = mysqli_prepare($koneksi, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_pelanggan
);

mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($hasil);

if (!$data) {
    die("Data pelanggan tidak ditemukan.");
}


/* SIMPAN PERUBAHAN */

if (isset($_POST["simpan"])) {

    $nama = $_POST["nama"] ?? "";
    $no_telp = $_POST["no_telp"] ?? "";
    $alamat = $_POST["alamat"] ?? "";

    $sql_update = "UPDATE pelanggan
                   SET nama = ?,
                       no_telp = ?,
                       alamat = ?
                   WHERE id_pelanggan = ?";

    $stmt_update = mysqli_prepare($koneksi, $sql_update);

    mysqli_stmt_bind_param(
        $stmt_update,
        "sssi",
        $nama,
        $no_telp,
        $alamat,
        $id_pelanggan
    );

    if (mysqli_stmt_execute($stmt_update)) {

        header("Location: pelanggan.php");
        exit;

    } else {

        die("Gagal menyimpan perubahan: " . mysqli_error($koneksi));

    }

}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Pelanggan - Avero Komputer</title>

</head>

<body>

    <div style="
    max-width:600px;
    margin:40px auto;
    padding:20px;
    font-family:Arial,sans-serif;
">

        <h2>Edit Pelanggan</h2>

        <form method="post">

            <div style="margin-bottom:15px;">

                <label>Nama</label>

                <input type="text" name="nama" value="<?php echo htmlspecialchars($data["nama"]); ?>" style="
                    width:100%;
                    padding:10px;
                    margin-top:5px;
                    box-sizing:border-box;
                ">

            </div>

            <div style="margin-bottom:15px;">

                <label>No. WhatsApp</label>

                <input type="text" name="no_telp" value="<?php echo htmlspecialchars($data["no_telp"]); ?>" style="
                    width:100%;
                    padding:10px;
                    margin-top:5px;
                    box-sizing:border-box;
                ">

            </div>

            <div style="margin-bottom:15px;">

                <label>Alamat</label>

                <textarea name="alamat" rows="4" style="
                    width:100%;
                    padding:10px;
                    margin-top:5px;
                    box-sizing:border-box;
                "><?php echo htmlspecialchars($data["alamat"]); ?></textarea>

            </div>

            <button type="submit" name="simpan" style="
                padding:10px 16px;
                background:#16a34a;
                color:white;
                border:none;
                border-radius:6px;
                cursor:pointer;
            ">
                💾 Simpan Perubahan
            </button>

            <a href="pelanggan.php" style="
                display:inline-block;
                margin-left:8px;
                padding:10px 16px;
                background:#64748b;
                color:white;
                text-decoration:none;
                border-radius:6px;
            ">
                Kembali
            </a>

        </form>

    </div>

</body>

</html>