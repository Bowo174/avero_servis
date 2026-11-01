<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once 'app_helper.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifyCsrfToken()) {
    http_response_code(403);
    exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
}
require_once 'koneksi.php';
$error = '';
$sukses = '';
$nomor_baru = '';
$pelanggan = [];
$r = mysqli_query($koneksi, 'SELECT id_pelanggan,nama,no_telp FROM pelanggan ORDER BY nama');
while ($x = mysqli_fetch_assoc($r))
    $pelanggan[] = $x;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pilihan = trim($_POST['id_pelanggan'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $telp = trim($_POST['no_telp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $namaBarang = trim($_POST['nama_barang'] ?? '');
    $kelengkapan = trim($_POST['kelengkapan'] ?? '');
    $keluhan = trim($_POST['keluhan'] ?? '');
    $disimpanFoto = null;
    if ($namaBarang === '' || $keluhan === '' || ($pilihan === '' && ($nama === '' || $telp === '')))
        $error = 'Lengkapi pelanggan, nama barang, dan keluhan servis.';
    if (empty($_POST['syarat_setuju']))
        $error = 'Persetujuan syarat dan ketentuan wajib dicentang.';
    if (!$error && isset($_FILES['foto_barang']) && $_FILES['foto_barang']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['foto_barang'];
        if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > 8 * 1024 * 1024 || !is_uploaded_file($f['tmp_name']) || @getimagesize($f['tmp_name']) === false) {
            $error = 'Foto tidak valid atau ukurannya lebih dari 8 MB.';
        } else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
            $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? '';
            if ($ext === '')
                $error = 'Format foto harus JPG, PNG, atau WebP.';
            else {
                $dir = __DIR__ . '/uploads/barang';
                if (!is_dir($dir))
                    mkdir($dir, 0755, true);
                $disimpanFoto = 'barang_' . bin2hex(random_bytes(12)) . '.' . $ext;
                if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $disimpanFoto)) {
                    $error = 'Foto gagal disimpan.';
                    $disimpanFoto = null;
                }
            }
        }
    }
    if (!$error) {
        mysqli_begin_transaction($koneksi);
        try {
            if ($pilihan !== '') {
                $idP = (int) $pilihan;
                $st = mysqli_prepare($koneksi, 'SELECT id_pelanggan FROM pelanggan WHERE id_pelanggan=?');
                mysqli_stmt_bind_param($st, 'i', $idP);
                mysqli_stmt_execute($st);
                if (!mysqli_fetch_assoc(mysqli_stmt_get_result($st)))
                    throw new Exception('Pelanggan yang dipilih tidak ditemukan.');
            } else {
                $st = mysqli_prepare($koneksi, 'INSERT INTO pelanggan (nama,no_telp,alamat) VALUES (?,?,?)');
                mysqli_stmt_bind_param($st, 'sss', $nama, $telp, $alamat);
                if (!mysqli_stmt_execute($st))
                    throw new Exception('Data pelanggan gagal disimpan.');
                $idP = mysqli_insert_id($koneksi);
            }
            $st = mysqli_prepare($koneksi, 'INSERT INTO barang (id_pelanggan,nama_barang,kelengkapan,foto_barang) VALUES (?,?,?,?)');
            mysqli_stmt_bind_param($st, 'isss', $idP, $namaBarang, $kelengkapan, $disimpanFoto);
            if (!mysqli_stmt_execute($st))
                throw new Exception('Data barang gagal disimpan.');
            $idB = mysqli_insert_id($koneksi);
            $nomorSementara = 'TMP-' . bin2hex(random_bytes(12));
            $token = bin2hex(random_bytes(32));
            $status = 'Diterima';
            $setuju = 1;
            $st = mysqli_prepare($koneksi, 'INSERT INTO servis (nomor_servis,id_pelanggan,id_barang,keluhan,status_servis,qr_token,syarat_setuju) VALUES (?,?,?,?,?,?,?)');
            mysqli_stmt_bind_param($st, 'siisssi', $nomorSementara, $idP, $idB, $keluhan, $status, $token, $setuju);
            if (!mysqli_stmt_execute($st))
                throw new Exception('Servis gagal disimpan.');
            $idS = mysqli_insert_id($koneksi);
            $nomor_baru = 'AVR-' . str_pad((string) $idS, 5, '0', STR_PAD_LEFT);
            $st = mysqli_prepare($koneksi, 'UPDATE servis SET nomor_servis=? WHERE id_servis=?');
            mysqli_stmt_bind_param($st, 'si', $nomor_baru, $idS);
            if (!mysqli_stmt_execute($st))
                throw new Exception('Nomor servis gagal dibuat.');
            $cat = 'Servis diterima oleh Avero Komputer.';
            $st = mysqli_prepare($koneksi, 'INSERT INTO riwayat_status (id_servis,status,catatan) VALUES (?,?,?)');
            mysqli_stmt_bind_param($st, 'iss', $idS, $status, $cat);
            if (!mysqli_stmt_execute($st))
                throw new Exception('Riwayat servis gagal disimpan.');
            mysqli_commit($koneksi);
            $sukses = 'Servis berhasil disimpan.';
            $pelanggan = [];
            $r = mysqli_query($koneksi, 'SELECT id_pelanggan,nama,no_telp FROM pelanggan ORDER BY nama');
            while ($x = mysqli_fetch_assoc($r))
                $pelanggan[] = $x;
        } catch (Throwable $e) {
            mysqli_rollback($koneksi);
            if ($disimpanFoto && is_file(__DIR__ . '/uploads/barang/' . $disimpanFoto))
                unlink(__DIR__ . '/uploads/barang/' . $disimpanFoto);
            $error = $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tambah Servis</title>
    <link rel="stylesheet" href="style/tambah_servis.css?v=2">
    <link rel="stylesheet" href="style/responsive.css?v=3">
</head>

<body><?php include 'navigasi.php'; ?>
    <main class="wrap">
        <h1>Tambah Servis</h1><?php if ($error): ?>
            <div class="notice error"><?php echo h($error); ?></div><?php endif; ?><?php if ($sukses): ?>
            <div class="notice"><?php echo h($sukses); ?> Nomor: <strong><?php echo h($nomor_baru); ?></strong> · <a
                    href="transaksi.php?id=<?php echo urlencode((string) $idS); ?>">Lanjut ke transaksi &amp; cetak</a></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" id="form-servis"><?php csrfField(); ?>
            <section class="card">
                <h2>Data pelanggan</h2><label>Pelanggan terdaftar</label><select name="id_pelanggan" id="pelanggan">
                    <option value="">+ Pelanggan baru</option><?php foreach ($pelanggan as $p): ?>
                        <option value="<?php echo (int) $p['id_pelanggan']; ?>" data-telp="<?php echo h($p['no_telp']); ?>"
                            <?php echo (($_POST['id_pelanggan'] ?? '') == $p['id_pelanggan']) ? 'selected' : ''; ?>>
                            <?php echo h($p['nama'] . ' · ' . $p['no_telp']); ?></option><?php endforeach; ?>
                </select>
                <div id="pelanggan-baru">
                    <div class="grid">
                        <div><label>Nama</label><input name="nama" maxlength="100"
                                value="<?php echo h($_POST['nama'] ?? ''); ?>"></div>
                        <div><label>No. WhatsApp</label><input name="no_telp" maxlength="30"
                                value="<?php echo h($_POST['no_telp'] ?? ''); ?>"></div>
                    </div><label>Alamat</label><textarea name="alamat"><?php echo h($_POST['alamat'] ?? ''); ?></textarea>
                </div>
            </section>
            <section class="card">
                <h2>Barang dan keluhan</h2><label>Nama barang</label><input name="nama_barang" required maxlength="100"
                    placeholder="Laptop, printer, PC…"
                    value="<?php echo h($_POST['nama_barang'] ?? ''); ?>"><label>Kelengkapan</label><textarea
                    name="kelengkapan"
                    placeholder="Charger, tas, kabel daya…"><?php echo h($_POST['kelengkapan'] ?? ''); ?></textarea><label>Foto
                    barang (JPG, PNG, WebP; maks. 8 MB)</label><input type="file" name="foto_barang" id="foto"
                    accept="image/jpeg,image/png,image/webp" capture="environment"><img id="preview" class="preview"
                    alt="Pratinjau foto"><label>Keluhan</label><textarea name="keluhan"
                    required><?php echo h($_POST['keluhan'] ?? ''); ?></textarea><label><input type="checkbox"
                        name="syarat_setuju" value="1" required class="inline-4f15898a"> Saya menyetujui syarat dan
                    ketentuan servis.</label>
            </section>
            <section class="card"><button class="btn">Simpan &amp; Buat Tanda Terima</button></section>
        </form>
    </main>
    <script>
        const pelanggan = document.getElementById('pelanggan'), baru = document.getElementById('pelanggan-baru'); function togglePelanggan() { const pilih = pelanggan.value !== ''; baru.hidden = pilih; baru.querySelectorAll('input,textarea').forEach(e => e.required = !pilih) } pelanggan.addEventListener('change', togglePelanggan); togglePelanggan();
        const foto = document.getElementById('foto'), preview = document.getElementById('preview');
        async function decodeFoto(file) { if (window.createImageBitmap) { try { return await createImageBitmap(file) } catch (e) { } } return new Promise((resolve, reject) => { const image = new Image(), url = URL.createObjectURL(file); image.onload = () => resolve({ width: image.naturalWidth, height: image.naturalHeight, draw: (ctx, w, h) => ctx.drawImage(image, 0, 0, w, h), close: () => URL.revokeObjectURL(url) }); image.onerror = reject; image.src = url }) } foto.addEventListener('change', async () => { const f = foto.files[0]; if (!f) return; if (!f.type.startsWith('image/')) { alert('Pilih file gambar.'); foto.value = ''; return } if (f.size > 20 * 1024 * 1024) { alert('Foto terlalu besar (maksimal 20 MB sebelum kompresi).'); foto.value = ''; return } try { const image = await decodeFoto(f); const scale = Math.min(1, 1440 / Math.max(image.width, image.height)); const canvas = document.createElement('canvas'); canvas.width = Math.round(image.width * scale); canvas.height = Math.round(image.height * scale); const ctx = canvas.getContext('2d'); ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, canvas.width, canvas.height); if (image.draw) image.draw(ctx, canvas.width, canvas.height); else ctx.drawImage(image, 0, 0, canvas.width, canvas.height); if (image.close) image.close(); const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', .82)); if (!blob) throw new Error('Kompresi gagal'); const compressed = new File([blob], f.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg', lastModified: Date.now() }); const transfer = new DataTransfer(); transfer.items.add(compressed); foto.files = transfer.files; preview.src = URL.createObjectURL(compressed); preview.style.display = 'block' } catch (error) { alert('Foto tidak dapat dibaca browser. Coba pilih foto JPG atau PNG dari galeri.'); foto.value = ''; preview.style.display = 'none' } });
    </script>
</body>

</html>