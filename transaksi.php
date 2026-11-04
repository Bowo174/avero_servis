<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once 'app_helper.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifyCsrfToken()) {
    http_response_code(403);
    exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
}
require_once 'koneksi.php';

$q = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = pageLimit();
$offset = ($page - 1) * $limit;
$like = '%' . $q . '%';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id_servis'] ?? 0);
    $descriptions = $_POST['keterangan'] ?? [];
    $quantities = $_POST['jumlah'] ?? [];
    $prices = $_POST['harga_satuan'] ?? [];
    $detailsToSave = [];
    $valid = true;

    foreach ($descriptions as $i => $description) {
        $description = trim($description);
        $quantity = trim((string) ($quantities[$i] ?? ''));
        $price = trim((string) ($prices[$i] ?? ''));
        if ($description === '' && $quantity === '' && $price === '')
            continue;
        if ($description === '' || strlen($description) > 255 || !ctype_digit($quantity) || (int) $quantity < 1 || (int) $quantity > 2147483647 || !ctype_digit($price) || (int) $price > 2147483647) {
            $valid = false;
            break;
        }
        $detailsToSave[] = [$description, (int) $quantity, (int) $price];
    }

    $check = mysqli_prepare($koneksi, 'SELECT id_servis FROM servis WHERE id_servis = ?');
    mysqli_stmt_bind_param($check, 'i', $id);
    mysqli_stmt_execute($check);
    $serviceExists = mysqli_fetch_assoc(mysqli_stmt_get_result($check));

    if (!$valid || !$serviceExists || count($detailsToSave) === 0) {
        $message = 'Isi minimal satu rincian dengan keterangan, jumlah, dan harga yang valid.';
    } else {
        mysqli_begin_transaction($koneksi);
        try {
            $delete = mysqli_prepare($koneksi, 'DELETE FROM detail_servis WHERE id_servis = ?');
            mysqli_stmt_bind_param($delete, 'i', $id);
            if (!mysqli_stmt_execute($delete))
                throw new Exception('Gagal memperbarui rincian.');

            $insert = mysqli_prepare($koneksi, 'INSERT INTO detail_servis (id_servis, keterangan, jumlah, harga_satuan) VALUES (?, ?, ?, ?)');
            foreach ($detailsToSave as $detail) {
                mysqli_stmt_bind_param($insert, 'isii', $id, $detail[0], $detail[1], $detail[2]);
                if (!mysqli_stmt_execute($insert))
                    throw new Exception('Gagal menyimpan rincian.');
            }
            mysqli_commit($koneksi);
            header('Location: transaksi.php?id=' . $id . '&saved=1');
            exit;
        } catch (Throwable $error) {
            mysqli_rollback($koneksi);
            $message = 'Rincian transaksi gagal disimpan. Silakan coba lagi.';
        }
    }
}

$selected = (int) ($_GET['id'] ?? 0);
$service = null;
$details = [];
if ($selected > 0) {
    $statement = mysqli_prepare($koneksi, 'SELECT s.id_servis, s.nomor_servis, s.tanggal_masuk, s.status_servis, s.keluhan, p.nama, p.no_telp, b.nama_barang FROM servis s JOIN pelanggan p ON p.id_pelanggan = s.id_pelanggan JOIN barang b ON b.id_barang = s.id_barang WHERE s.id_servis = ?');
    mysqli_stmt_bind_param($statement, 'i', $selected);
    mysqli_stmt_execute($statement);
    $service = mysqli_fetch_assoc(mysqli_stmt_get_result($statement));
    if ($service) {
        $statement = mysqli_prepare($koneksi, 'SELECT keterangan, jumlah, harga_satuan FROM detail_servis WHERE id_servis = ? ORDER BY id_detail_servis');
        mysqli_stmt_bind_param($statement, 'i', $selected);
        mysqli_stmt_execute($statement);
        $result = mysqli_stmt_get_result($statement);
        while ($row = mysqli_fetch_assoc($result))
            $details[] = $row;
    }
}

$count = mysqli_prepare($koneksi, 'SELECT COUNT(*) total FROM servis s JOIN pelanggan p ON p.id_pelanggan = s.id_pelanggan JOIN barang b ON b.id_barang = s.id_barang WHERE s.nomor_servis LIKE ? OR p.nama LIKE ? OR b.nama_barang LIKE ?');
mysqli_stmt_bind_param($count, 'sss', $like, $like, $like);
mysqli_stmt_execute($count);
$total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($count))['total'];
$list = mysqli_prepare($koneksi, 'SELECT s.id_servis, s.nomor_servis, s.tanggal_masuk, s.status_servis, p.nama, b.nama_barang, b.foto_barang, COALESCE((SELECT SUM(d.jumlah * d.harga_satuan) FROM detail_servis d WHERE d.id_servis = s.id_servis), 0) total FROM servis s JOIN pelanggan p ON p.id_pelanggan = s.id_pelanggan JOIN barang b ON b.id_barang = s.id_barang WHERE s.nomor_servis LIKE ? OR p.nama LIKE ? OR b.nama_barang LIKE ? ORDER BY s.id_servis DESC LIMIT ? OFFSET ?');
mysqli_stmt_bind_param($list, 'sssii', $like, $like, $like, $limit, $offset);
mysqli_stmt_execute($list);
$rows = mysqli_stmt_get_result($list);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Transaksi Servis</title>
    <link rel="stylesheet" href="style/transaksi.css?v=3">
    <link rel="stylesheet" href="style/responsive.css?v=3">
