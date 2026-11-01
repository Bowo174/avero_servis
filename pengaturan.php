<?php

require_once __DIR__ . '/session_bootstrap.php';

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

require_once "koneksi.php";
require_once "db_backup.php";

if (empty($_SESSION['csrf_pengaturan']))
    $_SESSION['csrf_pengaturan'] = bin2hex(random_bytes(32));
$pesan = '';
$jenis_pesan = 'success';
$backup_terbaru = null;

if (isset($_GET['download'])) {
    $nama_backup = (string) $_GET['download'];
    if (!avero_valid_backup($nama_backup)) {
        http_response_code(404);
        exit('Backup tidak ditemukan atau sudah berubah.');
    }
    $file_backup = AVERO_BACKUP_DIR . DIRECTORY_SEPARATOR . $nama_backup;
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . basename($nama_backup) . '"');
    header('Content-Length: ' . filesize($file_backup));
    readfile($file_backup);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_database'])) {
    if (!hash_equals($_SESSION['csrf_pengaturan'], (string) ($_POST['csrf_token'] ?? ''))) {
        $pesan = 'Permintaan tidak valid. Muat ulang halaman lalu coba kembali.';
        $jenis_pesan = 'error';
    } else
        try {
            if ($_POST['aksi_database'] === 'backup') {
                $nama_backup = avero_make_backup($koneksi);
                $file_backup = AVERO_BACKUP_DIR . DIRECTORY_SEPARATOR . $nama_backup;
                header('Content-Type: application/sql; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . basename($nama_backup) . '"');
                header('Content-Length: ' . filesize($file_backup));
                readfile($file_backup);
                exit;
            } elseif ($_POST['aksi_database'] === 'restore') {
                $upload = $_FILES['backup_sql'] ?? null;
                if (!$upload || $upload['error'] !== UPLOAD_ERR_OK)
                    throw new RuntimeException('Pilih file SQL yang berhasil diunggah.');
                if ((int) $upload['size'] > 25 * 1024 * 1024)
                    throw new RuntimeException('Ukuran file SQL maksimal 25 MB.');
                if (strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION)) !== 'sql')
                    throw new RuntimeException('File restore harus berakhiran .sql.');
                $sql_upload = file_get_contents($upload['tmp_name']);
                if ($sql_upload === false)
                    throw new RuntimeException('File SQL tidak dapat dibaca.');
                avero_make_backup($koneksi, 'restore_safety');
                avero_restore_sql($koneksi, $sql_upload);
                $pesan = 'Database berhasil dipulihkan dari file SQL.';
            } elseif ($_POST['aksi_database'] === 'reset') {
                $registry = avero_backup_registry();
                $punya_backup_valid = false;
                foreach (array_keys($registry) as $nama)
                    if (avero_valid_backup($nama)) {
                        $punya_backup_valid = true;
                        break;
                    }
                if (!$punya_backup_valid)
                    throw new RuntimeException('Reset terkunci. Buat backup SQL yang valid terlebih dahulu.');
                if (($_POST['konfirmasi_reset'] ?? '') !== 'RESET')
                    throw new RuntimeException('Ketik RESET dengan benar untuk mengonfirmasi pengosongan data.');
                avero_make_backup($koneksi, 'reset_safety');
                $preserved = ['users', 'pengaturan_toko'];
                $operational = array_values(array_filter(avero_backup_tables($koneksi), fn($t) => !in_array($t, $preserved, true)));
                $koneksi->query('SET FOREIGN_KEY_CHECKS=0');
                $koneksi->begin_transaction();
                try {
                    foreach ($operational as $table) {
                        $safe = '`' . str_replace('`', '``', $table) . '`';
                        if (!$koneksi->query("DELETE FROM $safe"))
                            throw new RuntimeException($koneksi->error);
                    }
                    $koneksi->commit();
                } catch (Throwable $e) {
                    $koneksi->rollback();
                    throw $e;
                }
                $koneksi->query('SET FOREIGN_KEY_CHECKS=1');
                foreach ($operational as $table) {
                    $safe = '`' . str_replace('`', '``', $table) . '`';
                    $koneksi->query("ALTER TABLE $safe AUTO_INCREMENT=1");
                }
                $pesan = 'Reset selesai. Data pelanggan, barang, dan transaksi servis dikosongkan; akun admin dan pengaturan toko tetap tersedia.';
            }
        } catch (Throwable $e) {
            $pesan = 'Gagal: ' . $e->getMessage();
            $jenis_pesan = 'error';
        }
}

