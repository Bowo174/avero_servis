<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once 'app_helper.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifyCsrfToken()) {
    http_response_code(403);
    exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
}
require_once 'koneksi.php';
$nomor = trim($_GET['nomor'] ?? $_POST['nomor_servis'] ?? '');
$pesan = '';
$jenis = 'ok';
$statuses = ['Diterima', 'Pemeriksaan', 'Menunggu Persetujuan', 'Menunggu Sparepart', 'Sparepart Telah Tiba', 'Dalam Pengerjaan', 'Selesai', 'Sudah Diambil'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $baru = $_POST['status_baru'] ?? '';
    $st = mysqli_prepare($koneksi, 'SELECT s.id_servis,s.nomor_servis,s.status_servis,p.nama,p.no_telp,b.nama_barang FROM servis s JOIN pelanggan p ON p.id_pelanggan=s.id_pelanggan JOIN barang b ON b.id_barang=s.id_barang WHERE s.nomor_servis=?');
    mysqli_stmt_bind_param($st, 's', $nomor);
    mysqli_stmt_execute($st);
    $svc = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    if (!$svc || !in_array($baru, $statuses, true)) {
        $pesan = 'Servis atau status tidak valid.';
        $jenis = 'error';
    } elseif ($svc['status_servis'] === $baru) {
        $pesan = 'Status servis tidak berubah.';
    } else {
        $id = (int) $svc['id_servis'];
        $cat = 'Status servis diubah menjadi ' . $baru;
        $pesanWA = buildServiceStatusMessage($svc['nama'], $svc['nomor_servis'], $svc['nama_barang'], $baru);
        $jenisNotif = 'whatsapp';
        $queue = 'menunggu';
        mysqli_begin_transaction($koneksi);
        try {
            $update = "UPDATE servis SET status_servis=?";
            if ($baru === 'Selesai')
                $update .= ', tanggal_selesai=COALESCE(tanggal_selesai,NOW())';
            if ($baru === 'Sudah Diambil')
                $update .= ', tanggal_diambil=COALESCE(tanggal_diambil,NOW())';
            $update .= ' WHERE id_servis=?';
            $st = mysqli_prepare($koneksi, $update);
            mysqli_stmt_bind_param($st, 'si', $baru, $id);
            if (!mysqli_stmt_execute($st))
                throw new Exception();
            $st = mysqli_prepare($koneksi, 'INSERT INTO riwayat_status(id_servis,status,catatan,id_user) VALUES(?,?,?,?)');
            $uid = (int) $_SESSION['id_user'];
            mysqli_stmt_bind_param($st, 'issi', $id, $baru, $cat, $uid);
            if (!mysqli_stmt_execute($st))
                throw new Exception();
            $st = mysqli_prepare($koneksi, 'INSERT INTO notifikasi(id_servis,no_tujuan,jenis,pesan,status_kirim) VALUES(?,?,?,?,?)');
            mysqli_stmt_bind_param($st, 'issss', $id, $svc['no_telp'], $jenisNotif, $pesanWA, $queue);
            if (!mysqli_stmt_execute($st))
                throw new Exception();
            mysqli_commit($koneksi);
            $pesan = 'Status berhasil diperbarui dan notifikasi masuk antrean.';
        } catch (Throwable $e) {
            mysqli_rollback($koneksi);
            $pesan = 'Perubahan gagal disimpan. Coba lagi.';
            $jenis = 'error';
        }
    }
}
$data = null;
if ($nomor !== '') {
    $st = mysqli_prepare($koneksi, 'SELECT s.id_servis,s.nomor_servis,s.status_servis,p.nama,b.nama_barang,b.foto_barang FROM servis s JOIN pelanggan p ON p.id_pelanggan=s.id_pelanggan JOIN barang b ON b.id_barang=s.id_barang WHERE s.nomor_servis=?');
    mysqli_stmt_bind_param($st, 's', $nomor);
    mysqli_stmt_execute($st);
    $data = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ubah Status Servis</title>
    <link rel="stylesheet" href="style/ubah_status.css?v=2">
    <link rel="stylesheet" href="style/responsive.css?v=3">
</head>

<body><?php include 'navigasi.php'; ?>
    <main class="wrap">
        <section class="card">
            <h1>Ubah Status Servis</h1><?php if ($pesan): ?>
                <p class="notice <?php echo $jenis === 'error' ? 'error' : ''; ?>"><?php echo h($pesan); ?></p>
            <?php endif; ?><?php if ($data): ?>
                <div class="service-detail-layout">
                    <div class="info"><b><?php echo h($data['nomor_servis']); ?></b><br>Pelanggan:
                        <?php echo h($data['nama']); ?><br>Barang: <?php echo h($data['nama_barang']); ?><br>Status saat
                        ini: <b><?php echo h($data['status_servis']); ?></b></div>
                    <?php if (!empty($data['foto_barang'])):
                        $foto = 'uploads/barang/' . rawurlencode($data['foto_barang']); ?><button
                            type="button" class="photo-preview service-photo" data-preview-src="<?php echo h($foto); ?>"
                            data-preview-alt="Foto <?php echo h($data['nama_barang']); ?>"
                            aria-label="Lihat foto <?php echo h($data['nama_barang']); ?>"><img src="<?php echo h($foto); ?>"
                                alt="Foto <?php echo h($data['nama_barang']); ?>"></button><?php endif; ?>
                </div>
                <form method="post"><?php csrfField(); ?><input type="hidden" name="nomor_servis"
                        value="<?php echo h($data['nomor_servis']); ?>"><label for="status">Status baru</label><select
                        id="status" name="status_baru" required><?php foreach ($statuses as $s): ?>
                            <option value="<?php echo h($s); ?>" <?php echo $data['status_servis'] === $s ? 'selected' : ''; ?>>
                                <?php echo h($s); ?></option><?php endforeach; ?>
                    </select><button class="btn">Simpan status</button></form>
                <p><a href="transaksi.php?id=<?php echo (int) $data['id_servis']; ?>">Buka transaksi / input rincian
                        biaya</a></p><?php else: ?>
                <p>Data servis tidak ditemukan.</p><?php endif; ?>
        </section>
    </main>
    <script src="js/image-preview.js" defer></script>
</body>

</html>