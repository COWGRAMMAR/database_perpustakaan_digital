<?php
require_once '../auth/check_session.php';
requireRole(['Admin']);
require_once '../config/database.php';

$pageTitle = 'Denda & Pembayaran (Monitoring)';

$filter = $_GET['status'] ?? 'semua';
$where = '';
if ($filter === 'belum') {
    $where = "WHERE f.fine_status = 'Belum bayar'";
} elseif ($filter === 'lunas') {
    $where = "WHERE f.fine_status = 'Lunas'";
}

$sql = "SELECT f.id AS fine_id, f.amount, f.fine_status,
               b.borrow_date, b.due_date, b.return_date,
               bk.title AS judul_buku,
               mp.full_name, mp.member_number,
               p.payment_method, p.payment_date
        FROM fines f
        JOIN borrowings b ON f.borrowing_id = b.id
        JOIN books bk ON b.book_id = bk.id
        JOIN users u ON b.user_id = u.id
        LEFT JOIN member_profiles mp ON mp.user_id = u.id
        LEFT JOIN payments p ON p.fine_id = f.id
        $where
        ORDER BY f.fine_status ASC, b.due_date DESC";
$fines = $conn->query($sql);

require_once '../includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Denda & Pembayaran — Monitoring</h1>

    <div class="mb-4 flex gap-2">
        <a href="fines.php?status=semua" class="px-3 py-1 rounded <?= $filter==='semua'?'bg-blue-600 text-white':'bg-gray-200' ?>">Semua</a>
        <a href="fines.php?status=belum" class="px-3 py-1 rounded <?= $filter==='belum'?'bg-blue-600 text-white':'bg-gray-200' ?>">Belum Bayar</a>
        <a href="fines.php?status=lunas" class="px-3 py-1 rounded <?= $filter==='lunas'?'bg-blue-600 text-white':'bg-gray-200' ?>">Lunas</a>
    </div>

    <table class="w-full bg-white shadow rounded text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Member</th>
                <th class="p-2 text-left">Buku</th>
                <th class="p-2 text-left">Jatuh Tempo</th>
                <th class="p-2 text-right">Denda</th>
                <th class="p-2 text-center">Status</th>
                <th class="p-2 text-left">Metode Bayar</th>
                <th class="p-2 text-left">Tgl Bayar</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $fines->fetch_assoc()): ?>
            <tr class="border-t">
                <td class="p-2"><?= htmlspecialchars($row['full_name'] ?? '-') ?></td>
                <td class="p-2"><?= htmlspecialchars($row['judul_buku']) ?></td>
                <td class="p-2"><?= $row['due_date'] ?></td>
                <td class="p-2 text-right">Rp<?= number_format($row['amount'], 0, ',', '.') ?></td>
                <td class="p-2 text-center">
                    <span class="px-2 py-1 rounded text-xs <?= $row['fine_status']==='Lunas' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $row['fine_status'] ?>
                    </span>
                </td>
                <td class="p-2"><?= $row['payment_method'] ?? '-' ?></td>
                <td class="p-2"><?= $row['payment_date'] ?? '-' ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>