$sql = "SELECT *
        FROM pengaturan_toko
        ORDER BY id_pengaturan DESC
        LIMIT 1";

$hasil = mysqli_query($koneksi, $sql);

$data_toko = mysqli_fetch_assoc($hasil);

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['aksi_database'])) {

    if (!hash_equals($_SESSION['csrf_pengaturan'], (string) ($_POST['csrf_token'] ?? ''))) {
        $pesan = 'Permintaan tidak valid. Muat ulang halaman lalu coba kembali.';
        $jenis_pesan = 'error';
    } else {

        $nama_toko = $_POST["nama_toko"] ?? "";
        $alamat = $_POST["alamat"] ?? "";
        $no_telp = $_POST["no_telp"] ?? "";
        $syarat_ketentuan = $_POST["syarat_ketentuan"] ?? "";
        $footer = $_POST["footer"] ?? "";
        $nama_logo = $data_toko["logo"];

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $logo = $_FILES['logo'];
            if ($logo['error'] !== UPLOAD_ERR_OK || $logo['size'] > 5 * 1024 * 1024 || !is_uploaded_file($logo['tmp_name']) || @getimagesize($logo['tmp_name']) === false) {
                $pesan = 'Logo tidak valid atau ukurannya melebihi 5 MB.';
                $jenis_pesan = 'error';
            } else {
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($logo['tmp_name']);
                $ekstensi = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? '';
                if ($ekstensi === '') {
                    $pesan = 'Format logo harus JPG, PNG, atau WebP.';
                    $jenis_pesan = 'error';
                } else {
                    $nama_file = 'logo_' . bin2hex(random_bytes(12)) . '.' . $ekstensi;
                    $direktori = __DIR__ . '/uploads/logo';
                    if (!is_dir($direktori) && !mkdir($direktori, 0755, true) && !is_dir($direktori)) {
                        $pesan = 'Folder logo tidak dapat disiapkan.';
                        $jenis_pesan = 'error';
                    } elseif (!move_uploaded_file($logo['tmp_name'], $direktori . '/' . $nama_file)) {
                        $pesan = 'Logo gagal disimpan.';
                        $jenis_pesan = 'error';
                    } else {
                        $nama_logo = $nama_file;
                    }
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

        $stmt_update = $jenis_pesan === 'error' ? false : mysqli_prepare($koneksi, $sql_update);

        if ($stmt_update) {
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
    }
}

$backup_daftar = avero_backup_registry();
uksort($backup_daftar, 'strcmp');
$backup_valid_ada = false;
foreach (array_keys($backup_daftar) as $nama)
    if (avero_valid_backup($nama)) {
        $backup_valid_ada = true;
        break;
    }

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pengaturan - Avero Komputer</title>

    <link rel="stylesheet" href="style/pengaturan.css?v=2">

    <link rel="stylesheet" href="style/responsive.css?v=3">
</head>

<body><?php include 'navigasi.php'; ?>

    <div class="container">

        <?php if ($pesan !== ''): ?>
            <div class="notice <?php echo $jenis_pesan === 'error' ? 'error' : ''; ?>">
                <?php echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>

        <h1>Pengaturan</h1>

        <div class="card">

            <h2>Data Toko</h2>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_pengaturan'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_pengaturan'], ENT_QUOTES, 'UTF-8'); ?>">

                <label>Nama Toko</label>

                <br><br>

                <input type="text" name="nama_toko" value="<?php echo htmlspecialchars($data_toko["nama_toko"]); ?>"
                    class="inline-63477f99">

                <br><br>

                <label>Alamat</label>

                <br><br>

                <textarea name="alamat" rows="3"
                    class="inline-63477f99"><?php echo htmlspecialchars($data_toko["alamat"]); ?></textarea>

                <br><br>

                <label>Syarat & Ketentuan</label>

                <br><br>

                <textarea name="syarat_ketentuan" rows="8" class="inline-63477f99"><?php echo htmlspecialchars($data_toko["syarat_ketentuan"]); ?>

</textarea>

                <br><br>

                <label>Footer Tanda Terima</label>

                <br><br>

                <textarea name="footer" rows="3"
                    class="inline-63477f99"><?php echo htmlspecialchars($data_toko["footer"] ?? ""); ?></textarea>

                <br><br>

                <label>No. WhatsApp / Telepon</label>
                <br><br>

                <input type="text" name="no_telp" value="<?php echo htmlspecialchars($data_toko["no_telp"]); ?>"
                    class="inline-63477f99">
                <br><br>

                <label>Logo Toko</label>

                <br><br>

                <input type="file" name="logo" accept="image/png,image/jpeg,image/webp">
                <?php if (!empty($data_toko["logo"])) { ?>

                    <br><br>

                    <p>Logo saat ini:</p>

                    <img src="uploads/logo/<?php echo htmlspecialchars($data_toko["logo"]); ?>" alt="Logo Avero Komputer"
                        class="inline-fc06330f">

                <?php } ?>
                <br><br>

                <button type="submit" class="inline-fc9c28ff">
                    Simpan Pengaturan
                </button>

            </form>

        </div>

        <div class="container">
    <div class="card inline-3ae1f62a">
        <h2>Backup &amp; Restore Database</h2>
        <p class="muted">Buat backup untuk langsung mengunduh file SQL ke komputer. Untuk restore, pilih file SQL backup Avero yang pernah Anda unduh.</p>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_pengaturan'], ENT_QUOTES, 'UTF-8'); ?>">
            <button class="btn" type="submit" name="aksi_database" value="backup">Buat Backup &amp; Unduh SQL</button>
        </form>
        <form method="post" enctype="multipart/form-data" onsubmit="return confirm('Restore akan mengganti seluruh isi database dengan data dari file SQL. Sebelum melanjutkan, kondisi database saat ini akan dibackup. Lanjutkan?');" class="inline-4a909ea7">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_pengaturan'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="aksi_database" value="restore">
            <label for="backup_sql">Restore dari file SQL</label>
            <input class="field" type="file" id="backup_sql" name="backup_sql" accept=".sql,application/sql" required>
            <button class="btn secondary" type="submit">Upload &amp; Restore</button>
        </form>
        <p class="muted">Restore hanya menerima file SQL backup Avero dengan struktur tabel yang cocok. Maksimal 25 MB.</p>
    </div>

    <div class="card inline-3ae1f62a">
                <h2>Reset Data Operasional</h2>
                <p>Reset menghapus data pelanggan, barang, dan seluruh data servis. Akun admin dan pengaturan toko tetap
                    tersimpan agar aplikasi tetap dapat digunakan.</p>
                <?php if (!$backup_valid_ada): ?>
                    <p class="notice error">Reset terkunci sampai setidaknya satu backup SQL berhasil dibuat dan masih
                        valid.</p><?php endif; ?>
                <form method="post"
                    onsubmit="return confirm('Data operasional akan dikosongkan. Pastikan backup tersedia. Lanjutkan?');">
                    <input type="hidden" name="csrf_token"
                        value="<?php echo htmlspecialchars($_SESSION['csrf_pengaturan'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="aksi_database" value="reset">
                    <label for="konfirmasi_reset">Ketik <strong>RESET</strong> untuk mengonfirmasi</label><br><br>
                    <input class="field" id="konfirmasi_reset" name="konfirmasi_reset" autocomplete="off"
                        placeholder="RESET" required>
                    <br><br><button class="btn danger" type="submit" <?php echo !$backup_valid_ada ? 'disabled' : ''; ?>>Reset Data</button>
                </form>
            </div>
        </div>

</body>

</html>
