## Alur bisnis yang saya temukan

1. **Admin masuk** lewat [login.php](D:/laragon/www/avero_servis/login.php). Password diverifikasi dengan `password_verify()`, lalu identitas dan role disimpan di session.
2. **Servis baru dicatat** lewat [tambah_servis.php](D:/laragon/www/avero_servis/tambah_servis.php). Formulir menyimpan data pelanggan, barang, servis, lalu riwayat status awal “Diterima”.
3. **Admin memantau servis** di [data_servis.php](D:/laragon/www/avero_servis/data_servis.php). Halaman ini menggabungkan data dari tabel servis, pelanggan, dan barang.
4. **Status servis diperbarui** lewat [ubah_status.php](D:/laragon/www/avero_servis/ubah_status.php). Perubahan status juga dirancang untuk menambah riwayat dan antrean pesan WhatsApp. Saat status menjadi “Selesai”, form meminta rincian biaya.
5. **Nota dan tanda terima dicetak** lewat [nota_servis.php](D:/laragon/www/avero_servis/nota_servis.php) dan [tanda_terima_servis.php](D:/laragon/www/avero_servis/tanda_terima_servis.php).
6. **Pelanggan bisa memantau servis** lewat [status_servis.php](D:/laragon/www/avero_servis/status_servis.php), menggunakan token QR untuk melihat status dan riwayat.
7. Data pelanggan, barang, dan informasi toko ditampilkan atau diatur lewat `pelanggan.php`, `barang.php`, dan [pengaturan.php](D:/laragon/www/avero_servis/pengaturan.php).

Dari query yang terlihat, model datanya kira-kira seperti ini:

```text
pelanggan ──< barang
     │           │
     └──── servis ┘
              │
       ├── riwayat_status
       ├── detail_servis
       └── notifikasi
```

Ini saya simpulkan dari relasi yang dipakai di query. File skema database tidak ditemukan, jadi detail kolom, constraint, dan indeksnya belum bisa dipastikan.

## Bagian yang sudah baik

- Login memakai **prepared statement**, dan password akun disimpan sebagai hash.
- Banyak output data menggunakan `htmlspecialchars()`, yang membantu mencegah HTML berbahaya tampil sebagai markup.
- Token pelacakan QR dibuat dengan `random_bytes()`, lebih layak daripada token berurutan.
- Riwayat status dan rincian biaya sudah dipisahkan ke tabel masing-masing. Ini lebih mudah dirawat daripada menaruh semua informasi di satu kolom servis.

## Hal yang perlu diperhatikan

**Konsistensi penyimpanan servis.** Di `tambah_servis.php`, empat operasi insert dilakukan berturut-turut, tetapi tidak dibungkus transaksi. Jika insert barang atau servis gagal setelah pelanggan berhasil disimpan, database bisa menyisakan data yang tidak lengkap. Operasi ini sebaiknya memakai transaksi: semua berhasil di-commit, atau semua di-rollback.

**Nomor servis berpotensi bentrok.** Nomor dibuat dari jumlah baris servis ditambah satu. Dua pendaftaran bersamaan bisa memperoleh nomor yang sama; penghapusan data juga dapat membuat urutan nomor dipakai kembali. Nomor transaksi sebaiknya punya aturan unik di database dan dibuat dengan mekanisme yang aman terhadap permintaan bersamaan.

**Pembaruan status memakai ID dari formulir.** `ubah_status.php` menerima `id_servis` dari POST untuk mengubah data, sementara halaman awal menemukan servis berdasarkan nomor. ID dari browser tetap harus dianggap input yang bisa diubah. Server sebaiknya mengambil kembali ID servis berdasarkan nomor yang diverifikasi, lalu memastikan semua perubahan terkait ke servis yang sama.

**Perubahan status dan rincian biaya belum atomik.** Ketika status menjadi “Selesai”, kode memperbarui status, menghapus rincian lama, lalu menyimpan rincian baru. Jika penyimpanan rincian gagal di tengah proses, status bisa sudah “Selesai” tetapi datanya tidak utuh. Ini juga perlu transaksi.

**Pemeriksaan akses tidak konsisten.** Beberapa halaman admin memeriksa session, tetapi halaman edit pelanggan dan halaman nota terlihat dapat diakses tanpa pemeriksaan login. Akses nota berdasarkan nomor servis juga patut ditinjau karena nomor bukan rahasia yang kuat. Halaman pelacakan publik memiliki token acak, yang merupakan pendekatan lebih baik untuk akses pelanggan.

**Upload logo hanya memeriksa ekstensi.** [pengaturan.php](D:/laragon/www/avero_servis/pengaturan.php) menerima file berdasarkan nama ekstensi. Untuk upload yang lebih aman, server perlu memeriksa MIME/jenis file sebenarnya dan ukuran file, serta membuat nama file sendiri.

Ada pula beberapa petunjuk masalah kualitas yang lebih kecil: [webhook.php](D:/laragon/www/avero_servis/webhook.php) memakai `_DIR_` alih-alih konstanta PHP `__DIR__`; [data_servis2.php](D:/laragon/www/avero_servis/data_servis2.php) mengarah ke nama halaman tanda terima yang berbeda dari file yang ada; `isi_nota.php` kosong; dan beberapa teks ikon tampak rusak encoding-nya.

## Cara membaca kode PHP di proyek ini

Contoh pola yang berulang:

```php
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($hasil);
```

Urutannya adalah **siapkan query → ikat nilai input → jalankan → ambil hasil**. Tanda `?` di SQL menandai nilai yang akan diikat. Ini mencegah input seperti username disisipkan mentah ke dalam teks query.

Perbedaan penting:

- **Prepared statement** membantu mengamankan nilai input di query.
- **`htmlspecialchars()`** membantu mengamankan teks saat ditampilkan ke HTML.
- Keduanya menyelesaikan masalah yang berbeda; satu tidak menggantikan yang lain.

Sebagai tutor, saya akan membahas kode dengan urutan **tujuan bisnis → alur data → query → alur kontrol PHP → risiko dan perbaikan**, supaya kita memahami alasan di balik kode, bukan sekadar menghafal sintaks.

Catatan lingkungan: saya menemukan kredensial database langsung di [koneksi.php](D:/laragon/www/avero_servis/koneksi.php), dan akun awal berkredensial tetap ditulis di [buat_admin.php](D:/laragon/www/avero_servis/buat_admin.php). Hindari memakai kredensial tersebut di server publik, dan jangan biarkan script pembuatan admin dapat dijalankan sembarang orang.

Untuk sesi belajar berikutnya, titik awal yang paling bagus adalah membedah **`tambah_servis.php`**: kita bisa mengikuti satu pengiriman form dari `$_POST`, masuk ke database, lalu memahami kenapa transaksi penting.