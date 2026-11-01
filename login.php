<?php

require_once __DIR__ . '/session_bootstrap.php';
require_once 'app_helper.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifyCsrfToken()) {
    http_response_code(403);
    exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
}
require_once "koneksi.php";

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    $sql = "SELECT id_user, nama, username, password, role, aktif
            FROM users
            WHERE username = ?
            LIMIT 1";

    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($hasil);

    if (
        $user &&
        $user["aktif"] == 1 &&
        password_verify($password, $user["password"])
    ) {

        session_regenerate_id(true);

        $_SESSION["id_user"] = $user["id_user"];
        $_SESSION["nama"] = $user["nama"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["role"] = $user["role"];

        header("Location: index.php");
        exit;

    } else {

        $pesan = "Username atau password salah.";
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - Avero Komputer</title>

    <link rel="stylesheet" href="style/login.css?v=2">

<link rel="stylesheet" href="style/responsive.css?v=3">
</head>

<body>

<div class="container">

    <div class="card">

        <h1>AVERO KOMPUTER</h1>

        <div class="subtitle">
            Login Admin
        </div>

        <?php if ($pesan != "") { ?>

            <div class="error">
                <?php echo htmlspecialchars($pesan); ?>
            </div>

        <?php } ?>

        <form method="POST">
            <?php csrfField(); ?>

            <label>Username</label>

            <input
                type="text"
                name="username"
                required
                autocomplete="username"
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                required
                autocomplete="current-password"
            >

            <button type="submit">
                Login
            </button>

        </form>

    </div>

</div>

</body>

</html>
