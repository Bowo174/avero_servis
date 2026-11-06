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
$pelangganTerpilih = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pilihan = trim($_POST['id_pelanggan'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $telp = trim($_POST['no_telp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    if ($pilihan !== '') {
        $idTerpilih = (int) $pilihan;
        $stPelanggan = mysqli_prepare($koneksi, 'SELECT id_pelanggan,nama,no_telp FROM pelanggan WHERE id_pelanggan=?');
        mysqli_stmt_bind_param($stPelanggan, 'i', $idTerpilih);
        mysqli_stmt_execute($stPelanggan);
        $pelangganTerpilih = mysqli_fetch_assoc(mysqli_stmt_get_result($stPelanggan)) ?: null;
        if (!$pelangganTerpilih) { $pilihan = ''; $error = 'Pelanggan yang dipilih tidak ditemukan. Silakan cari kembali.'; }
    }
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
            $pelangganTerpilih = null;
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
    <link rel="stylesheet" href="style/tambah_servis.css?v=3">
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
                <h2>Data pelanggan</h2>
                <label for="cari-pelanggan">Pelanggan terdaftar</label>
                <div class="customer-search">
                    <input id="cari-pelanggan" type="search" autocomplete="off" placeholder="Ketik nama pelanggan untuk mencari..." aria-autocomplete="list" aria-controls="hasil-pelanggan" aria-expanded="false" value="<?php echo h($pelangganTerpilih['nama'] ?? ''); ?>">
                    <input type="hidden" name="id_pelanggan" id="id-pelanggan" value="<?php echo (int) ($pelangganTerpilih['id_pelanggan'] ?? 0) ?: ''; ?>">
                    <div id="hasil-pelanggan" class="customer-results" role="listbox" hidden></div>
                </div>
                <div id="pelanggan-terpilih" class="customer-selected" <?php echo $pelangganTerpilih ? '' : 'hidden'; ?>><?php if ($pelangganTerpilih): ?>Terdaftar: <strong><?php echo h($pelangganTerpilih['nama']); ?></strong> · <?php echo h($pelangganTerpilih['no_telp']); ?><?php endif; ?></div>
                <button type="button" id="pelanggan-baru-toggle" class="customer-new-toggle" <?php echo !$pelangganTerpilih ? 'hidden' : ''; ?>>+ Pelanggan baru</button>
                <div id="pelanggan-baru" <?php echo $pelangganTerpilih ? 'hidden' : ''; ?>>
                    <div class="grid">
                        <div><label>Nama</label><input name="nama" maxlength="100" value="<?php echo h($_POST['nama'] ?? ''); ?>" <?php echo $pelangganTerpilih ? '' : 'required'; ?>></div>
                        <div><label>No. WhatsApp</label><input name="no_telp" maxlength="30" value="<?php echo h($_POST['no_telp'] ?? ''); ?>" <?php echo $pelangganTerpilih ? '' : 'required'; ?>></div>
                    </div><label>Alamat</label><textarea name="alamat"><?php echo h($_POST['alamat'] ?? ''); ?></textarea>
                </div></section>
            <section class="card">
                <h2>Barang dan keluhan</h2><label>Nama barang</label><input name="nama_barang" required maxlength="100"
                    placeholder="Laptop, printer, PC…"
                    value="<?php echo h($_POST['nama_barang'] ?? ''); ?>"><label>Kelengkapan</label><textarea
                    name="kelengkapan"
                    placeholder="Charger, tas, kabel daya…"><?php echo h($_POST['kelengkapan'] ?? ''); ?></textarea><label>Foto
                    barang (JPG, PNG, WebP; maks. 8 MB)</label><input type="file" name="foto_barang" id="foto"
                    accept="image/jpeg,image/png,image/webp" capture="environment"><img id="preview" class="preview"
                    alt="Pratinjau foto"><p id="foto-info" class="photo-info" aria-live="polite"></p><label>Keluhan</label><textarea name="keluhan"
                    required><?php echo h($_POST['keluhan'] ?? ''); ?></textarea><label><input type="checkbox"
                        name="syarat_setuju" value="1" required class="inline-4f15898a"> Saya menyetujui syarat dan
                    ketentuan servis.</label>
            </section>
            <section class="card"><button class="btn">Simpan &amp; Buat Tanda Terima</button></section>
        </form>
    </main>
    <script>
        const cariPelanggan = document.getElementById('cari-pelanggan');
        const idPelanggan = document.getElementById('id-pelanggan');
        const hasilPelanggan = document.getElementById('hasil-pelanggan');
        const dataPelangganBaru = document.getElementById('pelanggan-baru');
        const tombolPelangganBaru = document.getElementById('pelanggan-baru-toggle');
        const pelangganTerpilih = document.getElementById('pelanggan-terpilih');
        let timerCariPelanggan, requestPelanggan;
        function tutupHasilPelanggan() { hasilPelanggan.hidden = true; hasilPelanggan.replaceChildren(); cariPelanggan.setAttribute('aria-expanded', 'false'); }
        function pilihPelanggan(item) {
            idPelanggan.value = item.id; cariPelanggan.value = item.nama;
            pelangganTerpilih.textContent = 'Terdaftar: ' + item.nama + ' · ' + (item.no_telp || '');
            pelangganTerpilih.hidden = false; dataPelangganBaru.hidden = true; tombolPelangganBaru.hidden = false;
            dataPelangganBaru.querySelectorAll('input,textarea').forEach(input => input.required = false); tutupHasilPelanggan();
        }
        cariPelanggan.addEventListener('input', () => {
            idPelanggan.value = ''; pelangganTerpilih.hidden = true; tombolPelangganBaru.hidden = true; dataPelangganBaru.hidden = false;
            dataPelangganBaru.querySelector('[name=nama]').value = cariPelanggan.value;
            dataPelangganBaru.querySelectorAll('input,textarea').forEach(input => input.required = input.name !== 'alamat');
            clearTimeout(timerCariPelanggan); if (requestPelanggan) requestPelanggan.abort();
            const query = cariPelanggan.value.trim(); if (query.length < 2) { tutupHasilPelanggan(); return; }
            timerCariPelanggan = setTimeout(async () => {
                requestPelanggan = new AbortController();
                try {
                    const response = await fetch('cari_pelanggan.php?q=' + encodeURIComponent(query), { signal: requestPelanggan.signal, headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error('Pencarian gagal');
                    const items = await response.json(); hasilPelanggan.replaceChildren();
                    items.slice(0, 5).forEach(item => {
                        const button = document.createElement('button'); button.type = 'button'; button.className = 'customer-result'; button.setAttribute('role', 'option');
                        const name = document.createElement('strong'); name.textContent = item.nama;
                        const phone = document.createElement('span'); phone.textContent = item.no_telp || '';
                        button.append(name, phone); button.addEventListener('click', () => pilihPelanggan(item)); hasilPelanggan.append(button);
                    });
                    if (!items.length) { const empty = document.createElement('div'); empty.className = 'customer-empty'; empty.textContent = 'Tidak ditemukan. Isi data pelanggan baru di bawah.'; hasilPelanggan.append(empty); }
                    hasilPelanggan.hidden = false; cariPelanggan.setAttribute('aria-expanded', 'true');
                } catch (error) { if (error.name !== 'AbortError') tutupHasilPelanggan(); }
            }, 250);
        });
        tombolPelangganBaru.addEventListener('click', () => {
            idPelanggan.value = ''; cariPelanggan.value = ''; pelangganTerpilih.hidden = true; tombolPelangganBaru.hidden = true; dataPelangganBaru.hidden = false;
            dataPelangganBaru.querySelectorAll('input,textarea').forEach(input => input.required = input.name !== 'alamat'); cariPelanggan.focus(); tutupHasilPelanggan();
        });
        document.addEventListener('click', event => { if (!event.target.closest('.customer-search')) tutupHasilPelanggan(); });        const foto = document.getElementById('foto');
        const preview = document.getElementById('preview');
        const infoFoto = document.getElementById('foto-info');
        const formServis = document.getElementById('form-servis');
        const tombolSimpan = formServis.querySelector('button[type="submit"], button:not([type])');
        const BATAS_UPLOAD = 7 * 1024 * 1024;
        let urlPreviewFoto = '';

        function decodeFoto(file) {
            if (window.createImageBitmap) {
                return createImageBitmap(file).then(bitmap => ({
                    width: bitmap.width,
                    height: bitmap.height,
                    draw: (ctx, width, height) => ctx.drawImage(bitmap, 0, 0, width, height),
                    close: () => bitmap.close()
                }));
            }
            return new Promise((resolve, reject) => {
                const image = new Image();
                const url = URL.createObjectURL(file);
                image.onload = () => resolve({
                    width: image.naturalWidth,
                    height: image.naturalHeight,
                    draw: (ctx, width, height) => ctx.drawImage(image, 0, 0, width, height),
                    close: () => URL.revokeObjectURL(url)
                });
                image.onerror = () => { URL.revokeObjectURL(url); reject(new Error('Gambar tidak dapat dibaca')); };
                image.src = url;
            });
        }

        function buatJpeg(canvas, kualitas) {
            return new Promise((resolve, reject) => {
                canvas.toBlob(blob => blob ? resolve(blob) : reject(new Error('Kompresi JPEG gagal')), 'image/jpeg', kualitas);
            });
        }

        async function kompresFoto(file) {
            const image = await decodeFoto(file);
            let scale = Math.min(1, 1600 / Math.max(image.width, image.height));
            let kualitas = 0.82;
            let hasil = null;
            try {
                for (let percobaan = 0; percobaan < 12; percobaan++) {
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.max(1, Math.round(image.width * scale));
                    canvas.height = Math.max(1, Math.round(image.height * scale));
                    const context = canvas.getContext('2d', { alpha: false });
                    if (!context) throw new Error('Browser tidak dapat memproses gambar');
                    context.fillStyle = '#fff';
                    context.fillRect(0, 0, canvas.width, canvas.height);
                    image.draw(context, canvas.width, canvas.height);
                    hasil = await buatJpeg(canvas, kualitas);
                    canvas.width = 0;
                    canvas.height = 0;
                    if (hasil.size <= BATAS_UPLOAD) break;
                    if (kualitas > 0.52) {
                        kualitas = Math.max(0.52, kualitas - 0.1);
                    } else {
                        scale *= 0.82;
                        kualitas = 0.76;
                    }
                }
            } finally {
                image.close();
            }
            if (!hasil || hasil.size > BATAS_UPLOAD) throw new Error('Ukuran foto belum bisa diturunkan di bawah 7 MB');
            const nama = file.name.replace(/\.[^.]+$/, '') || 'foto-servis';
            return new File([hasil], nama + '.jpg', { type: 'image/jpeg', lastModified: Date.now() });
        }

        foto.addEventListener('change', async () => {
            const file = foto.files && foto.files[0];
            if (!file) { infoFoto.textContent = ''; return; }
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                alert('Pilih foto JPG, PNG, atau WebP.');
                foto.value = '';
                return;
            }
            if (file.size > 30 * 1024 * 1024) {
                alert('Foto sumber terlalu besar untuk diproses di HP (maksimal 30 MB).');
                foto.value = '';
                return;
            }
            infoFoto.textContent = 'Sedang mengecilkan ukuran foto...';
            tombolSimpan.disabled = true;
            try {
                const hasil = await kompresFoto(file);
                const transfer = new DataTransfer();
                transfer.items.add(hasil);
                foto.files = transfer.files;
                if (urlPreviewFoto) URL.revokeObjectURL(urlPreviewFoto);
                urlPreviewFoto = URL.createObjectURL(hasil);
                preview.src = urlPreviewFoto;
                preview.style.display = 'block';
                infoFoto.textContent = 'Foto siap diunggah: ' + (hasil.size / 1024 / 1024).toFixed(2) + ' MB';
            } catch (error) {
                foto.value = '';
                preview.removeAttribute('src');
                preview.style.display = 'none';
                infoFoto.textContent = error.message || 'Foto gagal dikompres. Coba foto dengan resolusi lebih rendah.';
            } finally {
                tombolSimpan.disabled = false;
            }
        });
    </script>
</body>

</html>
