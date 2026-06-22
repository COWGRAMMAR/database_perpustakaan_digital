<?php
require_once '../auth/check_session.php';
requireRole(['Pembaca']);
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];
$book_id = (int) ($_GET['book_id'] ?? 0);
$file_id = (int) ($_GET['file_id'] ?? 0);

// Ambil file_url dari book_files, pastikan memang milik book_id ini
$stmt = $conn->prepare("SELECT file_url FROM book_files WHERE id = ? AND book_id = ?");
$stmt->bind_param('ii', $file_id, $book_id);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$file) {
    die('File tidak ditemukan.');
}

// Cek apakah sudah ada baris reading_history untuk user+buku ini (1 baris per buku)
$cek = $conn->prepare("SELECT id, last_page_read FROM reading_history WHERE user_id = ? AND book_id = ?");
$cek->bind_param('ii', $user_id, $book_id);
$cek->execute();
$existing = $cek->get_result()->fetch_assoc();
$cek->close();

if ($existing) {
    // Update last_accessed saja, last_page_read tetap (belum ada tracking halaman riil)
    $upd = $conn->prepare("UPDATE reading_history SET last_accessed = NOW() WHERE id = ?");
    $upd->bind_param('i', $existing['id']);
    $upd->execute();
    $upd->close();
} else {
    // Baris pertama: mulai dari halaman 1
    $ins = $conn->prepare("INSERT INTO reading_history (user_id, book_id, last_page_read, last_accessed) VALUES (?, ?, 1, NOW())");
    $ins->bind_param('ii', $user_id, $book_id);
    $ins->execute();
    $ins->close();
}

// Redirect ke file aslinya
header('Location: ' . $file['file_url']);
exit;