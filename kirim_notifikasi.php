<?php

require_once "koneksi.php";

/*
    MESIN ANTREAN NOTIFIKASI

    Untuk sementara hanya mengambil
    notifikasi yang statusnya "menunggu".
*/

$sql = "SELECT
            id_notifikasi,
            id_servis,
            no_tujuan,
            jenis,
            pesan,
            status_kirim
        FROM notifikasi
        WHERE status_kirim = 'menunggu'
        ORDER BY id_notifikasi ASC
        LIMIT 10";

$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die("Gagal mengambil antrean notifikasi: " . mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Antrean Notifikasi Avero</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            background: #fef3c7;
            color: #92400e;
            font-weight: bold;
        }

        .pesan {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            white-space: pre-line;
            margin-top: 10px;
        }
    </style>

</head>

<body>

<div class="container">

    <h2>Antrean Notifikasi Avero</h2>

    <?php if (mysqli_num_rows($hasil) == 0) { ?>

        <div class="card">
            Tidak ada notifikasi yang menunggu dikirim.
        </div>

    <?php } else { ?>

        <?php while ($row = mysqli_fetch_assoc($hasil)) { ?>

            <div class="card">

                <p>
                    <strong>ID Notifikasi:</strong>
                    <?php echo htmlspecialchars($row["id_notifikasi"]); ?>
                </p>

                <p>
                    <strong>ID Servis:</strong>
                    <?php echo htmlspecialchars($row["id_servis"]); ?>
                </p>

                <p>
                    <strong>Nomor Tujuan:</strong>
                    <?php echo htmlspecialchars($row["no_tujuan"]); ?>
                </p>

                <p>
                    <strong>Jenis:</strong>
                    <?php echo htmlspecialchars($row["jenis"]); ?>
                </p>

                <p>
                    <strong>Status:</strong>
                    <span class="status">
                        <?php echo htmlspecialchars($row["status_kirim"]); ?>
                    </span>
                </p>

                <strong>Pesan:</strong>

                <div class="pesan">
                    <?php echo htmlspecialchars($row["pesan"]); ?>
                </div>

            </div>

        <?php } ?>

    <?php } ?>

</div>

</body>

</html>