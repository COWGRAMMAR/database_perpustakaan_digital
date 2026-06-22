<?php
require_once '../auth/check_session.php';
requireRole(['Staff']);
require_once '../config/database.php';
require_once '../includes/audit_helper.php';

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

$success = '';
$error = '';

// ===== TAMBAH FILE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $file_url = trim($_POST['file_url']);
    $file_size_mb = (float) $_POST['file_size_mb'];
    $file_format = $_POST['file_format'];

    $stmt = $conn->prepare("INSERT INTO book_files (book_id, file_url, file_size_mb, file_format) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isds', $book_id, $file_url, $file_size_mb, $file_format);
    if ($stmt->execute()) {
        $success = 'File buku berhasil ditambahkan.';
        logAudit($conn, $_SESSION['user_id'], 'TAMBAH_FILE', 'book_files',
            "Staff menambahkan file ($file_format) untuk buku ID $book_id");
    } else {
        $error = 'Gagal menambahkan file.';
    }
    $stmt->close();
}

// ===== EDIT FILE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $file_id = (int) $_POST['file_id'];
    $file_url = trim($_POST['file_url']);
    $file_size_mb = (float) $_POST['file_size_mb'];
    $file_format = $_POST['file_format'];

    $stmt = $conn->prepare("UPDATE book_files SET file_url = ?, file_size_mb = ?, file_format = ? WHERE id = ? AND book_id = ?");
    $stmt->bind_param('sdsii', $file_url, $file_size_mb, $file_format, $file_id, $book_id);
    if ($stmt->execute()) {
        $success = 'File buku berhasil diperbarui.';
        logAudit($conn, $_SESSION['user_id'], 'EDIT_FILE', 'book_files',
            "Staff mengedit file ID $file_id untuk buku ID $book_id");
    } else {
        $error = 'Gagal memperbarui file.';
    }
    $stmt->close();
}

// ===== HAPUS FILE =====
if (isset($_GET['delete'])) {
    $file_id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM book_files WHERE id = ? AND book_id = ?");
    $stmt->bind_param('ii', $file_id, $book_id);
    if ($stmt->execute()) {
        $success = 'File buku berhasil dihapus.';
        logAudit($conn, $_SESSION['user_id'], 'HAPUS_FILE', 'book_files',
            "Staff menghapus file ID $file_id untuk buku ID $book_id");
    } else {
        $error = 'Gagal menghapus file.';
    }
    $stmt->close();
}

$files = $conn->query("SELECT * FROM book_files WHERE book_id = $book_id ORDER BY id DESC");

require_once '../includes/header.php';
?>

<div class="p-6 max-w-3xl mx-auto">
    <a href="books.php" class="text-sm text-indigo-600 hover:underline">&larr; Kembali ke Manajemen Buku</a>
    <h1 class="text-xl font-semibold text-gray-800 mt-2 mb-1">File Buku</h1>
    <p class="text-sm text-gray-500 mb-4">Buku: <span class="font-medium"><?= htmlspecialchars($book['title']) ?></span></p>

    <?php if ($error): ?>
        <div class="mb-4 px-4 py-2 bg-red-100 text-red-700 rounded text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded text-sm"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- FORM TAMBAH -->
    <div class="bg-white p-4 rounded shadow-sm border border-gray-100 mb-5">
        <h2 class="font-medium text-gray-700 mb-3">Tambah File Baru</h2>
        <form method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-2">
            <input type="hidden" name="action" value="add">
            <input type="url" name="file_url" required placeholder="URL file (link PDF/EPUB)"
                class="sm:col-span-2 border border-gray-300 rounded px-3 py-1.5 text-sm">
            <input type="number" step="0.01" name="file_size_mb" required placeholder="Ukuran (MB)"
                class="border border-gray-300 rounded px-3 py-1.5 text-sm">
            <select name="file_format" required class="border border-gray-300 rounded px-3 py-1.5 text-sm">
                <option value="PDF">PDF</option>
                <option value="EPUB">EPUB</option>
            </select>
            <button type="submit" class="sm:col-span-4 bg-indigo-600 text-white text-sm px-4 py-1.5 rounded hover:bg-indigo-700 w-fit">
                Tambah File
            </button>
        </form>
    </div>

    <!-- LIST FILE -->
    <div class="bg-white p-4 rounded shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-200">
                    <th class="py-2 pr-2">URL File</th>
                    <th class="py-2 pr-2">Format</th>
                    <th class="py-2 pr-2">Ukuran</th>
                    <th class="py-2 pr-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($files && $files->num_rows > 0): ?>
                    <?php while ($f = $files->fetch_assoc()): ?>
                        <tr class="border-b border-gray-100">
                            <td class="py-2 pr-2 text-gray-700 truncate max-w-[220px]">
                                <a href="<?= htmlspecialchars($f['file_url']) ?>" target="_blank" class="text-indigo-600 hover:underline">
                                    <?= htmlspecialchars($f['file_url']) ?>
                                </a>
                            </td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($f['file_format']) ?></td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($f['file_size_mb']) ?> MB</td>
                            <td class="py-2 pr-2 text-right">
                                <button onclick="document.getElementById('edit-<?= $f['id'] ?>').classList.remove('hidden')"
                                    class="text-indigo-600 hover:underline mr-2">Edit</button>
                                <a href="?book_id=<?= $book_id ?>&delete=<?= $f['id'] ?>"
                                    onclick="return confirm('Hapus file ini?')" class="text-red-600 hover:underline">Hapus</a>

                                <div id="edit-<?= $f['id'] ?>" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                                    <div class="bg-white p-5 rounded shadow w-80 text-left">
                                        <h3 class="font-bold mb-3">Edit File</h3>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="file_id" value="<?= $f['id'] ?>">
                                            <label class="block text-xs text-gray-500 mb-1">URL File</label>
                                            <input type="url" name="file_url" value="<?= htmlspecialchars($f['file_url']) ?>" required
                                                class="w-full border rounded p-2 mb-2 text-sm">
                                            <label class="block text-xs text-gray-500 mb-1">Ukuran (MB)</label>
                                            <input type="number" step="0.01" name="file_size_mb" value="<?= $f['file_size_mb'] ?>" required
                                                class="w-full border rounded p-2 mb-2 text-sm">
                                            <label class="block text-xs text-gray-500 mb-1">Format</label>
                                            <select name="file_format" class="w-full border rounded p-2 mb-3 text-sm">
                                                <option value="PDF" <?= $f['file_format'] === 'PDF' ? 'selected' : '' ?>>PDF</option>
                                                <option value="EPUB" <?= $f['file_format'] === 'EPUB' ? 'selected' : '' ?>>EPUB</option>
                                            </select>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" onclick="document.getElementById('edit-<?= $f['id'] ?>').classList.add('hidden')"
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
                    <tr><td colspan="4" class="py-4 text-center text-gray-400">Belum ada file untuk buku ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>