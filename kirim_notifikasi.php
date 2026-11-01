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
$c = mysqli_prepare($koneksi, 'SELECT COUNT(*) total FROM notifikasi WHERE no_tujuan LIKE ? OR pesan LIKE ? OR status_kirim LIKE ?');
mysqli_stmt_bind_param($c, 'sss', $like, $like, $like);
mysqli_stmt_execute($c);
$total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($c))['total'];
$st = mysqli_prepare($koneksi, 'SELECT id_notifikasi,id_servis,no_tujuan,jenis,pesan,status_kirim,waktu_kirim FROM notifikasi WHERE no_tujuan LIKE ? OR pesan LIKE ? OR status_kirim LIKE ? ORDER BY id_notifikasi DESC LIMIT ? OFFSET ?');
mysqli_stmt_bind_param($st, 'sssii', $like, $like, $like, $limit, $offset);
mysqli_stmt_execute($st);
$rows = mysqli_stmt_get_result($st);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Antrean Notifikasi</title>
    <link rel="stylesheet" href="style/kirim_notifikasi.css?v=2">
    <link rel="stylesheet" href="style/responsive.css?v=3">
</head>

<body><?php include 'navigasi.php'; ?>
    <main class="wrap">
        <h1>Antrean Notifikasi</h1>
        <form><input name="q" value="<?php echo h($q); ?>"
                placeholder="Cari nomor tujuan, pesan, status"><button>Cari</button></form>
        <?php while ($r = mysqli_fetch_assoc($rows)): ?>
            <article class="card"><strong>#<?php echo (int) $r['id_notifikasi']; ?> · Servis
                    #<?php echo (int) $r['id_servis']; ?></strong>
                <p><?php echo h($r['no_tujuan']); ?> · <?php echo h($r['jenis']); ?> · <?php echo h($r['status_kirim']); ?>
                </p>
                <div><?php echo nl2br(h($r['pesan'])); ?></div>
            </article><?php endwhile; ?><?php if (!$total): ?>
            <p>Tidak ada notifikasi.</p><?php endif; ?><?php renderPagination($total, $page, ['q' => $q]); ?>
    </main>
</body>

</html>