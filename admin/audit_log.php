<?php
require_once '../auth/check_session.php';
requireRole(['Admin']);
require_once '../config/database.php';

$pageTitle = 'Audit Log';

$filter_table = $_GET['table'] ?? '';
$where = $filter_table ? "WHERE al.table_name = '" . $conn->real_escape_string($filter_table) . "'" : '';

$sql = "SELECT al.*, u.username
        FROM audit_logs al
        LEFT JOIN users u ON al.user_id = u.id
        $where
        ORDER BY al.created_at DESC
        LIMIT 200";
$logs = $conn->query($sql);

require_once '../includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Audit Log (Semua Aktivitas)</h1>

    <div class="mb-4 flex gap-2">
        <a href="audit_log.php" class="px-3 py-1 rounded <?= !$filter_table?'bg-blue-600 text-white':'bg-gray-200' ?>">Semua</a>
        <a href="audit_log.php?table=borrowings" class="px-3 py-1 rounded <?= $filter_table==='borrowings'?'bg-blue-600 text-white':'bg-gray-200' ?>">Peminjaman</a>
        <a href="audit_log.php?table=fines" class="px-3 py-1 rounded <?= $filter_table==='fines'?'bg-blue-600 text-white':'bg-gray-200' ?>">Denda</a>
    </div>

    <table class="w-full bg-white shadow rounded text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Waktu</th>
                <th class="p-2 text-left">User</th>
                <th class="p-2 text-left">Aksi</th>
                <th class="p-2 text-left">Tabel</th>
                <th class="p-2 text-left">Deskripsi</th>
                <th class="p-2 text-left">IP</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($r = $logs->fetch_assoc()): ?>
            <tr class="border-t">
                <td class="p-2"><?= $r['created_at'] ?></td>
                <td class="p-2"><?= htmlspecialchars($r['username'] ?? '-') ?></td>
                <td class="p-2"><?= htmlspecialchars($r['action']) ?></td>
                <td class="p-2"><?= htmlspecialchars($r['table_name']) ?></td>
                <td class="p-2"><?= htmlspecialchars($r['description']) ?></td>
                <td class="p-2"><?= htmlspecialchars($r['ip_address']) ?></td>
            </tr>
            <?php endwhile; ?>
            <?php if ($logs->num_rows === 0): ?>
            <tr><td colspan="6" class="p-4 text-center text-gray-500">Belum ada aktivitas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>