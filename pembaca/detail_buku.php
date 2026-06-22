<?php
require_once '../auth/check_session.php';
requireRole(['Pembaca']);
require_once '../config/database.php';

$pageTitle = 'Detail Buku';
$book_id = (int) ($_GET['id'] ?? 0);

$success = '';
$error = '';

// ===== Proses tambah ke wishlist (dari halaman detail) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'wishlist') {
    $user_id = $_SESSION['user_id'];

    $cek = $conn->prepare("SELECT id FROM wishlists WHERE user_id = ? AND book_id = ?");
    $cek->bind_param('ii', $user_id, $book_id);
    $cek->execute();
    if ($cek->get_result()->num_rows > 0) {
        $error = 'Buku ini sudah ada di wishlist kamu.';
    } else {
        $stmt = $conn->prepare("INSERT INTO wishlists (user_id, book_id, added_at) VALUES (?, ?, NOW())");
        $stmt->bind_param('ii', $user_id, $book_id);
        if ($stmt->execute()) {
            $success = 'Buku berhasil ditambahkan ke wishlist.';
        } else {
            $error = 'Gagal menambahkan ke wishlist.';
        }
        $stmt->close();
    }
    $cek->close();
}

// ===== Proses tambah bookmark =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_bookmark') {
    $user_id = $_SESSION['user_id'];
    $page_number = (int) $_POST['page_number'];
    $notes = trim($_POST['notes']);

    $stmt = $conn->prepare("INSERT INTO bookmarks (user_id, book_id, page_number, notes) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iiis', $user_id, $book_id, $page_number, $notes);
    if ($stmt->execute()) {
        $success = 'Bookmark berhasil ditambahkan.';
    } else {
        $error = 'Gagal menambahkan bookmark.';
    }
    $stmt->close();
}

// ===== Proses edit bookmark =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_bookmark') {
    $user_id = $_SESSION['user_id'];
    $bookmark_id = (int) $_POST['bookmark_id'];
    $page_number = (int) $_POST['page_number'];
    $notes = trim($_POST['notes']);

    $stmt = $conn->prepare("UPDATE bookmarks SET page_number = ?, notes = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param('isii', $page_number, $notes, $bookmark_id, $user_id);
    if ($stmt->execute()) {
        $success = 'Bookmark berhasil diperbarui.';
    } else {
        $error = 'Gagal memperbarui bookmark.';
    }
    $stmt->close();
}

