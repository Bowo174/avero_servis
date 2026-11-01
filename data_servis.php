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
$c = mysqli_prepare($koneksi, 'SELECT COUNT(*) total FROM servis s JOIN pelanggan p ON p.id_pelanggan=s.id_pelanggan JOIN barang b ON b.id_barang=s.id_barang WHERE s.nomor_servis LIKE ? OR p.nama LIKE ? OR b.nama_barang LIKE ? OR s.status_servis LIKE ?');
mysqli_stmt_bind_param($c, 'ssss', $like, $like, $like, $like);
mysqli_stmt_execute($c);
$total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($c))['total'];
$st = mysqli_prepare($koneksi, 'SELECT s.id_servis,s.nomor_servis,s.tanggal_masuk,s.status_servis,s.qr_token,p.nama,p.no_telp,b.nama_barang FROM servis s JOIN pelanggan p ON p.id_pelanggan=s.id_pelanggan JOIN barang b ON b.id_barang=s.id_barang WHERE s.nomor_servis LIKE ? OR p.nama LIKE ? OR b.nama_barang LIKE ? OR s.status_servis LIKE ? ORDER BY s.id_servis DESC LIMIT ? OFFSET ?');
mysqli_stmt_bind_param($st, 'ssssii', $like, $like, $like, $like, $limit, $offset);
mysqli_stmt_execute($st);
$rows = mysqli_stmt_get_result($st);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Data Servis</title>
    <link rel="stylesheet" href="style/data_servis.css?v=2">
    <link rel="stylesheet" href="style/responsive.css?v=3">
</head>

<body><?php include 'navigasi.php'; ?>
    <main class="wrap">
        <h1>Data Servis</h1>
        <section class="card">
            <form class="bar"><input name="q" value="<?php echo h($q); ?>"
                    placeholder="Cari nomor, pelanggan, barang, status"><button class="btn">Cari</button><a
                    class="btn gray" href="tambah_servis.php">+ Servis baru</a></form>
            <div class="scroll">
                <table>
                    <thead>
                        <tr>
                            <th>No. Servis</th>
                            <th>Pelanggan</th>
                            <th>Barang</th>
                            <th>Status</th>
                            <th>Tanggal masuk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($r = mysqli_fetch_assoc($rows)):
                            $pesan = buildServiceStatusMessage($r['nama'], $r['nomor_servis'], $r['nama_barang'], $r['status_servis']);
                            $wa = 'https://wa.me/62' . ltrim(preg_replace('/\D/', '', $r['no_telp']), '0') . '?text=' . rawurlencode($pesan); ?>
                            <tr>
                                <td><?php echo h($r['nomor_servis']); ?></td>
                                <td><?php echo h($r['nama']); ?><br><small><?php echo h($r['no_telp']); ?></small></td>
                                <td><?php echo h($r['nama_barang']); ?></td>
                                <td><?php echo h($r['status_servis']); ?></td>
                                <td><?php echo h(date('d-m-Y H:i', strtotime($r['tanggal_masuk']))); ?></td>
                                <td>
                                    <a class="btn action-btn" title="Ubah status servis" aria-label="Ubah status servis" data-tooltip="Ubah status" href="ubah_status.php?nomor=<?php echo urlencode($r['nomor_servis']); ?>">✏️</a>
                                    <a class="btn blue action-btn" title="Buka transaksi servis" aria-label="Buka transaksi servis" data-tooltip="Transaksi" href="transaksi.php?id=<?php echo (int) $r['id_servis']; ?>">💰</a>
                                    <a class="btn action-btn" title="Kirim WhatsApp kepada pelanggan" aria-label="Kirim WhatsApp kepada pelanggan" data-tooltip="Kirim WhatsApp" target="_blank" rel="noopener" href="<?php echo h($wa); ?>">💬</a>
                                </td>
                            </tr><?php endwhile; ?><?php if (!$total): ?>
                            <tr>
                                <td colspan="6">Data servis belum ditemukan.</td>
                            </tr><?php endif; ?>
                    </tbody>
                </table>
            </div><?php renderPagination($total, $page, ['q' => $q]); ?>
        </section>
    </main>
</body>

</html>