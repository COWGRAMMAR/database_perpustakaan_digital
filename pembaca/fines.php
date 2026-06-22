<?php
require_once '../auth/check_session.php';
requireRole(['Pembaca']);
require_once '../config/database.php';

$pageTitle = 'Denda & Pembayaran Saya';
$user_id = $_SESSION['user_id'];

$sql = "SELECT f.id AS fine_id, f.amount, f.fine_status,
               b.borrow_date, b.due_date, b.return_date,
               bk.title AS judul_buku,
               p.payment_method, p.payment_date
        FROM fines f
        JOIN borrowings b ON f.borrowing_id = b.id
        JOIN books bk ON b.book_id = bk.id
        LEFT JOIN payments p ON p.fine_id = f.id
        WHERE b.user_id = ?
        ORDER BY f.fine_status ASC, b.due_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$fines = $stmt->get_result();

require_once '../includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Denda & Pembayaran Saya</h1>

    <table class="w-full bg-white shadow rounded text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Buku</th>
                <th class="p-2 text-left">Jatuh Tempo</th>
                <th class="p-2 text-left">Kembali</th>
                <th class="p-2 text-right">Denda</th>
                <th class="p-2 text-center">Status</th>
                <th class="p-2 text-left">Metode Bayar</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $fines->fetch_assoc()): ?>
            <tr class="border-t">
                <td class="p-2"><?= htmlspecialchars($row['judul_buku']) ?></td>
                <td class="p-2"><?= $row['due_date'] ?></td>
                <td class="p-2"><?= $row['return_date'] ?? '-' ?></td>
                <td class="p-2 text-right">Rp<?= number_format($row['amount'], 0, ',', '.') ?></td>
                <td class="p-2 text-center">
                    <span class="px-2 py-1 rounded text-xs <?= $row['fine_status']==='Lunas' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $row['fine_status'] ?>
                    </span>
                </td>
                <td class="p-2"><?= $row['payment_method'] ?? '-' ?></td>
            </tr>
            <?php endwhile; ?>
            <?php if ($fines->num_rows === 0): ?>
            <tr><td colspan="6" class="p-4 text-center text-gray-500">Belum ada denda.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$stmt->close();
require_once '../includes/footer.php';
?>