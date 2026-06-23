<?php
require_once '../auth/check_session.php';
requireRole(['Admin']);
require_once '../config/database.php';

$pageTitle = 'Master Data Buku (Monitoring)';
$tab = $_GET['tab'] ?? 'authors';
$allowedTabs = ['authors', 'publishers', 'categories'];
if (!in_array($tab, $allowedTabs)) $tab = 'authors';

$tableMap = [
    'authors'    => ['table' => 'authors',    'fields' => ['author_name' => 'Nama Penulis', 'bio' => 'Bio']],
    'publishers' => ['table' => 'publishers', 'fields' => ['publisher_name' => 'Nama Penerbit', 'address' => 'Alamat']],
    'categories' => ['table' => 'categories', 'fields' => ['category_name' => 'Nama Kategori']],
];
$current = $tableMap[$tab];
$table   = $current['table'];
$fields  = $current['fields'];

$result = $conn->query("SELECT * FROM $table ORDER BY id ASC");

require_once '../includes/header.php';
?>

<div class="p-6">
    <h1 class="text-xl font-semibold text-gray-800 mb-1">Master Data Buku</h1>
    <p class="text-sm text-gray-500 mb-4">Mode monitoring — Admin tidak dapat mengubah data ini. Perubahan dilakukan oleh Staff.</p>

    <div class="flex gap-2 mb-6 border-b border-gray-200">
        <?php foreach ($tableMap as $key => $info): ?>
            <a href="?tab=<?= $key ?>"
               class="px-4 py-2 text-sm font-medium <?= $tab === $key ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500 hover:text-indigo-600' ?>">
                <?= $key === 'authors' ? 'Penulis' : ($key === 'publishers' ? 'Penerbit' : 'Kategori') ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="bg-white p-4 rounded shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-200">
                    <th class="py-2 pr-2">#</th>
                    <?php foreach ($fields as $label): ?>
                        <th class="py-2 pr-2"><?= $label ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="border-b border-gray-100">
                            <td class="py-2 pr-2 text-gray-500"><?= $row['id'] ?></td>
                            <?php foreach (array_keys($fields) as $col): ?>
                                <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($row[$col] ?? '') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="<?= count($fields) + 1 ?>" class="py-4 text-center text-gray-400">Belum ada data.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>