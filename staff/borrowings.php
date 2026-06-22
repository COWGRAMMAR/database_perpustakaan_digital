<?php
require_once '../auth/check_session.php';
requireRole(['Staff']);
require_once '../config/database.php';
require_once '../includes/audit_helper.php';

// ===== AUTO: proses keterlambatan setiap halaman ini dibuka =====
$conn->query("CALL sp_proses_keterlambatan()");
while ($conn->more_results() && $conn->next_result()) { /* flush hasil CALL */ }

$pageTitle = 'Manajemen Peminjaman';
$error = '';
$success = '';

// ===== PROSES PINJAM (CALL stored procedure asli: sp_pinjam_buku) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pinjam') {
    $user_id = (int) $_POST['user_id'];
    $book_id = (int) $_POST['book_id'];

    $stmt = $conn->prepare("CALL sp_pinjam_buku(?, ?)");
    $stmt->bind_param('ii', $user_id, $book_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $hasil = $row['hasil'] ?? 'Terjadi kesalahan tidak terduga.';

    if (str_contains($hasil, 'berhasil')) {
        logAudit($conn, $_SESSION['user_id'], 'PROSES_PINJAM', 'borrowings',
            "Staff memproses peminjaman buku ID $book_id untuk user ID $user_id");
    }

    if (stripos($hasil, 'berhasil') !== false) {
        $success = $hasil;
    } else {
        $error = $hasil;
    }

    $stmt->close();
    while ($conn->more_results() && $conn->next_result()) { /* flush hasil tambahan dari CALL */ }
}

// ===== PROSES KEMBALI =====
if (isset($_GET['return'])) {
    $id = (int) $_GET['return'];

    $row = $conn->query("SELECT due_date FROM borrowings WHERE id = $id")->fetch_assoc();
    if ($row) {
        $today = date('Y-m-d');
        $status = ($today > $row['due_date']) ? 'Terlambat' : 'Kembali';

        $stmt = $conn->prepare("UPDATE borrowings SET return_date = ?, status = ? WHERE id = ?");
        $stmt->bind_param('ssi', $today, $status, $id);
        if ($stmt->execute()) {
            $success = 'Buku berhasil diproses sebagai dikembalikan.' . ($status === 'Terlambat' ? ' (Terlambat — denda otomatis dibuat)' : '');
            logAudit($conn, $_SESSION['user_id'], 'PROSES_KEMBALI', 'borrowings',
                "Staff memproses pengembalian buku untuk borrowing ID $id");
        } else {
            $error = 'Gagal memproses pengembalian.';
        }
        $stmt->close();
    }
}

// ===== DROPDOWN: Pembaca aktif & buku =====
$members = $conn->query("
    SELECT mp.user_id, mp.full_name, mp.member_number
    FROM member_profiles mp
    JOIN users u ON mp.user_id = u.id
    WHERE u.is_active = 1
    ORDER BY mp.full_name
");
$books = $conn->query("SELECT id, title FROM books ORDER BY title");

// ===== FILTER LIST =====
$statusFilter = $_GET['status'] ?? '';
$statusClause = '';
if (in_array($statusFilter, ['Dipinjam', 'Kembali', 'Terlambat'])) {
    $statusClause = "AND b.status = '" . $conn->real_escape_string($statusFilter) . "'";
}

$borrowings = $conn->query("
    SELECT b.*, bk.title, mp.full_name, mp.member_number
    FROM borrowings b
    JOIN books bk ON b.book_id = bk.id
    JOIN member_profiles mp ON b.user_id = mp.user_id
    WHERE 1=1 $statusClause
    ORDER BY b.id DESC
");

require_once '../includes/header.php';
?>

<div class="p-6">
    <h1 class="text-xl font-semibold text-gray-800 mb-4">Manajemen Peminjaman</h1>

    <?php if ($error): ?>
        <div class="mb-4 px-4 py-2 bg-red-100 text-red-700 rounded text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded text-sm"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- FORM PINJAM -->
        <div class="lg:col-span-1 bg-white p-4 rounded shadow-sm border border-gray-100">
            <h2 class="font-medium text-gray-700 mb-3">Catat Peminjaman Baru</h2>
            <form method="POST" action="borrowings.php">
                <input type="hidden" name="action" value="pinjam">

                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">Pembaca</label>
                    <select name="user_id" required class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                        <option value="">-- Pilih Pembaca --</option>
                        <?php while ($m = $members->fetch_assoc()): ?>
                            <option value="<?= $m['user_id'] ?>">
                                <?= htmlspecialchars($m['full_name']) ?> (<?= htmlspecialchars($m['member_number']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">Buku</label>
                    <select name="book_id" required class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                        <option value="">-- Pilih Buku --</option>
                        <?php while ($bk = $books->fetch_assoc()): ?>
                            <option value="<?= $bk['id'] ?>"><?= htmlspecialchars($bk['title']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <p class="text-xs text-gray-400 mb-3">Due date otomatis +7 hari dari hari ini (via trigger).</p>

                <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-1.5 rounded hover:bg-indigo-700">
                    Catat Peminjaman
                </button>
            </form>
        </div>

        <!-- LIST -->
        <div class="lg:col-span-2 bg-white p-4 rounded shadow-sm border border-gray-100 overflow-x-auto">
            <div class="flex gap-2 mb-3">
                <a href="?status=" class="px-3 py-1 text-xs rounded <?= $statusFilter === '' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' ?>">Semua</a>
                <a href="?status=Dipinjam" class="px-3 py-1 text-xs rounded <?= $statusFilter === 'Dipinjam' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' ?>">Dipinjam</a>
                <a href="?status=Terlambat" class="px-3 py-1 text-xs rounded <?= $statusFilter === 'Terlambat' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' ?>">Terlambat</a>
                <a href="?status=Kembali" class="px-3 py-1 text-xs rounded <?= $statusFilter === 'Kembali' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' ?>">Kembali</a>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-200">
                        <th class="py-2 pr-2">Pembaca</th>
                        <th class="py-2 pr-2">Buku</th>
                        <th class="py-2 pr-2">Pinjam</th>
                        <th class="py-2 pr-2">Jatuh Tempo</th>
                        <th class="py-2 pr-2">Kembali</th>
                        <th class="py-2 pr-2">Status</th>
                        <th class="py-2 pr-2 text-right">Aksi</th>
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
                                <td class="py-2 pr-2">
                                    <?php
                                        $badge = $b['status'] === 'Dipinjam' ? 'bg-blue-100 text-blue-700'
                                            : ($b['status'] === 'Terlambat' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700');
                                    ?>
                                    <span class="px-2 py-0.5 rounded text-xs <?= $badge ?>"><?= htmlspecialchars($b['status']) ?></span>
                                </td>
                                <td class="py-2 pr-2 text-right">
                                    <?php if ($b['status'] !== 'Kembali' && $b['status'] !== 'Terlambat'): ?>
                                        <a href="?return=<?= $b['id'] ?>" onclick="return confirm('Proses pengembalian buku ini?')" class="text-indigo-600 hover:underline">Kembalikan</a>
                                    <?php elseif ($b['status'] === 'Terlambat' && !$b['return_date']): ?>
                                        <a href="?return=<?= $b['id'] ?>" onclick="return confirm('Proses pengembalian buku ini (terlambat)?')" class="text-red-600 hover:underline">Kembalikan</a>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="py-4 text-center text-gray-400">Belum ada data peminjaman.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>