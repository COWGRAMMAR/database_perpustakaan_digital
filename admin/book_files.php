<?php
require_once '../auth/check_session.php';
requireRole(['Admin']);
require_once '../config/database.php';

$pageTitle = 'File Buku';
$book_id = (int) ($_GET['book_id'] ?? 0);

$bookStmt = $conn->prepare("SELECT id, title FROM books WHERE id = ?");
$bookStmt->bind_param('i', $book_id);
$bookStmt->execute();
$book = $bookStmt->get_result()->fetch_assoc();
$bookStmt->close();

if (!$book) {
    require_once '../includes/header.php';
    echo '<div class="p-6"><p class="text-gray-400 text-sm">Buku tidak ditemukan.</p>
          <a href="books.php" class="text-indigo-600 text-sm hover:underline">Kembali ke daftar buku</a></div>';
    require_once '../includes/footer.php';
    exit;
}

$files = $conn->query("SELECT * FROM book_files WHERE book_id = $book_id ORDER BY id DESC");

require_once '../includes/header.php';
?>

<div class="p-6 max-w-3xl mx-auto">
    <a href="books.php" class="text-sm text-indigo-600 hover:underline">&larr; Kembali ke Manajemen Buku</a>
    <h1 class="text-xl font-semibold text-gray-800 mt-2 mb-1">File Buku (Monitoring)</h1>
    <p class="text-sm text-gray-500 mb-4">Buku: <span class="font-medium"><?= htmlspecialchars($book['title']) ?></span></p>

    <div class="bg-white p-4 rounded shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-200">
                    <th class="py-2 pr-2">URL File</th>
                    <th class="py-2 pr-2">Format</th>
                    <th class="py-2 pr-2">Ukuran</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($files && $files->num_rows > 0): ?>
                    <?php while ($f = $files->fetch_assoc()): ?>
                        <tr class="border-b border-gray-100">
                            <td class="py-2 pr-2 text-gray-700 truncate max-w-[260px]">
                                <a href="<?= htmlspecialchars($f['file_url']) ?>" target="_blank" class="text-indigo-600 hover:underline">
                                    <?= htmlspecialchars($f['file_url']) ?>
                                </a>
                            </td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($f['file_format']) ?></td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($f['file_size_mb']) ?> MB</td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="py-4 text-center text-gray-400">Belum ada file untuk buku ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>