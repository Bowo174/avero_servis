<?php

session_start();
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

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 400px;
            margin: 60px auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
        }

        h1 {
            text-align: center;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 16px;
        }

        button {
            width: 100%;
            margin-top: 25px;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

    </style>

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