<?php
require_once '../auth/check_session.php';
requireRole(['Admin']);
require_once '../config/database.php';

$pageTitle = 'Manajemen Staff';

$search = trim($_GET['q'] ?? '');
$searchClause = '';
if ($search !== '') {
    $esc = $conn->real_escape_string($search);
    $searchClause = "AND (sp.full_name LIKE '%$esc%' OR sp.staff_number LIKE '%$esc%' OR u.username LIKE '%$esc%')";
}

$staffList = $conn->query("
    SELECT sp.*, u.username, u.email, u.is_active, u.created_at
    FROM staff_profiles sp
    JOIN users u ON sp.user_id = u.id
    WHERE 1=1 $searchClause
    ORDER BY sp.id DESC
");

// detail view (klik salah satu staff)
$detail = null;
if (isset($_GET['detail'])) {
    $id = (int) $_GET['detail'];
    $stmt = $conn->prepare("
        SELECT sp.*, u.username, u.email, u.is_active, u.created_at
        FROM staff_profiles sp
        JOIN users u ON sp.user_id = u.id
        WHERE sp.id = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $detail = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

require_once '../includes/header.php';
?>

<div class="p-6">
    <h1 class="text-xl font-semibold text-gray-800 mb-1">Manajemen Staff</h1>
    <p class="text-sm text-gray-500 mb-4">Daftar staff (read-only). Untuk menambah/mengedit/menghapus, gunakan menu Manajemen User.</p>

    <div class="grid grid-cols-1 <?= $detail ? 'lg:grid-cols-3' : '' ?> gap-6">

        <!-- LIST -->
        <div class="<?= $detail ? 'lg:col-span-2' : '' ?> bg-white p-4 rounded shadow-sm border border-gray-100 overflow-x-auto">
            <form method="GET" class="mb-3">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama/NIP/username..."
                    class="border border-gray-300 rounded px-3 py-1.5 text-sm w-64">
                <button type="submit" class="bg-gray-100 text-gray-600 text-sm px-3 py-1.5 rounded ml-1 hover:bg-gray-200">Cari</button>
            </form>

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-200">
                        <th class="py-2 pr-2">NIP</th>
                        <th class="py-2 pr-2">Nama</th>
                        <th class="py-2 pr-2">Username</th>
                        <th class="py-2 pr-2">Status</th>
                        <th class="py-2 pr-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($staffList && $staffList->num_rows > 0): ?>
                        <?php while ($s = $staffList->fetch_assoc()): ?>
                            <tr class="border-b border-gray-100">
                                <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($s['staff_number']) ?></td>
                                <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($s['full_name']) ?></td>
                                <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($s['username']) ?></td>
                                <td class="py-2 pr-2">
                                    <span class="px-2 py-0.5 rounded text-xs <?= $s['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500' ?>">
                                        <?= $s['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td class="py-2 pr-2 text-right whitespace-nowrap">
                                    <a href="?detail=<?= $s['id'] ?>" class="text-indigo-600 hover:underline mr-3">Detail</a>
                                    <a href="users.php?edit=<?= $s['user_id'] ?>" class="text-gray-600 hover:underline">Edit</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="py-4 text-center text-gray-400">Belum ada data staff.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- DETAIL PANEL -->
        <?php if ($detail): ?>
        <div class="lg:col-span-1 bg-white p-4 rounded shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-medium text-gray-700">Detail Staff</h2>
                <a href="staff.php" class="text-xs text-gray-400 hover:text-gray-600">✕ Tutup</a>
            </div>
            <dl class="text-sm space-y-2">
                <div>
                    <dt class="text-xs text-gray-400">Nomor Induk Staff</dt>
                    <dd class="text-gray-700"><?= htmlspecialchars($detail['staff_number']) ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Nama Lengkap</dt>
                    <dd class="text-gray-700"><?= htmlspecialchars($detail['full_name']) ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">No. Telepon</dt>
                    <dd class="text-gray-700"><?= htmlspecialchars($detail['phone_number']) ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Username</dt>
                    <dd class="text-gray-700"><?= htmlspecialchars($detail['username']) ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Email</dt>
                    <dd class="text-gray-700"><?= htmlspecialchars($detail['email']) ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Status Akun</dt>
                    <dd>
                        <span class="px-2 py-0.5 rounded text-xs <?= $detail['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500' ?>">
                            <?= $detail['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Akun Dibuat</dt>
                    <dd class="text-gray-700"><?= htmlspecialchars($detail['created_at']) ?></dd>
                </div>
            </dl>
            <a href="users.php?edit=<?= $detail['user_id'] ?>"
               class="inline-block mt-4 bg-indigo-600 text-white text-sm px-4 py-1.5 rounded hover:bg-indigo-700">
                Edit di Manajemen User
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>