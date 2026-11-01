<?php
require_once __DIR__ . '/session_bootstrap.php';
if (empty($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}
require_once 'koneksi.php';
require_once 'app_helper.php';
$st = mysqli_query($koneksi, 'SELECT nama_toko,alamat,no_telp,logo FROM pengaturan_toko ORDER BY id_pengaturan DESC LIMIT 1');
$toko = mysqli_fetch_assoc($st) ?: [];
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#0f766e">
    <title>Avero Service</title>
    <link rel="stylesheet" href="style/index.css?v=3">
<link rel="stylesheet" href="style/responsive.css?v=3">
    <script src="js/typewriter.js" defer></script>
</head>

<body><?php include 'navigasi.php'; ?>
    <header class="header">
        <h1>Avero Service</h1>
        <p>Manajemen servis, transaksi, dan laporan Avero Komputer</p>
    </header>
    <main class="wrap">
        <section class="welcome">
            <h2 class="typewriter" id="welcome-title" data-text="Selamat datang, Admin Avero">Selamat datang, Admin Avero</h2>
            <p>Pilih menu untuk melanjutkan pekerjaan.</p>
        </section>
        <nav class="menu"><a href="pelanggan.php"><span class="icon">👥</span>Master Pelanggan</a><a
                href="tambah_servis.php"><span class="icon">➕</span>Tambah Servis</a><a href="data_servis.php"><span
                    class="icon">📋</span>Data Servis</a><a href="barang.php"><span
                    class="icon">📦</span>Barang</a><a href="status.php"><span class="icon">🔄</span>Status
                Servis</a><a href="transaksi.php"><span class="icon">💰</span>Transaksi / Kasir</a><a
                    href="laporan.php"><span class="icon">📊</span>Laporan</a><a href="ubah_password.php"><span class="icon">🔐</span>Ganti Password</a><a href="pengaturan.php"><span
                    class="icon">⚙️</span>Pengaturan Toko</a><a href="logout.php"><span
                    class="icon">🚪</span>Keluar</a></nav>
        <footer class="footer"><?php if (!empty($toko['logo'])): ?><img
                    src="uploads/logo/<?php echo rawurlencode($toko['logo']); ?>" alt="Logo toko"><?php endif; ?>
            <div><strong><?php echo h($toko['nama_toko'] ?? 'Avero Komputer'); ?></strong>
                <p><?php echo nl2br(h($toko['alamat'] ?? '')); ?></p>
                <p>Kontak: <?php echo h($toko['no_telp'] ?? ''); ?></p>
            </div><small class="copy">© <?php echo date('Y'); ?> Avero Komputer</small>
        </footer>
    </main>
</body>

</html>
