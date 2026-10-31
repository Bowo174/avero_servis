<?php

session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

require_once "koneksi.php";

$nomor_servis = $_GET["nomor"] ?? "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomor_servis = $_POST["nomor_servis"] ?? $nomor_servis;
}

$data = null;
$pesan = "";
$detail_form = [];
$status_form = $_POST["status_baru"] ?? "";
$validasi_rincian = true;

if ($_SERVER["REQUEST_METHOD"] === "POST" && $status_form === "Selesai") {
    $keterangan_post = $_POST["keterangan"] ?? [];
    $jumlah_post = $_POST["jumlah"] ?? [];
    $harga_post = $_POST["harga_satuan"] ?? [];
    $jumlah_baris = max(count($keterangan_post), count($jumlah_post), count($harga_post));

    for ($i = 0; $i < $jumlah_baris; $i++) {
        $keterangan = trim($keterangan_post[$i] ?? "");
        $jumlah = trim((string)($jumlah_post[$i] ?? ""));
        $harga_satuan = trim((string)($harga_post[$i] ?? ""));

        if ($keterangan === "" && $jumlah === "" && $harga_satuan === "") {
            continue;
        }

        $detail_form[] = [
            "keterangan" => $keterangan,
            "jumlah" => $jumlah,
            "harga_satuan" => $harga_satuan
        ];

        if (
            $keterangan === "" ||
            !preg_match("/^[1-9][0-9]*$/", $jumlah) ||
            !preg_match("/^[0-9]+$/", $harga_satuan)
        ) {
            $validasi_rincian = false;
            $pesan = "Lengkapi keterangan, jumlah (minimal 1), dan harga satuan untuk setiap baris rincian.";
            break;
        }
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_servis = $_POST["id_servis"] ?? "";
    $status_baru = $_POST["status_baru"] ?? "";

    $status_valid = [
        "Diterima",
        "Pemeriksaan",
        "Menunggu Persetujuan",
        "Menunggu Sparepart",
        "Sparepart Telah Tiba",
        "Dalam Pengerjaan",
        "Selesai",
        "Sudah Diambil"
    ];

    if (
        $id_servis != "" &&
        in_array($status_baru, $status_valid) &&
        $validasi_rincian
    ) {

        $sql_update = "UPDATE servis
                       SET status_servis = ?
                       WHERE id_servis = ?";

        $stmt_update = mysqli_prepare($koneksi, $sql_update);

        mysqli_stmt_bind_param(
            $stmt_update,
            "si",
            $status_baru,
            $id_servis
        );

        if (mysqli_stmt_execute($stmt_update)) {
            if ($status_baru === "Selesai") {
                $stmt_hapus_detail = mysqli_prepare($koneksi, "DELETE FROM detail_servis WHERE id_servis = ?");
                mysqli_stmt_bind_param($stmt_hapus_detail, "i", $id_servis);
                mysqli_stmt_execute($stmt_hapus_detail);

                $stmt_simpan_detail = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO detail_servis (id_servis, keterangan, jumlah, harga_satuan) VALUES (?, ?, ?, ?)"
                );

                foreach ($detail_form as $baris_detail) {
                    mysqli_stmt_bind_param(
                        $stmt_simpan_detail,
                        "isii",
                        $id_servis,
                        $baris_detail["keterangan"],
                        $baris_detail["jumlah"],
                        $baris_detail["harga_satuan"]
                    );
                    mysqli_stmt_execute($stmt_simpan_detail);
                }
            }
            // AMBIL NOMOR WHATSAPP PELANGGAN
            $sql_pelanggan = "SELECT
                    pelanggan.nama,
                    pelanggan.no_telp
                  FROM servis
                  JOIN pelanggan
                    ON servis.id_pelanggan = pelanggan.id_pelanggan
                  WHERE servis.id_servis = ?";

            $stmt_pelanggan = mysqli_prepare($koneksi, $sql_pelanggan);

            mysqli_stmt_bind_param(
                $stmt_pelanggan,
                "i",
                $id_servis
            );

            mysqli_stmt_execute($stmt_pelanggan);

            $hasil_pelanggan = mysqli_stmt_get_result($stmt_pelanggan);

            $data_pelanggan = mysqli_fetch_assoc($hasil_pelanggan);

            $nama_pelanggan = $data_pelanggan["nama"] ?? "";
            $no_telp_pelanggan = $data_pelanggan["no_telp"] ?? "";

            $catatan = "Status servis diubah menjadi " . $status_baru;

            // SIAPKAN PESAN WHATSAPP

            // BUAT PESAN WHATSAPP SESUAI STATUS
            switch ($status_baru) {

                case "Diterima":

                    $pesan_whatsapp =
                        "Assalamu'alaikum.. Sahabat Avero Komputer,\n\n" .
                        "Servis Anda dengan nomor " . $nomor_servis .
                        " telah kami terima.\n\n" .
                        "Barang Anda akan segera kami periksa.";

                    break;


                case "Pemeriksaan":

                    $pesan_whatsapp =
                        "Assalamu'alaikum.. Sahabat Avero Komputer,\n\n" .
                        "Servis Anda dengan nomor " . $nomor_servis .
                        " sekarang dalam tahap Pemeriksaan.\n\n" .
                        "Anda dapat memantau perkembangan servis melalui link status servis Avero Komputer.";

                    break;

                case "Menunggu Persetujuan":

                    $pesan_whatsapp =
                        "Assalamu'alaikum.. Sahabat Avero Komputer,\n\n" .
                        "Servis Anda dengan nomor " . $nomor_servis .
                        " saat ini menunggu persetujuan sebelum proses servis dapat dilanjutkan.\n\n" .
                        "Silakan hubungi Avero Komputer untuk informasi lebih lanjut.";

                    break;


                case "Menunggu Sparepart":

                    $pesan_whatsapp =
                        "Assalamu'alaikum.. Sahabat Avero Komputer,\n\n" .
                        "Servis Anda dengan nomor " . $nomor_servis .
                        " saat ini dalam tahap Menunggu Sparepart.\n\n" .
                        "Kami akan menginformasikan kembali apabila proses servis dapat dilanjutkan.";

                    break;
                    
                case "Sparepart Telah Tiba":
                    $pesan_whatsapp =
                        "Assalamu'alaikum.. Sahabat Avero Komputer,\n\n" .
                        "Servis Anda dengan nomor " . $nomor_servis .
                        " saat ini dalam tahap Sparepart Telah Tiba.\n\n" .
                        "Sparepart yang diperlukan untuk proses servis telah tiba. Dimohon untuk segera membawa perangkat atau device anda ke toko, supaya proses pengerjaan segera kami lanjutkan.";

                    break;

                case "Dalam Pengerjaan":

                    $pesan_whatsapp =
                        "Assalamu'alaikum.. Sahabat Avero Komputer,\n\n" .
                        "Servis Anda dengan nomor " . $nomor_servis .
                        " sekarang dalam tahap Dalam Pengerjaan.\n\n" .
                        "Anda dapat memantau perkembangan servis melalui link status servis Avero Komputer.";

                    break;


                case "Selesai":

                    $pesan_whatsapp =
                        "Assalamu'alaikum.. Sahabat Avero Komputer,\n\n" .
                        "Servis Anda dengan nomor " . $nomor_servis .
                        " telah selesai dan sudah dapat diambil.\n\n" .
                        "Terima kasih telah mempercayakan servis kepada Avero Komputer.";

                    break;


                case "Sudah Diambil":

                    $pesan_whatsapp =
                        "Assalamu'alaikum.. Sahabat Avero Komputer,\n\n" .
                        "Servis Anda dengan nomor " . $nomor_servis .
                        " telah diambil.\n\n" .
                        "Terima kasih telah mempercayakan servis kepada Avero Komputer.";

                    break;


                default:

                    $pesan_whatsapp =
                        "Assalamu'alaikum.. Sahabat Avero Komputer,\n\n" .
                        "Status servis Anda dengan nomor " . $nomor_servis .
                        " telah diperbarui menjadi " . $status_baru . ".";

                    break;
            }

            // SIMPAN NOTIFIKASI KE ANTREAN
            $sql_notifikasi = "INSERT INTO notifikasi
                   (id_servis, no_tujuan, jenis, pesan, status_kirim)
                   VALUES (?, ?, ?, ?, ?)";

            $stmt_notifikasi = mysqli_prepare($koneksi, $sql_notifikasi);

            $jenis_notifikasi = "whatsapp";
            $status_kirim = "menunggu";

            mysqli_stmt_bind_param(
                $stmt_notifikasi,
                "issss",
                $id_servis,
                $no_telp_pelanggan,
                $jenis_notifikasi,
                $pesan_whatsapp,
                $status_kirim
            );

            mysqli_stmt_execute($stmt_notifikasi);

            $sql_riwayat = "INSERT INTO riwayat_status
                            (id_servis, status, catatan, id_user)
                            VALUES (?, ?, ?, ?)";

            $stmt_riwayat = mysqli_prepare($koneksi, $sql_riwayat);

            if (!$stmt_riwayat) {
                die("Query riwayat gagal: " . mysqli_error($koneksi));
            }

            mysqli_stmt_bind_param(
                $stmt_riwayat,
                "issi",
                $id_servis,
                $status_baru,
                $catatan,
                $_SESSION["id_user"]
            );

            mysqli_stmt_execute($stmt_riwayat);

            // $pesan = "Status servis berhasil diubah menjadi " . $status_baru;
        }
    }
}
if ($nomor_servis != "") {

    $sql = "SELECT
                id_servis,
                nomor_servis,
                status_servis
            FROM servis
            WHERE nomor_servis = ?";

    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "s", $nomor_servis);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($hasil);

    if ($data && $validasi_rincian) {
        $detail_form = [];
        $stmt_detail_form = mysqli_prepare(
            $koneksi,
            "SELECT keterangan, jumlah, harga_satuan FROM detail_servis WHERE id_servis = ? ORDER BY id_detail_servis ASC"
        );
        mysqli_stmt_bind_param($stmt_detail_form, "i", $data["id_servis"]);
        mysqli_stmt_execute($stmt_detail_form);
        $hasil_detail_form = mysqli_stmt_get_result($stmt_detail_form);
        while ($baris_detail = mysqli_fetch_assoc($hasil_detail_form)) {
            $detail_form[] = $baris_detail;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ubah Status Servis</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 15px;
        }

        select,
        button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        button {
            cursor: pointer;
            font-weight: bold;
        }

        .message {
            color: #b91c1c;
            font-weight: bold;
        }

        .rincian-input {
            margin-top: 18px;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1.5fr auto;
            gap: 8px;
            margin: 8px 0;
        }

        .detail-row input {
            min-width: 0;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
        }

        .remove-detail {
            width: auto;
            padding: 8px 10px;
            margin-top: 0;
            background: #fee2e2;
            color: #991b1b;
        }

        #tambah-detail {
            margin: 4px 0 10px;
            background: #e0f2fe;
            color: #075985;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .actions button,
        .actions .button-link {
            flex: 1;
            width: auto;
            text-align: center;
            text-decoration: none;
        }

        .button-link {
            box-sizing: border-box;
            padding: 12px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: #0ea5e9;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        @media (max-width: 600px) {
            .detail-row {
                grid-template-columns: 1fr 1fr;
            }

            .detail-row input:first-child {
                grid-column: 1 / -1;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="card">

            <h2>Ubah Status Servis</h2>

            <?php if ($data) { ?>

                <p>
                    <strong>Nomor Servis:</strong>
                    <?php echo htmlspecialchars($data["nomor_servis"]); ?>
                </p>

                <p>
                    <strong>Status Saat Ini:</strong>
                    <?php echo htmlspecialchars($data["status_servis"]); ?>
                </p>
                <?php if ($pesan !== "") { ?><p class="message"><?php echo htmlspecialchars($pesan); ?></p><?php } ?>

                <label><strong>Status Baru</strong></label>
                <form method="POST">
                    <input
                        type="hidden"
                        name="nomor_servis"
                        value="<?php echo htmlspecialchars($data['nomor_servis']); ?>">
                    <input
                        type="hidden"
                        name="id_servis"
                        value="<?php echo htmlspecialchars($data['id_servis']); ?>">
                    <select name="status_baru" id="status_baru">
                        <option value="Diterima" <?php echo ($status_form !== "" ? $status_form : $data["status_servis"]) === "Diterima" ? "selected" : ""; ?>>Diterima</option>
                        <option value="Pemeriksaan" <?php echo ($status_form !== "" ? $status_form : $data["status_servis"]) === "Pemeriksaan" ? "selected" : ""; ?>>Pemeriksaan</option>
                        <option value="Menunggu Persetujuan" <?php echo ($status_form !== "" ? $status_form : $data["status_servis"]) === "Menunggu Persetujuan" ? "selected" : ""; ?>>Menunggu Persetujuan</option>
                        <option value="Menunggu Sparepart" <?php echo ($status_form !== "" ? $status_form : $data["status_servis"]) === "Menunggu Sparepart" ? "selected" : ""; ?>>Menunggu Sparepart</option>
                        <option value="Sparepart Telah Tiba" <?php echo ($status_form !== "" ? $status_form : $data["status_servis"]) === "Sparepart Telah Tiba" ? "selected" : ""; ?>>Sparepart Telah Tiba</option>
                        <option value="Dalam Pengerjaan" <?php echo ($status_form !== "" ? $status_form : $data["status_servis"]) === "Dalam Pengerjaan" ? "selected" : ""; ?>>Dalam Pengerjaan</option>
                        <option value="Selesai" <?php echo ($status_form !== "" ? $status_form : $data["status_servis"]) === "Selesai" ? "selected" : ""; ?>>Selesai</option>
                        <option value="Sudah Diambil" <?php echo ($status_form !== "" ? $status_form : $data["status_servis"]) === "Sudah Diambil" ? "selected" : ""; ?>>Sudah Diambil</option>
                    </select>
                    <div id="rincian-input" class="rincian-input" style="display:none;">
                        <h3>Rincian Pengerjaan</h3>
                        <div id="detail-list">
                            <?php foreach ($detail_form as $baris_detail) { ?>
                                <div class="detail-row">
                                    <input type="text" name="keterangan[]" placeholder="Keterangan" value="<?php echo htmlspecialchars($baris_detail["keterangan"]); ?>">
                                    <input type="number" name="jumlah[]" placeholder="Jumlah" min="1" step="1" value="<?php echo htmlspecialchars((string)$baris_detail["jumlah"]); ?>">
                                    <input type="number" name="harga_satuan[]" placeholder="Harga satuan" min="0" step="1" value="<?php echo htmlspecialchars((string)$baris_detail["harga_satuan"]); ?>">
                                    <button type="button" class="remove-detail" aria-label="Hapus rincian">Hapus</button>
                                </div>
                            <?php } ?>
                            <?php if (count($detail_form) === 0) { ?>
                                <div class="detail-row">
                                    <input type="text" name="keterangan[]" placeholder="Keterangan">
                                    <input type="number" name="jumlah[]" placeholder="Jumlah" min="1" step="1">
                                    <input type="number" name="harga_satuan[]" placeholder="Harga satuan" min="0" step="1">
                                    <button type="button" class="remove-detail" aria-label="Hapus rincian">Hapus</button>
                                </div>
                            <?php } ?>
                        </div>
                        <button type="button" id="tambah-detail">+ Tambah rincian</button>
                    </div>
                    <div class="actions">
                        <button type="submit">Simpan Status</button>
                        <?php if ($data["status_servis"] === "Selesai") { ?>
                            <a class="button-link" href="nota_servis.php?nomor=<?php echo urlencode($data["nomor_servis"]); ?>" target="_blank" rel="noopener">Cetak Tanda Terima</a>
                        <?php } ?>
                    </div>
                </form>

            <?php } else { ?>

                <h3>Data servis tidak ditemukan</h3>

            <?php } ?>

        </div>

    </div>

    <script>
        const statusSelect = document.getElementById("status_baru");
        const rincianInput = document.getElementById("rincian-input");
        const detailList = document.getElementById("detail-list");

        function perbaruiRincian() {
            const aktif = statusSelect.value === "Selesai";
            rincianInput.style.display = aktif ? "block" : "none";
            rincianInput.querySelectorAll("input").forEach((input) => {
                input.required = aktif;
                input.disabled = !aktif;
            });
        }

        function tambahBarisDetail() {
            const row = document.createElement("div");
            row.className = "detail-row";
            row.innerHTML = '<input type="text" name="keterangan[]" placeholder="Keterangan"><input type="number" name="jumlah[]" placeholder="Jumlah" min="1" step="1"><input type="number" name="harga_satuan[]" placeholder="Harga satuan" min="0" step="1"><button type="button" class="remove-detail" aria-label="Hapus rincian">Hapus</button>';
            detailList.appendChild(row);
            perbaruiRincian();
        }

        document.getElementById("tambah-detail").addEventListener("click", tambahBarisDetail);
        detailList.addEventListener("click", (event) => {
            if (event.target.classList.contains("remove-detail")) {
                if (detailList.querySelectorAll(".detail-row").length > 1) {
                    event.target.closest(".detail-row").remove();
                }
            }
        });
        statusSelect.addEventListener("change", perbaruiRincian);
        perbaruiRincian();
    </script>
</body>

</html>