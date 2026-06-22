<?php
require_once '../auth/check_session.php';
requireRole(['Staff']);
require_once '../config/database.php';
require_once '../includes/audit_helper.php';

$pageTitle = 'Denda & Pembayaran';

// Proses pembayaran denda
$hasil = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bayar'])) {
    $fine_id = (int)$_POST['fine_id'];
    $payment_method = $_POST['payment_method'];

    $stmt = $conn->prepare("CALL sp_bayar_denda(?, ?)");
    $stmt->bind_param('is', $fine_id, $payment_method);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $hasil = $row['hasil'] ?? 'Terjadi kesalahan.';

    // setelah CALL sp_bayar_denda dan $hasil didapat, tambahkan:
    if (str_contains($hasil, 'berhasil')) {
        logAudit($conn, $_SESSION['user_id'], 'VERIFIKASI_BAYAR', 'fines',
            "Staff memverifikasi pembayaran denda ID $fine_id via $payment_method");
    }

    $stmt->close();
    while ($conn->more_results() && $conn->next_result()) { /* flush */ }
}

// Filter status
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
               u.id AS user_id
        FROM fines f
        JOIN borrowings b ON f.borrowing_id = b.id
        JOIN books bk ON b.book_id = bk.id
        JOIN users u ON b.user_id = u.id
        LEFT JOIN member_profiles mp ON mp.user_id = u.id
        $where
        ORDER BY f.fine_status ASC, b.due_date ASC";
$fines = $conn->query($sql);

require_once '../includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Denda & Pembayaran</h1>

    <?php if ($hasil): ?>
        <div class="mb-4 p-3 rounded <?= str_contains($hasil, 'berhasil') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= htmlspecialchars($hasil) ?>
        </div>
    <?php endif; ?>

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
                <th class="p-2 text-left">Kembali</th>
                <th class="p-2 text-right">Denda</th>
                <th class="p-2 text-center">Status</th>
                <th class="p-2 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $fines->fetch_assoc()): ?>
            <tr class="border-t">
                <td class="p-2"><?= htmlspecialchars($row['full_name'] ?? '-') ?><br>
                    <span class="text-xs text-gray-500"><?= htmlspecialchars($row['member_number'] ?? '') ?></span>
                </td>
                <td class="p-2"><?= htmlspecialchars($row['judul_buku']) ?></td>
                <td class="p-2"><?= $row['due_date'] ?></td>
                <td class="p-2"><?= $row['return_date'] ?? '-' ?></td>
                <td class="p-2 text-right">Rp<?= number_format($row['amount'], 0, ',', '.') ?></td>
                <td class="p-2 text-center">
                    <span class="px-2 py-1 rounded text-xs <?= $row['fine_status']==='Lunas' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $row['fine_status'] ?>
                    </span>
                </td>
                <td class="p-2 text-center">
                    <?php if ($row['fine_status'] === 'Belum bayar'): ?>
                    <button onclick="document.getElementById('modal-<?= $row['fine_id'] ?>').classList.remove('hidden')"
                        class="bg-blue-600 text-white px-2 py-1 rounded text-xs">Bayar</button>

                    <div id="modal-<?= $row['fine_id'] ?>" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                        <div class="bg-white p-5 rounded shadow w-80">
                            <h3 class="font-bold mb-3">Bayar Denda</h3>
                            <form method="POST">
                                <input type="hidden" name="fine_id" value="<?= $row['fine_id'] ?>">
                                <label class="block text-sm mb-1">Metode Pembayaran</label>
                                <select name="payment_method" class="w-full border rounded p-2 mb-3" required>
                                    <option value="E-Wallet">E-Wallet</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                </select>
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="document.getElementById('modal-<?= $row['fine_id'] ?>').classList.add('hidden')"
                                        class="px-3 py-1 rounded bg-gray-200">Batal</button>
                                    <button type="submit" name="bayar" value="1"
                                        onclick="return confirm('Konfirmasi pembayaran denda ini?')"
                                        class="px-3 py-1 rounded bg-blue-600 text-white">Konfirmasi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>