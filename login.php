<?php
require 'koneksi.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query cari user
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $hasil = mysqli_query($conn, $query);
    $user  = mysqli_fetch_assoc($hasil);

    if ($user) {
        $_SESSION['user'] = $user;
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login SIMKAM</title>
</head>
<body>
    <h2>Login SIMKAM</h2>

    <?php if ($error != "") { ?>
        <p style="color:red"><?= $error ?></p>
    <?php } ?>

    <form method="post">
        <p>Username: <input type="text" name="username" required></p>
        <p>Password: <input type="password" name="password" required></p>
        <button type="submit">Masuk</button>
    </form>

    <hr>
    <small>
        Akun demo:<br>
        admin / 123<br>
        dosen1 / 123<br>
        mhs1 / 123
    </small>
</body>
</html>