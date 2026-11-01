<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once 'app_helper.php';
requireLogin();
require_once 'koneksi.php';
$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$q = trim($_GET['q'] ?? '');
$validDate = static function ($v) {
    $d = DateTime::createFromFormat('Y-m-d', $v);
    return $d && $d->format('Y-m-d') === $v; };
if (!$validDate($dari))
    $dari = date('Y-m-01');
if (!$validDate($sampai))
    $sampai = date('Y-m-d');
if ($dari > $sampai) {
    $tmp = $dari;
    $dari = $sampai;
    $sampai = $tmp;
}
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = pageLimit();
$offset = ($page - 1) * $limit;
$like = '%' . $q . '%';
$summary = mysqli_prepare($koneksi, 'SELECT COUNT(*) jumlah,COUNT(DISTINCT DATE(s.tanggal_masuk)) hari,COALESCE(SUM((SELECT SUM(d.jumlah*d.harga_satuan) FROM detail_servis d WHERE d.id_servis=s.id_servis)),0) omzet FROM servis s JOIN pelanggan p ON p.id_pelanggan=s.id_pelanggan JOIN barang b ON b.id_barang=s.id_barang WHERE DATE(s.tanggal_masuk) BETWEEN ? AND ? AND (p.nama LIKE ? OR b.nama_barang LIKE ? OR s.nomor_servis LIKE ?)');
mysqli_stmt_bind_param($summary, 'sssss', $dari, $sampai, $like, $like, $like);
mysqli_stmt_execute($summary);
$sum = mysqli_fetch_assoc(mysqli_stmt_get_result($summary));
$cnt = mysqli_prepare($koneksi, 'SELECT COUNT(*) total FROM servis s JOIN pelanggan p ON p.id_pelanggan=s.id_pelanggan JOIN barang b ON b.id_barang=s.id_barang WHERE DATE(s.tanggal_masuk) BETWEEN ? AND ? AND (p.nama LIKE ? OR b.nama_barang LIKE ? OR s.nomor_servis LIKE ?)');
mysqli_stmt_bind_param($cnt, 'sssss', $dari, $sampai, $like, $like, $like);
mysqli_stmt_execute($cnt);
$total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($cnt))['total'];
$rows = mysqli_prepare($koneksi, 'SELECT s.nomor_servis,s.tanggal_masuk,s.status_servis,p.nama,b.nama_barang,COALESCE((SELECT SUM(d.jumlah*d.harga_satuan) FROM detail_servis d WHERE d.id_servis=s.id_servis),0) total FROM servis s JOIN pelanggan p ON p.id_pelanggan=s.id_pelanggan JOIN barang b ON b.id_barang=s.id_barang WHERE DATE(s.tanggal_masuk) BETWEEN ? AND ? AND (p.nama LIKE ? OR b.nama_barang LIKE ? OR s.nomor_servis LIKE ?) ORDER BY s.tanggal_masuk DESC LIMIT ? OFFSET ?');
mysqli_stmt_bind_param($rows, 'sssssii', $dari, $sampai, $like, $like, $like, $limit, $offset);
mysqli_stmt_execute($rows);
$result = mysqli_stmt_get_result($rows);
$dailyPage = max(1, (int) ($_GET['page_daily'] ?? 1));
$dailyOffset = ($dailyPage - 1) * $limit;
$dc = mysqli_prepare($koneksi, 'SELECT COUNT(*) total FROM (SELECT DATE(s.tanggal_masuk) tanggal,b.id_barang FROM servis s JOIN barang b ON b.id_barang=s.id_barang JOIN pelanggan p ON p.id_pelanggan=s.id_pelanggan WHERE DATE(s.tanggal_masuk) BETWEEN ? AND ? AND (p.nama LIKE ? OR b.nama_barang LIKE ? OR s.nomor_servis LIKE ?) GROUP BY DATE(s.tanggal_masuk),b.id_barang) grouped_rows');
mysqli_stmt_bind_param($dc, 'sssss', $dari, $sampai, $like, $like, $like);
mysqli_stmt_execute($dc);
$dailyTotal = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($dc))['total'];
$daily = mysqli_prepare($koneksi, 'SELECT DATE(s.tanggal_masuk) tanggal,b.nama_barang,COUNT(*) jumlah FROM servis s JOIN barang b ON b.id_barang=s.id_barang JOIN pelanggan p ON p.id_pelanggan=s.id_pelanggan WHERE DATE(s.tanggal_masuk) BETWEEN ? AND ? AND (p.nama LIKE ? OR b.nama_barang LIKE ? OR s.nomor_servis LIKE ?) GROUP BY DATE(s.tanggal_masuk),b.id_barang ORDER BY tanggal DESC,jumlah DESC LIMIT ? OFFSET ?');
mysqli_stmt_bind_param($daily, 'sssssii', $dari, $sampai, $like, $like, $like, $limit, $dailyOffset);
mysqli_stmt_execute($daily);
$dailyResult = mysqli_stmt_get_result($daily);
$statusRows = mysqli_prepare($koneksi, 'SELECT s.status_servis,COUNT(*) jumlah,COALESCE(SUM((SELECT SUM(d.jumlah*d.harga_satuan) FROM detail_servis d WHERE d.id_servis=s.id_servis)),0) total FROM servis s JOIN barang b ON b.id_barang=s.id_barang JOIN pelanggan p ON p.id_pelanggan=s.id_pelanggan WHERE DATE(s.tanggal_masuk) BETWEEN ? AND ? AND (p.nama LIKE ? OR b.nama_barang LIKE ? OR s.nomor_servis LIKE ?) GROUP BY s.status_servis ORDER BY jumlah DESC');
mysqli_stmt_bind_param($statusRows, 'sssss', $dari, $sampai, $like, $like, $like);
mysqli_stmt_execute($statusRows);
$statusResult = mysqli_stmt_get_result($statusRows);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Laporan Servis</title>
    <link rel="stylesheet" href="style/laporan.css?v=2">
    <link rel="stylesheet" href="style/responsive.css?v=3">
