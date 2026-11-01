<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once 'app_helper.php';
requireLogin();
require_once 'koneksi.php';
$q = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = pageLimit();
$offset = ($page - 1) * $limit;
$like = '%' . $q . '%';
$c = mysqli_prepare($koneksi, 'SELECT COUNT(*) total FROM servis s JOIN pelanggan p ON p.id_pelanggan=s.id_pelanggan JOIN barang b ON b.id_barang=s.id_barang WHERE s.status_servis LIKE ? OR p.nama LIKE ? OR b.nama_barang LIKE ? OR s.nomor_servis LIKE ?');
mysqli_stmt_bind_param($c, 'ssss', $like, $like, $like, $like);
mysqli_stmt_execute($c);
$total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($c))['total'];
$st = mysqli_prepare($koneksi, 'SELECT s.nomor_servis,s.tanggal_masuk,s.status_servis,p.nama,b.nama_barang,b.foto_barang FROM servis s JOIN pelanggan p ON p.id_pelanggan=s.id_pelanggan JOIN barang b ON b.id_barang=s.id_barang WHERE s.status_servis LIKE ? OR p.nama LIKE ? OR b.nama_barang LIKE ? OR s.nomor_servis LIKE ? ORDER BY s.id_servis DESC LIMIT ? OFFSET ?');
mysqli_stmt_bind_param($st, 'ssssii', $like, $like, $like, $like, $limit, $offset);
mysqli_stmt_execute($st);
$rows = mysqli_stmt_get_result($st);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Status Servis</title>
    <link rel="stylesheet" href="style/status.css?v=2">
    <link rel="stylesheet" href="style/responsive.css?v=3">
</head>

<body><?php include 'navigasi.php'; ?>
    <main class="wrap">
        <h1>Status Servis</h1>
        <section class="card">
            <form><input name="q" value="<?php echo h($q); ?>"
                    placeholder="Cari status, pelanggan, barang, nomor"><button>Cari</button></form>
            <div class="scroll">
                <table>
                    <thead>
                        <tr>
                            <th>No. Servis</th>
                            <th>Pelanggan</th>
                            <th>Barang</th>
                            <th>Foto</th>
                            <th>Status</th>
                            <th>Tanggal masuk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody><?php while ($r = mysqli_fetch_assoc($rows)): ?>
                            <tr>
                                <td><?php echo h($r['nomor_servis']); ?></td>
                                <td><?php echo h($r['nama']); ?></td>
                                <td><?php echo h($r['nama_barang']); ?></td>
                                <td><?php if (!empty($r['foto_barang'])):
                                    $foto = 'uploads/barang/' . rawurlencode($r['foto_barang']); ?><button
                                            type="button" class="photo-preview" data-preview-src="<?php echo h($foto); ?>"
                                            data-preview-alt="Foto <?php echo h($r['nama_barang']); ?>"
                                            aria-label="Lihat foto <?php echo h($r['nama_barang']); ?>"><img
                                                src="<?php echo h($foto); ?>"
                                                alt="Foto <?php echo h($r['nama_barang']); ?>"></button><?php else: ?>&mdash;<?php endif; ?>
                                </td>
                                <td><?php echo h($r['status_servis']); ?></td>
                                <td><?php echo h(date('d-m-Y H:i', strtotime($r['tanggal_masuk']))); ?></td>
                                <td><a class="btn"
                                        href="ubah_status.php?nomor=<?php echo urlencode($r['nomor_servis']); ?>">Ubah</a>
                                </td>
                            </tr><?php endwhile; ?><?php if (!$total): ?>
                            <tr>
                                <td colspan="7">Data tidak ditemukan.</td>
                            </tr><?php endif; ?>
                    </tbody>
                </table>
            </div><?php renderPagination($total, $page, ['q' => $q]); ?>
        </section>
    </main>
    <script src="js/image-preview.js" defer></script>
</body>

</html>