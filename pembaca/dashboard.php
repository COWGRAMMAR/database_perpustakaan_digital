<?php
// pembaca/dashboard.php
require_once '../auth/check_session.php';
requireRole(['Pembaca']);

$pageTitle = 'Dashboard Pembaca';
require_once '../includes/header.php';
?>

    <div class="bg-white rounded-xl shadow p-6 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Dashboard Pembaca</h1>
        <p class="text-gray-600">Selamat datang, selamat membaca!</p>
    </div>

<?php require_once '../includes/footer.php'; ?>
