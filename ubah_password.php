<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once 'app_helper.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifyCsrfToken()) {
    http_response_code(403);
    exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
}
require_once 'koneksi.php';
$pesan = '';
$error = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = (string) ($_POST['password_lama'] ?? '');
    $new = (string) ($_POST['password_baru'] ?? '');
    $confirm = (string) ($_POST['konfirmasi_password'] ?? '');
    $stmt = mysqli_prepare($koneksi, 'SELECT password FROM users WHERE id_user = ? AND aktif = 1 LIMIT 1');
    $id = (int) $_SESSION['id_user'];
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$user || !password_verify($current, $user['password'])) {
        $pesan = 'Password saat ini tidak benar.';
        $error = true;
    } elseif (strlen($new) < 12) {
        $pesan = 'Password baru harus memiliki minimal 12 karakter.';
        $error = true;
    } elseif ($new !== $confirm) {
        $pesan = 'Konfirmasi password baru tidak sama.';
        $error = true;
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $update = mysqli_prepare($koneksi, 'UPDATE users SET password = ? WHERE id_user = ?');
        mysqli_stmt_bind_param($update, 'si', $hash, $id);
        if (mysqli_stmt_execute($update)) {
            session_regenerate_id(true);
            $pesan = 'Password berhasil diganti.';
        } else {
            $pesan = 'Password gagal diganti. Coba kembali.';
            $error = true;
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ganti Password - Avero Service</title>
    <link rel="stylesheet" href="style/login.css?v=2">
    <link rel="stylesheet" href="style/responsive.css?v=3">
</head>
<body>
    <?php include 'navigasi.php'; ?>
    <main class="container">
        <section class="card">
            <h1>Ganti Password</h1>
            <p class="subtitle">Gunakan password unik minimal 12 karakter.</p>
            <?php if ($pesan): ?><p class="<?php echo $error ? 'error' : 'success'; ?>"><?php echo h($pesan); ?></p><?php endif; ?>
            <form method="post">
                <?php csrfField(); ?>
                <label for="password_lama">Password saat ini</label>
                <input id="password_lama" type="password" name="password_lama" autocomplete="current-password" required>
                <label for="password_baru">Password baru</label>
                <input id="password_baru" type="password" name="password_baru" minlength="12" autocomplete="new-password" required>
                <label for="konfirmasi_password">Ulangi password baru</label>
                <input id="konfirmasi_password" type="password" name="konfirmasi_password" minlength="12" autocomplete="new-password" required>
                <button type="submit">Simpan Password</button>
                <p><a href="index.php">Kembali ke dashboard</a></p>
            </form>
        </section>
    </main>
</body>
</html>
