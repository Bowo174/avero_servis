<?php

session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

require_once "koneksi.php";
$sql = "SELECT
            id_pelanggan,
            nama,
            alamat,
            no_telp,
            created_at
        FROM pelanggan
        ORDER BY id_pelanggan DESC";

$hasil = mysqli_query($koneksi, $sql);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Pelanggan - Avero Komputer</title>

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

        <h1>👥 Data Pelanggan</h1>

        <div class="card" style="overflow-x:auto;">

            <table style="width:100%; border-collapse:collapse; min-width:700px;">

                <tr style="background:#f8fafc;">

                    <th style="padding:12px; text-align:left;">
                        Nama
                    </th>

                    <th style="padding:12px; text-align:left;">
                        No. WhatsApp
                    </th>

                    <th style="padding:12px; text-align:left;">
                        Alamat
                    </th>

                    <th style="padding:12px; text-align:left;">
                        Terdaftar
                    </th>

                    <th style="padding:12px; text-align:left;">
                        Aksi
                    </th>

                </tr>

                <?php while ($row = mysqli_fetch_assoc($hasil)) { ?>

                    <tr style="border-top:1px solid #e2e8f0;">

                        <td style="padding:12px;">
                            <?php echo htmlspecialchars($row["nama"]); ?>
                        </td>

                        <td style="padding:12px;">
                            <?php echo htmlspecialchars($row["no_telp"]); ?>
                        </td>

                        <td style="padding:12px;">
                            <?php echo htmlspecialchars($row["alamat"]); ?>
                        </td>

                        <td style="padding:12px;">
                            <?php
                            echo date(
                                "d-m-Y H:i",
                                strtotime($row["created_at"])
                            );
                            ?>
                        </td>

                        
                        <td style="padding:12px;">

                            <a href="edit_pelanggan.php?id=<?php echo urlencode($row["id_pelanggan"]); ?>" style="
            display:inline-block;
            padding:8px 12px;
            background:#f59e0b;
            color:white;
            text-decoration:none;
            border-radius:6px;
        ">
                                ✏️ Edit
                            </a>

                        </td>

                    </tr>

                <?php } ?>

            </table>

        </div>

</body>

</html>