</head>

<body>
    <?php include 'navigasi.php'; ?>
    <main class="wrap">
        <div class="bar">
            <h1 class="inline-da5cd676">Transaksi Servis</h1><button class="btn" id="open-modal">+ Pilih servis untuk
                kasir</button>
        </div>
        <?php if ($message): ?>
            <div class="msg"><?php echo h($message); ?></div><?php endif; ?>

        <?php if ($service): ?>
            <section class="card">
                <h2>Input rincian biaya</h2>
                <div class="service-meta"><b><?php echo h($service['nomor_servis']); ?></b> Â·
                    <?php echo h($service['nama']); ?> Â·
                    <?php echo h($service['no_telp']); ?><br><?php echo h($service['nama_barang']); ?> Â· Status:
                    <?php echo h($service['status_servis']); ?><br>Keluhan: <?php echo h($service['keluhan']); ?></div>
                <form method="post" id="transaksi-form"><?php csrfField(); ?>
                    <input type="hidden" name="id_servis" value="<?php echo (int) $service['id_servis']; ?>">
                    <div id="detail-list">
                        <?php foreach ($details as $detail): ?>
                            <div class="fields"><input name="keterangan[]" required maxlength="255"
                                    placeholder="Keterangan / jasa / barang"
                                    value="<?php echo h($detail['keterangan']); ?>"><input type="number" name="jumlah[]" min="1"
                                    step="1" required value="<?php echo (int) $detail['jumlah']; ?>"><input type="number"
                                    name="harga_satuan[]" min="0" step="1" required
                                    value="<?php echo (int) $detail['harga_satuan']; ?>"><button class="btn secondary remove"
                                    type="button">Hapus</button></div><?php endforeach; ?>
                        <?php if (!$details): ?>
                            <div class="fields"><input name="keterangan[]" required maxlength="255"
                                    placeholder="Keterangan / jasa / barang"><input type="number" name="jumlah[]" min="1"
                                    step="1" required placeholder="Jumlah"><input type="number" name="harga_satuan[]" min="0"
                                    step="1" required placeholder="Harga satuan"><button class="btn secondary remove"
                                    type="button">Hapus</button></div><?php endif; ?>
                    </div>
                    <p><button class="btn secondary" type="button" id="add-row">+ Tambah rincian</button> <button
                            class="btn" type="submit">Simpan transaksi</button> <a class="btn secondary" target="_blank"
                            rel="noopener"
                            href="tanda_terima_servis.php?nomor=<?php echo urlencode($service['nomor_servis']); ?>">Cetak
                            Tanda Terima</a><?php if (isset($_GET['saved']) || count($details) > 0): ?> <a class="btn print"
                                target="_blank" rel="noopener"
                                href="nota_servis.php?nomor=<?php echo urlencode($service['nomor_servis']); ?>">Cetak Nota
                                Servis</a><?php endif; ?></p>
                    <p class="total">Total saat ini: Rp <span id="sum">0</span></p>
                </form>
            </section>
        <?php endif; ?>

        <section class="card">
            <h2>Daftar servis</h2>
            <form class="bar" method="get"><input name="q" value="<?php echo h($q); ?>"
                    placeholder="Cari nomor servis, pelanggan, barang"><button class="btn">Cari</button></form>
            <div class="scroll">
                <table>
                    <thead>
                        <tr>
                            <th>No. Servis</th>
                            <th>Pelanggan</th>
                            <th>Barang</th>
                            <th>Foto</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($rows)): ?>
                            <tr>
                                <td><?php echo h($row['nomor_servis']); ?></td>
                                <td><?php echo h($row['nama']); ?></td>
                                <td><?php echo h($row['nama_barang']); ?></td>
                                <td><?php if (!empty($row['foto_barang'])):
                                    $foto = 'uploads/barang/' . rawurlencode($row['foto_barang']); ?><button
                                            type="button" class="photo-preview" data-preview-src="<?php echo h($foto); ?>"
                                            data-preview-alt="Foto <?php echo h($row['nama_barang']); ?>"
                                            aria-label="Lihat foto <?php echo h($row['nama_barang']); ?>"><img
                                                src="<?php echo h($foto); ?>"
                                                alt="Foto <?php echo h($row['nama_barang']); ?>"></button><?php else: ?>&mdash;<?php endif; ?>
                                </td>
                                <td><?php echo h($row['status_servis']); ?></td>
                                <td>Rp <?php echo number_format((int) $row['total'], 0, ',', '.'); ?></td>
                                <td><a class="btn" href="transaksi.php?id=<?php echo (int) $row['id_servis']; ?>">Buka
                                        kasir</a></td>
                            </tr><?php endwhile; ?>
                        <?php if (!$total): ?>
                            <tr>
                                <td colspan="7">Tidak ada servis ditemukan.</td>
                            </tr><?php endif; ?>
                    </tbody>
                </table>
            </div><?php renderPagination($total, $page, ['q' => $q]); ?>
        </section>
    </main>

    <div class="modal" id="modal">
        <div class="dialog"><button class="close" id="close-modal">Tutup</button>
            <h2>Pilih servis</h2>
            <p>Pilih nama pelanggan dan barang yang akan dibuat transaksinya.</p>
            <label for="cari-servis-transaksi">Cari nama pelanggan, barang, atau nomor servis</label>
            <div class="customer-search">
                <input type="search" id="cari-servis-transaksi" autocomplete="off" placeholder="Ketik minimal 2 karakter...">
                <div id="hasil-servis-transaksi" class="customer-results" role="listbox" hidden></div>
            </div>
        </div>
    </div>
    <script>
        const cariServisTransaksi = document.getElementById('cari-servis-transaksi');
        const hasilServisTransaksi = document.getElementById('hasil-servis-transaksi');
        let timerServisTransaksi, abortServisTransaksi;
        cariServisTransaksi.addEventListener('input', () => {
            clearTimeout(timerServisTransaksi); if (abortServisTransaksi) abortServisTransaksi.abort();
            const query = cariServisTransaksi.value.trim(); hasilServisTransaksi.replaceChildren(); hasilServisTransaksi.hidden = true;
            if (query.length < 2) return;
            timerServisTransaksi = setTimeout(async () => {
                abortServisTransaksi = new AbortController();
                try {
                    const response = await fetch('cari_servis.php?q=' + encodeURIComponent(query), { signal: abortServisTransaksi.signal, headers: { Accept: 'application/json' } });
                    const items = await response.json();
                    items.slice(0, 5).forEach(item => {
                        const link = document.createElement('a'); link.className = 'customer-result'; link.href = 'transaksi.php?id=' + encodeURIComponent(item.id); link.setAttribute('role', 'option');
                        const name = document.createElement('strong'); name.textContent = item.nama + ' · ' + item.nama_barang;
                        const meta = document.createElement('span'); meta.textContent = item.nomor_servis + ' · ' + item.status_servis;
                        link.append(name, meta); hasilServisTransaksi.append(link);
                    });
                    if (!items.length) { const empty = document.createElement('div'); empty.className = 'customer-empty'; empty.textContent = 'Tidak ada servis yang cocok.'; hasilServisTransaksi.append(empty); }
                    hasilServisTransaksi.hidden = false;
                } catch (error) { if (error.name !== 'AbortError') hasilServisTransaksi.hidden = true; }
            }, 250);
        });        const modal = document.getElementById('modal'); document.getElementById('open-modal').onclick = () => modal.classList.add('open'); document.getElementById('close-modal').onclick = () => modal.classList.remove('open'); modal.addEventListener('click', event => { if (event.target === modal) modal.classList.remove('open') });
        const list = document.getElementById('detail-list'); function updateTotal() { const sum = document.getElementById('sum'); if (!list || !sum) return; let value = 0; list.querySelectorAll('.fields').forEach(row => value += (Number(row.querySelector('[name="jumlah[]"]').value) || 0) * (Number(row.querySelector('[name="harga_satuan[]"]').value) || 0)); sum.textContent = new Intl.NumberFormat('id-ID').format(value) }
        if (list) { list.addEventListener('input', updateTotal); list.addEventListener('click', event => { if (event.target.classList.contains('remove') && list.children.length > 1) { event.target.parentElement.remove(); updateTotal() } }); document.getElementById('add-row').onclick = () => { const row = document.createElement('div'); row.className = 'fields'; row.innerHTML = '<input name="keterangan[]" required maxlength="255" placeholder="Keterangan / jasa / barang"><input type="number" name="jumlah[]" min="1" step="1" required placeholder="Jumlah"><input type="number" name="harga_satuan[]" min="0" step="1" required placeholder="Harga satuan"><button class="btn secondary remove" type="button">Hapus</button>'; list.appendChild(row) } } updateTotal();
    </script>
    <script src="js/image-preview.js" defer></script>
</body>

</html>