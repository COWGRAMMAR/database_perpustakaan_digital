<?php
require_once '../auth/check_session.php';
requireRole(['Pembaca']);
require_once '../config/database.php';

$pageTitle = 'Katalog Buku';

// ===== Proses tambah ke wishlist =====
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'wishlist') {
    $book_id = (int) $_POST['book_id'];
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

// ===== Filter & search =====
$search = $_GET['search'] ?? '';
$categoryFilter = (int) ($_GET['category'] ?? 0);

$where = "WHERE 1=1";
if ($search !== '') {
    $searchEsc = $conn->real_escape_string($search);
    $where .= " AND bk.title LIKE '%$searchEsc%'";
}
if ($categoryFilter > 0) {
    $where .= " AND bc.category_id = $categoryFilter";
}

$sql = "
    SELECT DISTINCT bk.id, bk.title, bk.publication_year, bk.synopsis,
        (SELECT ROUND(AVG(rr.rating), 1) FROM reviews_ratings rr WHERE rr.book_id = bk.id) AS avg_rating,
        (SELECT COUNT(*) FROM borrowings b WHERE b.book_id = bk.id AND b.status IN ('Dipinjam', 'Terlambat')) AS sedang_dipinjam
    FROM books bk
    LEFT JOIN book_categories bc ON bc.book_id = bk.id
    $where
    ORDER BY bk.title ASC
";
$books = $conn->query($sql);

$categories = $conn->query("SELECT id, category_name FROM categories ORDER BY category_name");

require_once '../includes/header.php';
?>

<div class="p-6">
    <h1 class="text-xl font-semibold text-gray-800 mb-4">Katalog Buku</h1>

    <?php if ($error): ?>
        <div class="mb-4 px-4 py-2 bg-red-100 text-red-700 rounded text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded text-sm"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- FILTER -->
    <form method="GET" class="flex flex-wrap gap-2 mb-5">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari judul buku..."
            class="border border-gray-300 rounded px-3 py-1.5 text-sm flex-1 min-w-[200px]">

        <select name="category" class="border border-gray-300 rounded px-3 py-1.5 text-sm">
            <option value="0">-- Semua Kategori --</option>
            <?php while ($c = $categories->fetch_assoc()): ?>
                <option value="<?= $c['id'] ?>" <?= $categoryFilter === (int)$c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['category_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-1.5 rounded hover:bg-indigo-700">
            Cari
        </button>
        <?php if ($search !== '' || $categoryFilter > 0): ?>
            <a href="katalog.php" class="text-sm text-gray-500 px-3 py-1.5 hover:underline">Reset</a>
        <?php endif; ?>
    </form>

    <!-- GRID BUKU -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php if ($books && $books->num_rows > 0): ?>
            <?php while ($b = $books->fetch_assoc()): ?>
                <div class="bg-white rounded shadow-sm border border-gray-100 p-4 flex flex-col">
                    <div class="flex justify-between items-start mb-1">
                        <h2 class="font-medium text-gray-800 text-sm leading-snug">
                            <?= htmlspecialchars($b['title']) ?>
                        </h2>
                        <?php if ($b['sedang_dipinjam'] > 0): ?>
                            <span class="bg-red-100 text-red-700 text-[10px] px-2 py-0.5 rounded shrink-0 ml-2">Sedang Dipinjam</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-gray-400 mb-2"><?= htmlspecialchars($b['publication_year']) ?></p>
                    <p class="text-xs text-gray-500 mb-3 line-clamp-3 flex-1">
                        <?= htmlspecialchars(mb_strimwidth($b['synopsis'] ?? '-', 0, 120, '...')) ?>
                    </p>

                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs text-yellow-600">
                            ⭐ <?= $b['avg_rating'] ?? '0.0' ?>
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <a href="detail_buku.php?id=<?= $b['id'] ?>"
                            class="flex-1 text-center bg-indigo-600 text-white text-xs px-3 py-1.5 rounded hover:bg-indigo-700">
                            Lihat Detail
                        </a>
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="wishlist">
                            <input type="hidden" name="book_id" value="<?= $b['id'] ?>">
                            <button type="submit" title="Tambah ke Wishlist"
                                class="bg-gray-100 text-gray-600 text-xs px-3 py-1.5 rounded hover:bg-gray-200">
                                ♥
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-400 text-sm col-span-full text-center py-8">Tidak ada buku yang ditemukan.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>