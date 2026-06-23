<?php
require_once '../auth/check_session.php';
requireRole(['Staff']);
require_once '../config/database.php';

$pageTitle = 'Audit System (Aktivitas Staff)';

// Hanya tampilkan log dari user yang role-nya Staff (bukan Pembaca)
$sql = "SELECT al.*, u.username
        FROM audit_logs al
        JOIN users u ON al.user_id = u.id
        JOIN user_roles ur ON ur.user_id = u.id
        JOIN roles r ON r.id = ur.role_id
        WHERE r.role_name = 'Staff'
        ORDER BY al.created_at DESC
        LIMIT 200";
$logs = $conn->query($sql);

require_once '../includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Audit System — Aktivitas Staff</h1>

    <table class="w-full bg-white shadow rounded text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Waktu</th>
                <th class="p-2 text-left">Staff</th>
                <th class="p-2 text-left">Aksi</th>
                <th class="p-2 text-left">Tabel</th>
                <th class="p-2 text-left">Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($r = $logs->fetch_assoc()): ?>
            <tr class="border-t">
                <td class="p-2"><?= $r['created_at'] ?></td>
                <td class="p-2"><?= htmlspecialchars($r['username']) ?></td>
                <td class="p-2"><?= htmlspecialchars($r['action']) ?></td>
                <td class="p-2"><?= htmlspecialchars($r['table_name']) ?></td>
                <td class="p-2"><?= htmlspecialchars($r['description']) ?></td>
            </tr>
            <?php endwhile; ?>
            <?php if ($logs->num_rows === 0): ?>
            <tr><td colspan="5" class="p-4 text-center text-gray-500">Belum ada aktivitas staff.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>