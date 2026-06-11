<?php
// ============================================================
// Konfigurasi Koneksi Database
// ============================================================

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "db_perpustakaan";

// Koneksi ke MySQL (pilih database nanti setelah CREATE DATABASE)
$conn = mysqli_connect($host, $user, $pass);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Buat database kalo belum ada
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $dbname";
mysqli_query($conn, $sql_create_db);

// Pilih database
mysqli_select_db($conn, $dbname);
?>
