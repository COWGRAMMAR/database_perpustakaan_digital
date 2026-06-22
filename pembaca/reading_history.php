<?php
require_once '../auth/check_session.php';
requireRole(['Pembaca']);
require_once '../config/database.php';

$pageTitle = 'Riwayat Membaca';
$user_id = $_SESSION['user_id'];

$success = '';
$error = '';

// ===== Update manual halaman terakhir dibaca =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_page') {
    $book_id = (int) $_POST['book_id'];
    $last_page = (int) $_POST['last_page_read'];

    // Validasi: halaman tidak boleh melebihi total_pages buku
    $cekBuku = $conn->prepare("SELECT total_pages FROM books WHERE id = ?");
    $cekBuku->bind_param('i', $book_id);
    $cekBuku->execute();
    $bukuRow = $cekBuku->get_result()->fetch_assoc();
    $cekBuku->close();

    if (!$bukuRow) {
        $error = 'Buku tidak ditemukan.';
    } elseif ($last_page < 1 || $last_page > $bukuRow['total_pages']) {
        $error = "Halaman harus antara 1 - {$bukuRow['total_pages']}.";
    } else {
        $stmt = $conn->prepare("UPDATE reading_history SET last_page_read = ?, last_accessed = NOW() WHERE user_id = ? AND book_id = ?");
        $stmt->bind_param('iii', $last_page, $user_id, $book_id);
        if ($stmt->execute()) {
            $success = 'Progress membaca berhasil diperbarui.';
        } else {
            $error = 'Gagal memperbarui progress.';
        }
        $stmt->close();
    }
}

$stmt = $conn->prepare("
    SELECT rh.book_id, rh.last_page_read, rh.last_accessed,
           bk.title, bk.total_pages,
           (SELECT bf.id FROM book_files bf WHERE bf.book_id = bk.id LIMIT 1) AS file_id
    FROM reading_history rh
    JOIN books bk ON rh.book_id = bk.id
    WHERE rh.user_id = ?
    ORDER BY rh.last_accessed DESC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$history = $stmt->get_result();

require_once '../includes/header.php';
?>

<div class="p-6">
    <h1 class="text-xl font-semibold text-gray-800 mb-4">Riwayat Membaca</h1>

    <?php if ($error): ?>
        <div class="mb-4 px-4 py-2 bg-red-100 text-red-700 rounded text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded text-sm"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="bg-white rounded shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-200">
                    <th class="py-2 pr-2">Buku</th>
                    <th class="py-2 pr-2">Halaman Terakhir</th>
                    <th class="py-2 pr-2">Progress</th>
                    <th class="py-2 pr-2">Terakhir Diakses</th>
                    <th class="py-2 pr-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($history && $history->num_rows > 0): ?>
                    <?php while ($h = $history->fetch_assoc()): ?>
                        <?php
                            $percent = $h['total_pages'] > 0
                                ? min(100, round(($h['last_page_read'] / $h['total_pages']) * 100))
                                : 0;
                        ?>
                        <tr class="border-b border-gray-100">
                            <td class="py-2 pr-2 text-gray-700 font-medium"><?= htmlspecialchars($h['title']) ?></td>
                            <td class="py-2 pr-2 text-gray-700">
                                <?= $h['last_page_read'] ?> / <?= $h['total_pages'] ?>
                            </td>
                            <td class="py-2 pr-2">
                                <div class="w-32 bg-gray-100 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: <?= $percent ?>%"></div>
                                </div>
                                <span class="text-xs text-gray-400"><?= $percent ?>%</span>
                            </td>
                            <td class="py-2 pr-2 text-gray-700">
                                <?= date('d M Y, H:i', strtotime($h['last_accessed'])) ?>
                            </td>
                            <td class="py-2 pr-2 text-right space-x-2">
                                <a href="detail_buku.php?id=<?= $h['book_id'] ?>" class="text-indigo-600 hover:underline">Detail</a>
                                <?php if ($h['file_id']): ?>
                                    <a href="baca.php?book_id=<?= $h['book_id'] ?>&file_id=<?= $h['file_id'] ?>" target="_blank"
                                        class="text-emerald-600 hover:underline">Lanjut Baca</a>
                                <?php endif; ?>
                                <button onclick="document.getElementById('update-<?= $h['book_id'] ?>').classList.remove('hidden')"
                                    class="text-gray-500 hover:underline">Update Halaman</button>

                                <div id="update-<?= $h['book_id'] ?>" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                                    <div class="bg-white p-5 rounded shadow w-80 text-left">
                                        <h3 class="font-bold mb-1"><?= htmlspecialchars($h['title']) ?></h3>
                                        <p class="text-xs text-gray-400 mb-3">Total halaman: <?= $h['total_pages'] ?></p>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="update_page">
                                            <input type="hidden" name="book_id" value="<?= $h['book_id'] ?>">
                                            <label class="block text-xs text-gray-500 mb-1">Sampai halaman ke-</label>
                                            <input type="number" name="last_page_read" min="1" max="<?= $h['total_pages'] ?>"
                                                value="<?= $h['last_page_read'] ?>" required
                                                class="w-full border rounded p-2 mb-3 text-sm">
                                            <div class="flex justify-end gap-2">
                                                <button type="button" onclick="document.getElementById('update-<?= $h['book_id'] ?>').classList.add('hidden')"
                                                    class="px-3 py-1 rounded bg-gray-200 text-sm">Batal</button>
                                                <button type="submit" class="px-3 py-1 rounded bg-indigo-600 text-white text-sm">Simpan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="py-6 text-center text-gray-400">
                        Belum ada riwayat membaca. <a href="katalog.php" class="text-indigo-600 hover:underline">Cari buku di katalog</a>.
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>