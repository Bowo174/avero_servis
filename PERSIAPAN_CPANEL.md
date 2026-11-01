# Persiapan keamanan cPanel

Jangan membuka aplikasi ke publik sebelum menyelesaikan langkah berikut.

1. Buat database dan user MySQL melalui cPanel, lalu berikan user akses hanya ke database aplikasi.
2. Salin `config.local.php.example` menjadi `config.local.php`. Isi host, nama database, user, password, dan URL aplikasi yang benar. URL harus memakai HTTPS dan menyertakan subfolder bila aplikasi tidak dipasang di root domain.
3. Import `avero_servis.sql` melalui phpMyAdmin. Jangan membuka atau memindahkan dump SQL ke folder publik lain. Aturan `.htaccess` di project memblokir akses web ke file SQL.
4. Aktifkan sertifikat SSL sebelum login dan gunakan URL HTTPS. Service worker/PWA dan cookie sesi lebih aman melalui HTTPS.
5. Pastikan folder `uploads`, `storage`, dan `storage/backups` dapat ditulis oleh PHP. Aturan `.htaccess` di dalam folder tetap harus ikut terunggah.
6. Login menggunakan akun admin yang telah disiapkan, lalu ganti password melalui menu **Ganti Password** sebelum aplikasi diumumkan. Bila membuat akun tambahan melalui `buat_admin.php`, jalankan hanya lewat terminal PHP dan set `AVERO_ADMIN_USER` serta `AVERO_ADMIN_PASSWORD` yang kuat; script menolak akses web.
7. Hapus `tes_koneksi.php` dan `buat_admin.php` setelah setup selesai. Keduanya sudah dibatasi agar tidak dapat dijalankan melalui browser.
8. Jangan mengaktifkan webhook WhatsApp sampai `whatsapp_webhook_verify_token` dan `whatsapp_app_secret` diisi dengan rahasia milik aplikasi Meta. Endpoint memverifikasi signature dan tidak menyimpan isi payload.
9. Untuk tes QR lewat handphone di jaringan lokal, isi `app_base_url` dengan alamat IP komputer yang dapat dijangkau handphone, contohnya `http://192.168.1.10/avero_servis`. Di hosting, gunakan URL HTTPS domain cPanel.

Nilai database produksi tidak disimpan di `koneksi.php`. File rahasia `config.local.php` sengaja tidak dilacak Git dan diblokir dari akses browser.
