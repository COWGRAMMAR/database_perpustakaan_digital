<?php
require_once '../auth/check_session.php';
requireRole(['Admin']);
require_once '../config/database.php';

$pageTitle = 'Peminjaman (Monitoring)';

$borrowings = $conn->query("
    SELECT b.*, bk.title, mp.full_name, mp.member_number
    FROM borrowings b
    JOIN books bk ON b.book_id = bk.id
    JOIN member_profiles mp ON b.user_id = mp.user_id
    ORDER BY b.id DESC
");

require_once '../includes/header.php';
?>

<div class="p-6">
    <h1 class="text-xl font-semibold text-gray-800 mb-1">Peminjaman</h1>
    <p class="text-sm text-gray-500 mb-4">Mode monitoring — input & proses pengembalian dilakukan oleh Staff.</p>

    <div class="bg-white p-4 rounded shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-200">
                    <th class="py-2 pr-2">Pembaca</th>
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
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['full_name']) ?></td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['title']) ?></td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['borrow_date']) ?></td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['due_date']) ?></td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['return_date'] ?? '-') ?></td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['status']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="py-4 text-center text-gray-400">Belum ada data.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>