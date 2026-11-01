<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once 'app_helper.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifyCsrfToken()) {
    http_response_code(403);
    exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
}
require_once 'koneksi.php';

$pesan = '';
$jenis_pesan = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $no_telp = trim($_POST['no_telp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    if ($nama === '' || $no_telp === '') {
        $pesan = 'Nama dan nomor WhatsApp wajib diisi.';
        $jenis_pesan = 'error';
    } else {
        $stmt = mysqli_prepare($koneksi, 'INSERT INTO pelanggan (nama, no_telp, alamat) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sss', $nama, $no_telp, $alamat);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: pelanggan.php?tersimpan=1');
            exit;
        }
        $pesan = 'Pelanggan gagal disimpan. Silakan coba lagi.';
        $jenis_pesan = 'error';
    }
}
if (isset($_GET['tersimpan']))
    $pesan = 'Pelanggan berhasil ditambahkan.';
$q = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = pageLimit();
$offset = ($page - 1) * $limit;
$like = '%' . $q . '%';
$count = mysqli_prepare($koneksi, 'SELECT COUNT(*) total FROM pelanggan WHERE nama LIKE ? OR no_telp LIKE ? OR alamat LIKE ?');
mysqli_stmt_bind_param($count, 'sss', $like, $like, $like);
mysqli_stmt_execute($count);
$total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($count))['total'];
$stmt = mysqli_prepare($koneksi, 'SELECT id_pelanggan,nama,no_telp,alamat,created_at FROM pelanggan WHERE nama LIKE ? OR no_telp LIKE ? OR alamat LIKE ? ORDER BY id_pelanggan DESC LIMIT ? OFFSET ?');
mysqli_stmt_bind_param($stmt, 'sssii', $like, $like, $like, $limit, $offset);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Master Pelanggan</title>
    <link rel="stylesheet" href="style/pelanggan.css?v=2">
    <link rel="stylesheet" href="style/responsive.css?v=3">
</head>

<body><?php include 'navigasi.php'; ?>
    <main class="wrap">
        <h1>Master Pelanggan</h1><?php if ($pesan): ?>
            <div class="notice <?php echo $jenis_pesan === 'error' ? 'error' : ''; ?>"><?php echo h($pesan); ?></div>
        <?php endif; ?>
        <div class="grid">
            <section class="card">
                <h2>Tambah pelanggan</h2>
                <form method="post"><?php csrfField(); ?><label>Nama</label><input name="nama" required maxlength="100"
                        value="<?php echo h($_POST['nama'] ?? ''); ?>"><label>No. WhatsApp</label><input name="no_telp"
                        required maxlength="30"
                        value="<?php echo h($_POST['no_telp'] ?? ''); ?>"><label>Alamat</label><textarea
                        name="alamat"><?php echo h($_POST['alamat'] ?? ''); ?></textarea>
                    <p><button class="btn">Simpan pelanggan</button></p>
                </form>
            </section>
            <section class="card">
                <h2>Daftar pelanggan (<?php echo $total; ?>)</h2>
                <form class="tools" method="get"><input name="q" placeholder="Cari nama, nomor, alamat"
                        value="<?php echo h($q); ?>"><button class="btn">Cari</button></form>
                <div class="scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>WhatsApp</th>
                                <th>Alamat</th>
                                <th>Terdaftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody><?php while ($row = mysqli_fetch_assoc($hasil)): ?>
                                <tr>
                                    <td><?php echo h($row['nama']); ?></td>
                                    <td><?php echo h($row['no_telp']); ?></td>
                                    <td><?php echo h($row['alamat']); ?></td>
                                    <td><?php echo h(date('d-m-Y', strtotime($row['created_at']))); ?></td>
                                    <td><a class="btn"
                                            href="edit_pelanggan.php?id=<?php echo (int) $row['id_pelanggan']; ?>">Edit</a>
                                    </td>
                                </tr><?php endwhile; ?><?php if ($total === 0): ?>
                                <tr>
                                    <td colspan="5">Pelanggan belum ditemukan.</td>
                                </tr><?php endif; ?>
                        </tbody>
                    </table>
                </div><?php renderPagination($total, $page, ['q' => $q]); ?>
            </section>
        </div>
    </main>
</body>

</html>