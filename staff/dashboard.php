<?php
// staff/dashboard.php
require_once '../auth/check_session.php';
requireRole(['Staff']);

$pageTitle = 'Dashboard Staff';
require_once '../includes/header.php';
?>

    <div class="bg-white rounded-xl shadow p-6 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Dashboard Staff</h1>
        <p class="text-gray-600">Selamat datang di panel Staff.</p>
    </div>

<?php require_once '../includes/footer.php'; ?>
