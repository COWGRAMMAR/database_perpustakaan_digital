<?php
require_once '../auth/check_session.php';
requireRole(['Admin']);
require_once '../config/database.php';

$pageTitle = 'Manajemen Buku (Monitoring)';

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
    <h1 class="text-xl font-semibold text-gray-800 mb-1">Manajemen Buku</h1>
    <p class="text-sm text-gray-500 mb-4">Mode monitoring — perubahan data buku dilakukan oleh Staff.</p>

    <div class="bg-white p-4 rounded shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-200">
                    <th class="py-2 pr-2">Id</th>
                    <th class="py-2 pr-2">Judul</th>
                    <th class="py-2 pr-2">Penerbit</th>
                    <th class="py-2 pr-2">Penulis</th>
                    <th class="py-2 pr-2">Kategori</th>
                    <th class="py-2 pr-2">Tahun</th>
                    <th class="py-2 pr-2 text-center">File</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($books && $books->num_rows > 0): ?>
                    <?php while ($b = $books->fetch_assoc()): ?>
                        <tr class="border-b border-gray-100">
                            <td class="py-2 pr-2 text-gray-500"><?= $b['id'] ?></td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['title']) ?></td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['publisher_name'] ?? '-') ?></td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['author_names'] ?? '-') ?></td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['category_names'] ?? '-') ?></td>
                            <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($b['publication_year'] ?? '-') ?></td>
                            <td class="py-2 pr-2 text-center">
                                <a href="book_files.php?book_id=<?= $b['id'] ?>" class="text-indigo-600 hover:underline">Lihat</a>
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

<?php require_once '../includes/footer.php'; ?>