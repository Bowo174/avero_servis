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
$c = mysqli_prepare($koneksi, 'SELECT COUNT(*) total FROM barang b JOIN pelanggan p ON p.id_pelanggan=b.id_pelanggan WHERE b.nama_barang LIKE ? OR b.kelengkapan LIKE ? OR p.nama LIKE ?');
mysqli_stmt_bind_param($c, 'sss', $like, $like, $like);
mysqli_stmt_execute($c);
$total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($c))['total'];
$st = mysqli_prepare($koneksi, 'SELECT b.id_barang,b.nama_barang,b.kelengkapan,b.foto_barang,b.created_at,p.nama FROM barang b JOIN pelanggan p ON p.id_pelanggan=b.id_pelanggan WHERE b.nama_barang LIKE ? OR b.kelengkapan LIKE ? OR p.nama LIKE ? ORDER BY b.id_barang DESC LIMIT ? OFFSET ?');
mysqli_stmt_bind_param($st, 'sssii', $like, $like, $like, $limit, $offset);
mysqli_stmt_execute($st);
$rows = mysqli_stmt_get_result($st);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Data Barang</title>
    <link rel="stylesheet" href="style/barang.css?v=2">
    <link rel="stylesheet" href="style/responsive.css?v=3">
</head>

<body><?php include 'navigasi.php'; ?>
    <main class="wrap">
        <h1>Data Barang Servis</h1>
        <section class="card">
            <form class="bar"><input name="q" value="<?php echo h($q); ?>"
                    placeholder="Cari barang, kelengkapan, pemilik"><button class="btn">Cari</button></form>
            <div class="scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Barang</th>
                            <th>Pemilik</th>
                            <th>Kelengkapan</th>
                            <th>Tanggal masuk</th>
                        </tr>
                    </thead>
                    <tbody><?php while ($r = mysqli_fetch_assoc($rows)): ?>
                            <tr>
                                <td><?php if ($r['foto_barang']): ?><img class="thumb"
                                            src="uploads/barang/<?php echo rawurlencode($r['foto_barang']); ?>"
                                            alt="Foto <?php echo h($r['nama_barang']); ?>"
                                            data-full="uploads/barang/<?php echo rawurlencode($r['foto_barang']); ?>"><?php else: ?>—<?php endif; ?>
                                </td>
                                <td><?php echo h($r['nama_barang']); ?></td>
                                <td><?php echo h($r['nama']); ?></td>
                                <td><?php echo h($r['kelengkapan']); ?></td>
                                <td><?php echo h(date('d-m-Y', strtotime($r['created_at']))); ?></td>
                            </tr><?php endwhile; ?><?php if (!$total): ?>
                            <tr>
                                <td colspan="5">Belum ada data barang.</td>
                            </tr><?php endif; ?>
                    </tbody>
                </table>
            </div><?php renderPagination($total, $page, ['q' => $q]); ?>
        </section>
    </main>
    <div class="lightbox" id="lightbox"><button>Tutup ✕</button><img alt="Pratinjau foto barang"></div>
    <script>const box = document.getElementById('lightbox'); document.querySelectorAll('.thumb').forEach(i => i.onclick = () => { box.querySelector('img').src = i.dataset.full; box.classList.add('open') }); box.onclick = e => { if (e.target === box || e.target.tagName === 'BUTTON') box.classList.remove('open') }; document.addEventListener('keydown', e => { if (e.key === 'Escape') box.classList.remove('open') });</script>
</body>

</html>