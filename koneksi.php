<?php
// Mulai session
session_start();

// ===== KONEKSI KE MYSQL =====
$host = "localhost";
$user = "root";      // default XAMPP/Laragon
$pass = "";          // default XAMPP kosong
$db   = "simkam";

$conn = mysqli_connect($host, $user, $pass, $db);

// Kalau gagal, tampilkan pesan
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Fungsi cek login
function harus_login() {
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }
}

// Fungsi singkat untuk escape output HTML
function e($teks) {
    return htmlspecialchars($teks);
}
?>