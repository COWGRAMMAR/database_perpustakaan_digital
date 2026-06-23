<?php
require_once '../auth/check_session.php';
requireRole(['Pembaca']);
require_once '../config/database.php';

$pageTitle = 'Wishlist';
$user_id = $_SESSION['user_id'];

$success = '';
$error = '';

// ===== Proses hapus dari wishlist =====
if (isset($_GET['remove'])) {
    $wishlist_id = (int) $_GET['remove'];

    $stmt = $conn->prepare("DELETE FROM wishlists WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $wishlist_id, $user_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $success = 'Buku berhasil dihapus dari wishlist.';
    } else {
        $error = 'Gagal menghapus, atau data tidak ditemukan.';
    }
    $stmt->close();
}

// ===== Ambil data wishlist milik user ini =====
$stmt = $conn->prepare("
    SELECT w.id AS wishlist_id, w.added_at,
           bk.id AS book_id, bk.title, bk.publication_year,
           (SELECT ROUND(AVG(rr.rating), 1) FROM reviews_ratings rr WHERE rr.book_id = bk.id) AS avg_rating
    FROM wishlists w
    JOIN books bk ON w.book_id = bk.id
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$wishlists = $stmt->get_result();

require_once '../includes/header.php';
?>

<div class="p-6">
    <h1 class="text-xl font-semibold text-gray-800 mb-4">Wishlist Saya</h1>

    <?php if ($error): ?>
        <div class="mb-4 px-4 py-2 bg-red-100 text-red-700 rounded text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded text-sm"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php if ($wishlists && $wishlists->num_rows > 0): ?>
            <?php while ($w = $wishlists->fetch_assoc()): ?>
                <div class="bg-white rounded shadow-sm border border-gray-100 p-4 flex flex-col">
                    <h2 class="font-medium text-gray-800 text-sm leading-snug mb-1">
                        <?= htmlspecialchars($w['title']) ?>
                    </h2>
                    <p class="text-xs text-gray-400 mb-2"><?= htmlspecialchars($w['publication_year']) ?></p>

                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs text-yellow-600">⭐ <?= $w['avg_rating'] ?? '0.0' ?></span>
                        <span class="text-xs text-gray-400">Ditambahkan <?= date('d M Y', strtotime($w['added_at'])) ?></span>
                    </div>

                    <div class="flex gap-2 mt-auto">
                        <a href="detail_buku.php?id=<?= $w['book_id'] ?>"
                            class="flex-1 text-center bg-indigo-600 text-white text-xs px-3 py-1.5 rounded hover:bg-indigo-700">
                            Lihat Detail
                        </a>
                        <a href="?remove=<?= $w['wishlist_id'] ?>"
                            onclick="return confirm('Hapus buku ini dari wishlist?')"
                            class="bg-red-50 text-red-600 text-xs px-3 py-1.5 rounded hover:bg-red-100">
                            Hapus
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-400 text-sm col-span-full text-center py-8">
                Belum ada buku di wishlist. <a href="katalog.php" class="text-indigo-600 hover:underline">Cari buku di katalog</a>.
            </p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 