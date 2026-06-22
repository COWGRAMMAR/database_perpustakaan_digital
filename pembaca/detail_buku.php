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

// ===== Ambil data buku utama =====
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

// ===== Daftar ulasan =====
$reviews = $conn->query("
    SELECT rr.rating, rr.review_text, rr.review_date, mp.full_name
    FROM reviews_ratings rr
    LEFT JOIN member_profiles mp ON mp.user_id = rr.user_id
    WHERE rr.book_id = $book_id
    ORDER BY rr.review_date DESC
");

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

        <form method="POST" class="mt-5">
            <input type="hidden" name="action" value="wishlist">
            <button type="submit" class="bg-pink-600 text-white text-sm px-4 py-1.5 rounded hover:bg-pink-700">
                ♥ Tambahkan ke Wishlist
            </button>
        </form>
    </div>

    <!-- ULASAN -->
    <div class="bg-white rounded shadow-sm border border-gray-100 p-6 mt-4">
        <h2 class="font-medium text-gray-700 mb-3">Ulasan & Rating</h2>
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
</div>

<?php require_once '../includes/footer.php'; ?>