</head>

<body><?php include 'navigasi.php'; ?>
    <main class="wrap">
        <h1>Laporan Servis</h1>
        <section class="card">
            <form class="filters"><label>Dari <input type="date" name="dari"
                        value="<?php echo h($dari); ?>"></label><label>Sampai <input type="date" name="sampai"
                        value="<?php echo h($sampai); ?>"></label><input name="q"
                    placeholder="Cari pelanggan, barang, nomor" value="<?php echo h($q); ?>"><button
                    class="btn">Tampilkan laporan</button></form>
        </section>
        <section class="stats">
            <div class="stat">Jumlah servis<strong><?php echo (int) $sum['jumlah']; ?> barang</strong></div>
            <div class="stat">Hari dengan servis<strong><?php echo (int) $sum['hari']; ?> hari</strong></div>
            <div class="stat">Total biaya tercatat<strong>Rp
                    <?php echo number_format((int) $sum['omzet'], 0, ',', '.'); ?></strong></div>
        </section>
        <section class="card">
            <h2>Ringkasan berdasarkan status</h2>
            <div class="status-grid"><?php while ($sr = mysqli_fetch_assoc($statusResult)): ?>
                    <article class="status-card">
                        <strong><?php echo h($sr['status_servis']); ?></strong><?php echo (int) $sr['jumlah']; ?>
                        servis<br><small>Rp <?php echo number_format((int) $sr['total'], 0, ',', '.'); ?></small></article>
                <?php endwhile; ?>
            </div>
        </section>
        <section class="card">
            <h2>Servis per barang per hari</h2>
            <div class="scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama barang</th>
                            <th>Jumlah servis</th>
                        </tr>
                    </thead>
                    <tbody><?php while ($d = mysqli_fetch_assoc($dailyResult)): ?>
                            <tr>
                                <td><?php echo h(date('d-m-Y', strtotime($d['tanggal']))); ?></td>
                                <td><?php echo h($d['nama_barang']); ?></td>
                                <td><?php echo (int) $d['jumlah']; ?></td>
                            </tr><?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php renderPagination($dailyTotal, $dailyPage, ['dari' => $dari, 'sampai' => $sampai, 'q' => $q], 'page_daily'); ?>
        </section>
        <section class="card">
            <h2>Rincian transaksi servis</h2>
            <div class="scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal masuk</th>
                            <th>No. Servis</th>
                            <th>Pelanggan</th>
                            <th>Barang</th>
                            <th>Status</th>
                            <th>Total biaya</th>
                        </tr>
                    </thead>
                    <tbody><?php while ($r = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo h(date('d-m-Y H:i', strtotime($r['tanggal_masuk']))); ?></td>
                                <td><?php echo h($r['nomor_servis']); ?></td>
                                <td><?php echo h($r['nama']); ?></td>
                                <td><?php echo h($r['nama_barang']); ?></td>
                                <td><?php echo h($r['status_servis']); ?></td>
                                <td>Rp <?php echo number_format((int) $r['total'], 0, ',', '.'); ?></td>
                            </tr><?php endwhile; ?><?php if (!$total): ?>
                            <tr>
                                <td colspan="6">Tidak ada data pada rentang tanggal tersebut.</td>
                            </tr><?php endif; ?>
                    </tbody>
                </table>
            </div><?php renderPagination($total, $page, ['dari' => $dari, 'sampai' => $sampai, 'q' => $q]); ?>
        </section>
    </main>
</body>

</html>