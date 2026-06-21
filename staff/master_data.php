<?php
require_once '../auth/check_session.php';
requireRole(['Staff']);
require_once '../config/database.php';

$pageTitle = 'Master Data Buku';
$tab = $_GET['tab'] ?? 'authors';
$allowedTabs = ['authors', 'publishers', 'categories'];
if (!in_array($tab, $allowedTabs)) $tab = 'authors';

$tableMap = [
    'authors'    => ['table' => 'authors',    'pk' => 'id', 'fields' => ['author_name' => 'Nama Penulis', 'bio' => 'Bio']],
    'publishers' => ['table' => 'publishers', 'pk' => 'id', 'fields' => ['publisher_name' => 'Nama Penerbit', 'address' => 'Alamat']],
    'categories' => ['table' => 'categories', 'pk' => 'id', 'fields' => ['category_name' => 'Nama Kategori']],
];
$current = $tableMap[$tab];
$table   = $current['table'];
$fields  = $current['fields'];

$error = '';
$success = '';

// ===== HANDLE CREATE / UPDATE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = $_POST['id'] ?? null;

    $colNames = array_keys($fields);
    $values = [];
    foreach ($colNames as $col) {
        $values[$col] = trim($_POST[$col] ?? '');
    }

    // validasi sederhana: field pertama wajib diisi
    if ($values[$colNames[0]] === '') {
        $error = 'Field utama tidak boleh kosong.';
    } else {
        if ($action === 'create') {
            $cols = implode(', ', $colNames);
            $placeholders = implode(', ', array_fill(0, count($colNames), '?'));
            $types = str_repeat('s', count($colNames));
            $stmt = $conn->prepare("INSERT INTO $table ($cols) VALUES ($placeholders)");
            $stmt->bind_param($types, ...array_values($values));
            if ($stmt->execute()) {
                $success = 'Data berhasil ditambahkan.';
            } else {
                $error = 'Gagal menambahkan data: ' . $stmt->error;
            }
            $stmt->close();
        } elseif ($action === 'update' && $id) {
            $setClause = implode(' = ?, ', $colNames) . ' = ?';
            $types = str_repeat('s', count($colNames)) . 'i';
            $params = array_values($values);
            $params[] = $id;
            $stmt = $conn->prepare("UPDATE $table SET $setClause WHERE id = ?");
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                $success = 'Data berhasil diperbarui.';
            } else {
                $error = 'Gagal memperbarui data: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// ===== HANDLE DELETE =====
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $success = 'Data berhasil dihapus.';
    } else {
        // kemungkinan FK constraint (masih dipakai di book_authors/book_categories)
        $error = 'Gagal menghapus: data masih digunakan oleh buku lain.';
    }
    $stmt->close();
}

// ===== HANDLE EDIT MODE (ambil data utk form) =====
$editData = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ===== AMBIL SEMUA DATA UNTUK TABEL =====
$result = $conn->query("SELECT * FROM $table ORDER BY id ASC");

require_once '../includes/header.php';
?>

<div class="p-6">
    <h1 class="text-xl font-semibold text-gray-800 mb-4">Master Data Buku</h1>

    <!-- TAB NAVIGATION -->
    <div class="flex gap-2 mb-6 border-b border-gray-200">
        <?php foreach ($tableMap as $key => $info): ?>
            <a href="?tab=<?= $key ?>"
               class="px-4 py-2 text-sm font-medium <?= $tab === $key ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500 hover:text-indigo-600' ?>">
                <?= $key === 'authors' ? 'Penulis' : ($key === 'publishers' ? 'Penerbit' : 'Kategori') ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($error): ?>
        <div class="mb-4 px-4 py-2 bg-red-100 text-red-700 rounded text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded text-sm"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- FORM TAMBAH / EDIT -->
        <div class="md:col-span-1 bg-white p-4 rounded shadow-sm border border-gray-100">
            <h2 class="font-medium text-gray-700 mb-3"><?= $editData ? 'Edit Data' : 'Tambah Data Baru' ?></h2>
            <form method="POST" action="?tab=<?= $tab ?>">
                <input type="hidden" name="action" value="<?= $editData ? 'update' : 'create' ?>">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                <?php endif; ?>

                <?php foreach ($fields as $col => $label): ?>
                    <div class="mb-3">
                        <label class="block text-xs text-gray-500 mb-1"><?= $label ?></label>
                        <?php if ($col === 'bio' || $col === 'address'): ?>
                            <textarea name="<?= $col ?>" rows="3"
                                class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"><?= htmlspecialchars($editData[$col] ?? '') ?></textarea>
                        <?php else: ?>
                            <input type="text" name="<?= $col ?>" value="<?= htmlspecialchars($editData[$col] ?? '') ?>"
                                class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div class="flex gap-2">
                    <button type="submit"
                        class="bg-indigo-600 text-white text-sm px-4 py-1.5 rounded hover:bg-indigo-700">
                        <?= $editData ? 'Update' : 'Simpan' ?>
                    </button>
                    <?php if ($editData): ?>
                        <a href="?tab=<?= $tab ?>" class="text-sm px-4 py-1.5 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- TABEL DATA -->
        <div class="md:col-span-2 bg-white p-4 rounded shadow-sm border border-gray-100 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-200">
                        <th class="py-2 pr-2">#</th>
                        <?php foreach ($fields as $label): ?>
                            <th class="py-2 pr-2"><?= $label ?></th>
                        <?php endforeach; ?>
                        <th class="py-2 pr-2 text-right">Aksi</th>
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
                                <td class="py-2 pr-2 text-right whitespace-nowrap">
                                    <a href="?tab=<?= $tab ?>&edit=<?= $row['id'] ?>" class="text-indigo-600 hover:underline mr-3">Edit</a>
                                    <a href="?tab=<?= $tab ?>&delete=<?= $row['id'] ?>"
                                       onclick="return confirm('Yakin hapus data ini?')"
                                       class="text-red-600 hover:underline">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="<?= count($fields) + 2 ?>" class="py-4 text-center text-gray-400">Belum ada data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>