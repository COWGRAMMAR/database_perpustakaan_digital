<?php
// ============================================================
// Konfigurasi Koneksi Database
// Proyek: Perpustakaan Digital
// DBMS: MySQL (XAMPP)
// ============================================================

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "perpustakaan_digital";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset biar gak aneh kalo pake bahasa Indonesia
mysqli_set_charset($conn, "utf8");
?>
