<?php
require_once '../auth/check_session.php';
requireRole(['Staff']);
require_once '../config/database.php';

$pageTitle = 'Manajemen Buku';
$error = '';
$success = '';

// ===== CREATE / UPDATE BUKU =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = $_POST['id'] ?? null;

    $title = trim($_POST['title'] ?? '');
    $publisher_id = $_POST['publisher_id'] ?? null;
    $isbn = trim($_POST['isbn'] ?? '');
    $publication_year = $_POST['publication_year'] ?? null;
    $synopsis = trim($_POST['synopsis'] ?? '');
    $total_pages = $_POST['total_pages'] ?? 0;
    $author_ids = $_POST['author_ids'] ?? [];
    $category_ids = $_POST['category_ids'] ?? [];

    if ($title === '' || !$publisher_id) {
        $error = 'Judul dan Penerbit wajib diisi.';
    } else {
        if ($action === 'create') {
            $stmt = $conn->prepare("INSERT INTO books (publisher_id, title, isbn, publication_year, synopsis, total_pages) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('issisi', $publisher_id, $title, $isbn, $publication_year, $synopsis, $total_pages);
            if ($stmt->execute()) {
                $bookId = $stmt->insert_id;
                foreach ($author_ids as $aid) {
                    $conn->query("INSERT INTO book_authors (book_id, author_id) VALUES ($bookId, " . (int)$aid . ")");
                }
                foreach ($category_ids as $cid) {
                    $conn->query("INSERT INTO book_categories (book_id, category_id) VALUES ($bookId, " . (int)$cid . ")");
                }
                $success = 'Buku berhasil ditambahkan.';
            } else {
                $error = 'Gagal menambahkan buku: ' . $stmt->error;
            }
            $stmt->close();
        } elseif ($action === 'update' && $id) {
            $stmt = $conn->prepare("UPDATE books SET publisher_id=?, title=?, isbn=?, publication_year=?, synopsis=?, total_pages=? WHERE id=?");
            $stmt->bind_param('issisii', $publisher_id, $title, $isbn, $publication_year, $synopsis, $total_pages, $id);
            if ($stmt->execute()) {
                $conn->query("DELETE FROM book_authors WHERE book_id = " . (int)$id);
                $conn->query("DELETE FROM book_categories WHERE book_id = " . (int)$id);
                foreach ($author_ids as $aid) {
                    $conn->query("INSERT INTO book_authors (book_id, author_id) VALUES ($id, " . (int)$aid . ")");
                }
                foreach ($category_ids as $cid) {
                    $conn->query("INSERT INTO book_categories (book_id, category_id) VALUES ($id, " . (int)$cid . ")");
                }
                $success = 'Buku berhasil diperbarui.';
            } else {
                $error = 'Gagal memperbarui buku: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// ===== DELETE BUKU =====
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $success = 'Buku berhasil dihapus.';
    } else {
        $error = 'Gagal menghapus: buku masih memiliki riwayat peminjaman/file terkait.';
    }
    $stmt->close();
}

// ===== AMBIL DATA UTK EDIT =====
$editData = null;
$editAuthorIds = [];
$editCategoryIds = [];
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $r1 = $conn->query("SELECT author_id FROM book_authors WHERE book_id = $id");
    while ($r = $r1->fetch_assoc()) $editAuthorIds[] = $r['author_id'];

    $r2 = $conn->query("SELECT category_id FROM book_categories WHERE book_id = $id");
    while ($r = $r2->fetch_assoc()) $editCategoryIds[] = $r['category_id'];
}

// ===== DROPDOWN DATA =====
$publishers = $conn->query("SELECT id, publisher_name FROM publishers ORDER BY publisher_name");
$authors = $conn->query("SELECT id, author_name FROM authors ORDER BY author_name");
$categories = $conn->query("SELECT id, category_name FROM categories ORDER BY category_name");

// ===== LIST BUKU =====
$books = $conn->query("
    SELECT b.*, p.publisher_name,
        GROUP_CONCAT(DISTINCT a.author_name SEPARATOR ', ') AS author_names,
        GROUP_CONCAT(DISTINCT c.category_name SEPARATOR ', ') AS category_names
    FROM books b
    LEFT JOIN publishers p ON b.publisher_id = p.id
    LEFT JOIN book_authors ba ON b.id = ba.book_id
    LEFT JOIN authors a ON ba.author_id = a.id
    LEFT JOIN book_categories bc ON b.id = bc.book_id
    LEFT JOIN categories c ON bc.category_id = c.id
    GROUP BY b.id
    ORDER BY b.id ASC
");

require_once '../includes/header.php';
?>

<div class="p-6">
    <h1 class="text-xl font-semibold text-gray-800 mb-4">Manajemen Buku</h1>

    <?php if ($error): ?>
        <div class="mb-4 px-4 py-2 bg-red-100 text-red-700 rounded text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded text-sm"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- FORM -->
        <div class="lg:col-span-1 bg-white p-4 rounded shadow-sm border border-gray-100">
            <h2 class="font-medium text-gray-700 mb-3"><?= $editData ? 'Edit Buku' : 'Tambah Buku Baru' ?></h2>
            <form method="POST" action="books.php">
                <input type="hidden" name="action" value="<?= $editData ? 'update' : 'create' ?>">
                <?php if ($editData): ?><input type="hidden" name="id" value="<?= $editData['id'] ?>"><?php endif; ?>

                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">Judul Buku</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($editData['title'] ?? '') ?>"
                        class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">Penerbit</label>
                    <select name="publisher_id" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                        <option value="">-- Pilih Penerbit --</option>
                        <?php $publishers->data_seek(0); while ($p = $publishers->fetch_assoc()): ?>
                            <option value="<?= $p['id'] ?>" <?= ($editData['publisher_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['publisher_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">ISBN</label>
                    <input type="text" name="isbn" maxlength="13" value="<?= htmlspecialchars($editData['isbn'] ?? '') ?>"
                        class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                </div>

                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">Tahun Terbit</label>
                    <input type="number" name="publication_year" value="<?= htmlspecialchars($editData['publication_year'] ?? '') ?>"
                        class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                </div>

                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">Total Halaman</label>
                    <input type="number" name="total_pages" value="<?= htmlspecialchars($editData['total_pages'] ?? '') ?>"
                        class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                </div>

                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">Sinopsis</label>
                    <textarea name="synopsis" rows="3"
                        class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm"><?= htmlspecialchars($editData['synopsis'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">Penulis (bisa pilih lebih dari satu)</label>
                    <select name="author_ids[]" multiple class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm h-24">
                        <?php $authors->data_seek(0); while ($a = $authors->fetch_assoc()): ?>
                            <option value="<?= $a['id'] ?>" <?= in_array($a['id'], $editAuthorIds) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['author_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">Kategori (bisa pilih lebih dari satu)</label>
                    <select name="category_ids[]" multiple class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm h-24">
                        <?php $categories->data_seek(0); while ($c = $categories->fetch_assoc()): ?>
                            <option value="<?= $c['id'] ?>" <?= in_array($c['id'], $editCategoryIds) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['category_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-1.5 rounded hover:bg-indigo-700">
                        <?= $editData ? 'Update' : 'Simpan' ?>
                    </button>
                    <?php if ($editData): ?>
                        <a href="books.php" class="text-sm px-4 py-1.5 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- LIST -->
        <div class="lg:col-span-2 bg-white p-4 rounded shadow-sm border border-gray-100 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-200">
                        <th class="py-2 pr-2">Judul</th>
                        <th class="py-2 pr-2">Penerbit</th>
                        <th class="py-2 pr-2">Penulis</th>
                        <th class="py-2 pr-2">Kategori</th>
                        <th class="py-2 pr-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($books && $books->num_rows > 0): ?>
                        <?php while ($b = $books->fetch_assoc()): ?>
                            <tr class="border-b border-gray-100 align-top">
                                <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['title']) ?></td>
                                <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['publisher_name'] ?? '-') ?></td>
                                <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['author_names'] ?? '-') ?></td>
                                <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['category_names'] ?? '-') ?></td>
                                <td class="py-2 pr-2 text-right whitespace-nowrap">
                                    <a href="?edit=<?= $b['id'] ?>" class="text-indigo-600 hover:underline mr-3">Edit</a>
                                    <a href="?delete=<?= $b['id'] ?>" onclick="return confirm('Yakin hapus buku ini?')" class="text-red-600 hover:underline">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="py-4 text-center text-gray-400">Belum ada data buku.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>