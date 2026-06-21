<?php
// includes/header.php
// Include file ini di halaman yang SUDAH require_once 'auth/check_session.php'
// dan sudah punya session aktif ($_SESSION['username'], $_SESSION['role'])
//
// Cara pakai dari dalam folder admin/staff/pembaca:
//   require_once '../includes/header.php';
//
// Variabel opsional $pageTitle bisa di-set sebelum include, untuk judul halaman.

$base = getBasePath();
$pageTitle = $pageTitle ?? 'Perpustakaan Digital';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Perpustakaan Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <nav class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <span class="font-bold text-gray-800">📚 Perpustakaan Digital</span>
            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                <?= htmlspecialchars($_SESSION['role']) ?>
            </span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-600">
                Halo, <span class="font-medium"><?= htmlspecialchars($_SESSION['username']) ?></span>
            </span>
            <a href="<?= $base ?>logout.php"
               class="text-sm bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1.5 rounded-md transition">
                Logout
            </a>
        </div>
    </nav>

    <div class="flex">
        <?php require_once __DIR__ . '/sidebar.php'; ?>
        <main class="flex-1 p-6">
            <div class="p-6">
