<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once 'app_helper.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifyCsrfToken()) {
    http_response_code(403);
    exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
}
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

<link rel="stylesheet" href="style/edit_pelanggan.css?v=2">
<link rel="stylesheet" href="style/responsive.css?v=3">
</head>

<body><?php include 'navigasi.php'; ?>

    <div class="inline-cdda49f7">

        <h2>Edit Pelanggan</h2>

        <form method="post"><?php csrfField(); ?>

            <div class="inline-d7858cfa">

                <label>Nama</label>

                <input type="text" name="nama" value="<?php echo htmlspecialchars($data["nama"]); ?>" class="inline-33720129">

            </div>

            <div class="inline-d7858cfa">

                <label>No. WhatsApp</label>

                <input type="text" name="no_telp" value="<?php echo htmlspecialchars($data["no_telp"]); ?>" class="inline-33720129">

            </div>

            <div class="inline-d7858cfa">

                <label>Alamat</label>

                <textarea name="alamat" rows="4" class="inline-33720129"><?php echo htmlspecialchars($data["alamat"]); ?></textarea>

            </div>

            <button type="submit" name="simpan" class="inline-b89c18a3">
                ðŸ’¾ Simpan Perubahan
            </button>

            <a href="pelanggan.php" class="inline-22443647">
                Kembali
            </a>

        </form>

    </div>

</body>

</html>
