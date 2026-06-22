<?php
require_once '../auth/check_session.php';
requireRole(['Pembaca']);
require_once '../config/database.php';

$pageTitle = 'Profil Saya';
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT u.username, u.email, u.created_at,
           mp.member_number, mp.full_name, mp.address, mp.phone_number, mp.membership_type
    FROM users u
    JOIN member_profiles mp ON mp.user_id = u.id
    WHERE u.id = ?
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

require_once '../includes/header.php';
?>

<div class="p-6 max-w-2xl mx-auto">
    <h1 class="text-xl font-semibold text-gray-800 mb-4">Profil Saya</h1>

    <?php if ($profile): ?>
        <div class="bg-white rounded shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($profile['full_name']) ?></h2>
                    <p class="text-xs text-gray-400">Anggota sejak <?= date('d M Y', strtotime($profile['created_at'])) ?></p>
                </div>
                <span class="px-3 py-1 rounded text-xs <?= $profile['membership_type'] === 'Premium' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' ?>">
                    <?= htmlspecialchars($profile['membership_type']) ?>
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400">Nomor Anggota</p>
                    <p class="text-gray-700"><?= htmlspecialchars($profile['member_number']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Username</p>
                    <p class="text-gray-700"><?= htmlspecialchars($profile['username']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Email</p>
                    <p class="text-gray-700"><?= htmlspecialchars($profile['email']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">No. Telepon</p>
                    <p class="text-gray-700"><?= htmlspecialchars($profile['phone_number'] ?: '-') ?></p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs text-gray-400">Alamat</p>
                    <p class="text-gray-700"><?= htmlspecialchars($profile['address'] ?: '-') ?></p>
                </div>
            </div>

            <p class="text-xs text-gray-400 mt-5">
                Untuk mengubah data profil, silakan hubungi Staff perpustakaan.
            </p>
        </div>
    <?php else: ?>
        <p class="text-gray-400 text-sm">Profil tidak ditemukan.</p>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>