// ===== Proses hapus bookmark =====
if (isset($_GET['delete_bookmark'])) {
    $user_id = $_SESSION['user_id'];
    $bookmark_id = (int) $_GET['delete_bookmark'];

    $stmt = $conn->prepare("DELETE FROM bookmarks WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $bookmark_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $success = 'Bookmark berhasil dihapus.';
}

// ===== Proses tambah/edit ulasan (1 user 1 ulasan per buku) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'review') {
    $user_id = $_SESSION['user_id'];
    $rating = (int) $_POST['rating'];
    $review_text = trim($_POST['review_text']);

    $cek = $conn->prepare("SELECT id FROM reviews_ratings WHERE user_id = ? AND book_id = ?");
    $cek->bind_param('ii', $user_id, $book_id);
    $cek->execute();
    $existingReview = $cek->get_result()->fetch_assoc();
    $cek->close();

    if ($existingReview) {
        $stmt = $conn->prepare("UPDATE reviews_ratings SET rating = ?, review_text = ?, review_date = NOW() WHERE id = ?");
        $stmt->bind_param('isi', $rating, $review_text, $existingReview['id']);
        $stmt->execute();
        $stmt->close();
        $success = 'Ulasan kamu berhasil diperbarui.';
    } else {
        $stmt = $conn->prepare("INSERT INTO reviews_ratings (user_id, book_id, rating, review_text, review_date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param('iiis', $user_id, $book_id, $rating, $review_text);
        $stmt->execute();
        $stmt->close();
        $success = 'Ulasan kamu berhasil ditambahkan.';
    }
}

// ===== Proses hapus ulasan milik sendiri =====
if (isset($_GET['delete_review'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("DELETE FROM reviews_ratings WHERE user_id = ? AND book_id = ?");
    $stmt->bind_param('ii', $user_id, $book_id);
    $stmt->execute();
    $stmt->close();
    $success = 'Ulasan kamu berhasil dihapus.';
}

// ===== Ambil file buku (jika ada) =====// ===== Ambil data buku utama =====
$stmt = $conn->prepare("
    SELECT bk.*, p.publisher_name
    FROM books bk
    LEFT JOIN publishers p ON bk.publisher_id = p.id
    WHERE bk.id = ?
");
$stmt->bind_param('i', $book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$book) {
    require_once '../includes/header.php';
    echo '<div class="p-6"><p class="text-gray-400 text-sm">Buku tidak ditemukan.</p>
          <a href="katalog.php" class="text-indigo-600 text-sm hover:underline">Kembali ke katalog</a></div>';
    require_once '../includes/footer.php';
    exit;
}

// ===== Penulis =====
$authors = $conn->query("
    SELECT a.author_name FROM authors a
    JOIN book_authors ba ON ba.author_id = a.id
    WHERE ba.book_id = $book_id
");

// ===== Kategori =====
$categories = $conn->query("
    SELECT c.category_name FROM categories c
    JOIN book_categories bc ON bc.category_id = c.id
    WHERE bc.book_id = $book_id
");

// ===== Status sedang dipinjam (oleh siapa pun) =====
$dipinjam = $conn->query("
    SELECT COUNT(*) AS total FROM borrowings
    WHERE book_id = $book_id AND status IN ('Dipinjam', 'Terlambat')
")->fetch_assoc()['total'];

// ===== Rating rata-rata =====
$ratingRow = $conn->query("
    SELECT ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS total_review
    FROM reviews_ratings WHERE book_id = $book_id
")->fetch_assoc();

// ===== Ulasan milik sendiri (untuk pre-fill form) =====
$myReviewStmt = $conn->prepare("SELECT rating, review_text FROM reviews_ratings WHERE user_id = ? AND book_id = ?");
$myReviewStmt->bind_param('ii', $_SESSION['user_id'], $book_id);
$myReviewStmt->execute();
$myReview = $myReviewStmt->get_result()->fetch_assoc();

// ===== Daftar ulasan =====
$reviews = $conn->query("
    SELECT rr.rating, rr.review_text, rr.review_date, mp.full_name
    FROM reviews_ratings rr
    LEFT JOIN member_profiles mp ON mp.user_id = rr.user_id
    WHERE rr.book_id = $book_id
    ORDER BY rr.review_date DESC
");

// ===== Daftar file buku (untuk tombol Baca) =====
$bookFiles = $conn->query("SELECT id, file_url, file_format FROM book_files WHERE book_id = $book_id");

// ===== Daftar bookmark milik user untuk buku ini =====
$bookmarkStmt = $conn->prepare("SELECT id, page_number, notes FROM bookmarks WHERE user_id = ? AND book_id = ? ORDER BY page_number ASC");
$bookmarkStmt->bind_param('ii', $_SESSION['user_id'], $book_id);
$bookmarkStmt->execute();
$bookmarks = $bookmarkStmt->get_result();

require_once '../includes/header.php';
?>

<div class="p-6 max-w-4xl mx-auto">
    <a href="katalog.php" class="text-sm text-indigo-600 hover:underline">&larr; Kembali ke Katalog</a>

    <?php if ($error): ?>
        <div class="mt-4 px-4 py-2 bg-red-100 text-red-700 rounded text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="mt-4 px-4 py-2 bg-green-100 text-green-700 rounded text-sm"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="bg-white rounded shadow-sm border border-gray-100 p-6 mt-4">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($book['title']) ?></h1>
                <p class="text-sm text-gray-500 mt-1">
                    <?php
                        $authorNames = [];
                        while ($a = $authors->fetch_assoc()) $authorNames[] = $a['author_name'];
                        echo htmlspecialchars(implode(', ', $authorNames) ?: 'Penulis tidak diketahui');
                    ?>
                </p>
            </div>
            <?php if ($dipinjam > 0): ?>
                <span class="bg-red-100 text-red-700 text-xs px-3 py-1 rounded shrink-0">Sedang Dipinjam</span>
            <?php else: ?>
                <span class="bg-green-100 text-green-700 text-xs px-3 py-1 rounded shrink-0">Tersedia</span>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 text-sm">
            <div>
                <p class="text-xs text-gray-400">Penerbit</p>
                <p class="text-gray-700"><?= htmlspecialchars($book['publisher_name'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Tahun Terbit</p>
                <p class="text-gray-700"><?= htmlspecialchars($book['publication_year']) ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Total Halaman</p>
                <p class="text-gray-700"><?= htmlspecialchars($book['total_pages']) ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Rating</p>
                <p class="text-yellow-600">⭐ <?= $ratingRow['avg_rating'] ?? '0.0' ?> (<?= $ratingRow['total_review'] ?> ulasan)</p>
            </div>
        </div>

        <div class="mt-4">
            <p class="text-xs text-gray-400 mb-1">Kategori</p>
            <div class="flex gap-1 flex-wrap">
                <?php while ($c = $categories->fetch_assoc()): ?>
                    <span class="bg-indigo-50 text-indigo-700 text-xs px-2 py-0.5 rounded">
                        <?= htmlspecialchars($c['category_name']) ?>
                    </span>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="mt-4">
            <p class="text-xs text-gray-400 mb-1">Sinopsis</p>
            <p class="text-sm text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($book['synopsis'] ?? '-')) ?></p>
        </div>

        <div class="mt-5 flex gap-2">
            <form method="POST">
                <input type="hidden" name="action" value="wishlist">
                <button type="submit" class="bg-pink-600 text-white text-sm px-4 py-1.5 rounded hover:bg-pink-700">
                    ♥ Tambahkan ke Wishlist
                </button>
            </form>

             <?php if ($bookFiles && $bookFiles->num_rows > 0): ?>
                <div class="flex gap-2 flex-wrap">
                    <?php while ($bf = $bookFiles->fetch_assoc()): ?>
                        <form method="POST" target="_blank">
                            <input type="hidden" name="action" value="baca">
                            <input type="hidden" name="file_id" value="<?= $bf['id'] ?>">
                            <button type="submit" class="bg-emerald-600 text-white text-sm px-4 py-1.5 rounded hover:bg-emerald-700">
                                Baca (<?= htmlspecialchars($bf['file_format']) ?>)
                            </button>
                        </form>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <span class="bg-gray-100 text-gray-400 text-sm px-4 py-1.5 rounded">File belum tersedia</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- ULASAN -->
    <div class="bg-white rounded shadow-sm border border-gray-100 p-6 mt-4">
        <h2 class="font-medium text-gray-700 mb-3">Ulasan & Rating</h2>

        <!-- FORM ULASAN SAYA -->
        <div class="bg-gray-50 rounded p-4 mb-4">
            <p class="text-sm font-medium text-gray-700 mb-2">
                <?= $myReview ? 'Edit Ulasan Saya' : 'Tulis Ulasan' ?>
            </p>
            <form method="POST">
                <input type="hidden" name="action" value="review">
                <label class="block text-xs text-gray-500 mb-1">Rating</label>
                <select name="rating" required class="border border-gray-300 rounded px-3 py-1.5 text-sm mb-2">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i ?>" <?= ($myReview && (int)$myReview['rating'] === $i) ? 'selected' : '' ?>>
                            <?= $i ?> ⭐
                        </option>
                    <?php endfor; ?>
                </select>
                <label class="block text-xs text-gray-500 mb-1">Ulasan</label>
                <textarea name="review_text" rows="2" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm mb-2"
                    placeholder="Bagaimana menurutmu tentang buku ini?"><?= htmlspecialchars($myReview['review_text'] ?? '') ?></textarea>
                <div class="flex gap-2">
                    <button type="submit" class="bg-indigo-600 text-white text-xs px-4 py-1.5 rounded hover:bg-indigo-700">
                        <?= $myReview ? 'Simpan Perubahan' : 'Kirim Ulasan' ?>
                    </button>
                    <?php if ($myReview): ?>
                        <a href="?id=<?= $book_id ?>&delete_review=1" onclick="return confirm('Hapus ulasan kamu?')"
                            class="bg-red-50 text-red-600 text-xs px-4 py-1.5 rounded hover:bg-red-100">
                            Hapus Ulasan
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if ($reviews && $reviews->num_rows > 0): ?>
            <div class="space-y-3">
                <?php while ($r = $reviews->fetch_assoc()): ?>
                    <div class="border-b border-gray-100 pb-3">
                        <div class="flex justify-between items-center">
                            <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($r['full_name'] ?? 'Anonim') ?></p>
                            <span class="text-xs text-yellow-600">⭐ <?= $r['rating'] ?>/5</span>
                        </div>
                        <p class="text-xs text-gray-400 mb-1"><?= htmlspecialchars($r['review_date']) ?></p>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($r['review_text']) ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-sm text-gray-400">Belum ada ulasan untuk buku ini.</p>
        <?php endif; ?>
    </div>
    <!-- BOOKMARK -->
    <div class="bg-white rounded shadow-sm border border-gray-100 p-6 mt-4">
        <div class="flex justify-between items-center mb-3">
            <h2 class="font-medium text-gray-700">Bookmark Saya</h2>
            <button onclick="document.getElementById('modal-add-bookmark').classList.remove('hidden')"
                class="bg-amber-500 text-white text-xs px-3 py-1.5 rounded hover:bg-amber-600">
                + Tambah Bookmark
            </button>
        </div>

        <?php if ($bookmarks && $bookmarks->num_rows > 0): ?>
            <div class="space-y-2">
                <?php while ($bm = $bookmarks->fetch_assoc()): ?>
                    <div class="flex justify-between items-start border-b border-gray-100 pb-2">
                        <div>
                            <p class="text-sm text-gray-700">Halaman <span class="font-medium"><?= $bm['page_number'] ?></span></p>
                            <p class="text-xs text-gray-500"><?= htmlspecialchars($bm['notes'] ?: '-') ?></p>
                        </div>
                        <div class="flex gap-2 shrink-0">
                            <button onclick="document.getElementById('modal-edit-bookmark-<?= $bm['id'] ?>').classList.remove('hidden')"
                                class="text-indigo-600 hover:underline text-xs">Edit</button>
                            <a href="?id=<?= $book_id ?>&delete_bookmark=<?= $bm['id'] ?>"
                                onclick="return confirm('Hapus bookmark ini?')" class="text-red-600 hover:underline text-xs">Hapus</a>
                        </div>
                    </div>

                    <!-- MODAL EDIT -->
                    <div id="modal-edit-bookmark-<?= $bm['id'] ?>" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                        <div class="bg-white p-5 rounded shadow w-80 text-left">
                            <h3 class="font-bold mb-3">Edit Bookmark</h3>
                            <form method="POST">
                                <input type="hidden" name="action" value="edit_bookmark">
                                <input type="hidden" name="bookmark_id" value="<?= $bm['id'] ?>">
                                <label class="block text-xs text-gray-500 mb-1">Nomor Halaman</label>
                                <input type="number" name="page_number" min="1" max="<?= $book['total_pages'] ?>"
                                    value="<?= $bm['page_number'] ?>" required class="w-full border rounded p-2 mb-2 text-sm">
                                <label class="block text-xs text-gray-500 mb-1">Catatan</label>
                                <textarea name="notes" class="w-full border rounded p-2 mb-3 text-sm" rows="2"><?= htmlspecialchars($bm['notes']) ?></textarea>
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="document.getElementById('modal-edit-bookmark-<?= $bm['id'] ?>').classList.add('hidden')"
                                        class="px-3 py-1 rounded bg-gray-200 text-sm">Batal</button>
                                    <button type="submit" class="px-3 py-1 rounded bg-indigo-600 text-white text-sm">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-sm text-gray-400">Belum ada bookmark untuk buku ini.</p>
        <?php endif; ?>
    </div>

    <!-- MODAL TAMBAH BOOKMARK -->
    <div id="modal-add-bookmark" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white p-5 rounded shadow w-80 text-left">
            <h3 class="font-bold mb-3">Tambah Bookmark</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_bookmark">
                <label class="block text-xs text-gray-500 mb-1">Nomor Halaman</label>
                <input type="number" name="page_number" min="1" max="<?= $book['total_pages'] ?>" required
                    class="w-full border rounded p-2 mb-2 text-sm">
                <label class="block text-xs text-gray-500 mb-1">Catatan (opsional)</label>
                <textarea name="notes" class="w-full border rounded p-2 mb-3 text-sm" rows="2" placeholder="Catatan halaman ini..."></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-add-bookmark').classList.add('hidden')"
                        class="px-3 py-1 rounded bg-gray-200 text-sm">Batal</button>
                    <button type="submit" class="px-3 py-1 rounded bg-amber-500 text-white text-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>