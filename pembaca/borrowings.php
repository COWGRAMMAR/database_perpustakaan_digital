<?php
require_once '../auth/check_session.php';
requireRole(['Pembaca']);
require_once '../config/database.php';

$pageTitle = 'Peminjaman Saya';
$userId = $_SESSION['user_id'];

$borrowings = $conn->query("
    SELECT b.*, bk.title
    FROM borrowings b
    JOIN books bk ON b.book_id = bk.id
    WHERE b.user_id = $userId
    ORDER BY b.id DESC
");

require_once '../includes/header.php';
?>

<div class="p-6">
    <h1 class="text-xl font-semibold text-gray-800 mb-4">Peminjaman Saya</h1>

    <div class="bg-white p-4 rounded shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-200">
                    <th class="py-2 pr-2">Buku</th>
                    <th class="py-2 pr-2">Pinjam</th>
                    <th class="py-2 pr-2">Jatuh Tempo</th>
                    <th class="py-2 pr-2">Kembali</th>
                    <th class="py-2 pr-2">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($borrowings && $borrowings->num_rows > 0): ?>
                    <?php while ($b = $borrowings->fetch_assoc()): ?>
                        <tr class="border-b border-gray-100">
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['title']) ?></td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['borrow_date']) ?></td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['due_date']) ?></td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['return_date'] ?? '-') ?></td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['status']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="py-4 text-center text-gray-400">Belum pernah meminjam buku.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>