<?php
// config/database.php
// Konfigurasi koneksi database menggunakan mysqli

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'perpustakaan_digital';

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$conn) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

// Set charset biar aman dari masalah encoding
mysqli_set_charset($conn, 'utf8mb4');
