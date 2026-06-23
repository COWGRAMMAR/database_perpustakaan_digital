<?php
require_once '../auth/check_session.php';
requireRole(['Staff']);
require_once '../config/database.php';

$pageTitle = 'Data Pembaca';

// Detail anggota (kalau ada parameter id)
$detail = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT mp.*, u.username, u.email, u.is_active, u.created_at
                             FROM member_profiles mp
                             JOIN users u ON mp.user_id = u.id
                             WHERE mp.id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $detail = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// List semua anggota
$search = trim($_GET['search'] ?? '');
$where = '';
if ($search !== '') {
    $esc = $conn->real_escape_string($search);
    $where = "WHERE mp.full_name LIKE '%$esc%' OR mp.member_number LIKE '%$esc%'";
}

$sql = "SELECT mp.id, mp.full_name, mp.member_number, mp.membership_type, mp.phone_number, u.email, u.is_active
        FROM member_profiles mp
        JOIN users u ON mp.user_id = u.id
        $where
        ORDER BY mp.full_name ASC";
$members = $conn->query($sql);

require_once '../includes/header.php';
?>

<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Data Pembaca</h1>

    <?php if ($detail): ?>
        <div class="bg-white shadow rounded p-5 mb-5">
            <div class="flex justify-between items-start mb-3">
                <h2 class="font-semibold text-lg">Detail Anggota</h2>
                <a href="members.php" class="text-sm text-blue-600">&larr; Kembali ke daftar</a>
            </div>
            <table class="text-sm w-full">
                <tr class="border-t"><td class="p-2 font-medium w-48">Nama Lengkap</td><td class="p-2"><?= htmlspecialchars($detail['full_name']) ?></td></tr>
                <tr class="border-t"><td class="p-2 font-medium">No. Anggota</td><td class="p-2"><?= htmlspecialchars($detail['member_number']) ?></td></tr>
                <tr class="border-t"><td class="p-2 font-medium">Tipe Keanggotaan</td><td class="p-2">
                    <span class="px-2 py-1 rounded text-xs <?= $detail['membership_type']==='Premium' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700' ?>">
                        <?= $detail['membership_type'] ?>
                    </span>
                </td></tr>
                <tr class="border-t"><td class="p-2 font-medium">No. Telepon</td><td class="p-2"><?= htmlspecialchars($detail['phone_number']) ?></td></tr>
                <tr class="border-t"><td class="p-2 font-medium">Email</td><td class="p-2"><?= htmlspecialchars($detail['email']) ?></td></tr>
                <tr class="border-t"><td class="p-2 font-medium">Username</td><td class="p-2"><?= htmlspecialchars($detail['username']) ?></td></tr>
                <tr class="border-t"><td class="p-2 font-medium">Status Akun</td><td class="p-2">
                    <span class="px-2 py-1 rounded text-xs <?= $detail['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $detail['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                </td></tr>
                <tr class="border-t"><td class="p-2 font-medium">Bergabung Sejak</td><td class="p-2"><?= $detail['created_at'] ?></td></tr>
            </table>
        </div>
    <?php endif; ?>

    <form method="GET" class="mb-4 flex gap-2">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
               placeholder="Cari nama atau no. anggota..." class="border rounded p-2 flex-1">
        <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm">Cari</button>
        <?php if ($search): ?>
            <a href="members.php" class="px-4 py-2 rounded bg-gray-200 text-sm">Reset</a>
        <?php endif; ?>
    </form>

    <table class="w-full bg-white shadow rounded text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Nama</th>
                <th class="p-2 text-left">No. Anggota</th>
                <th class="p-2 text-left">Tipe</th>
                <th class="p-2 text-left">Telepon</th>
                <th class="p-2 text-center">Status</th>
                <th class="p-2 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($r = $members->fetch_assoc()): ?>
            <tr class="border-t">
                <td class="p-2"><?= htmlspecialchars($r['full_name']) ?></td>
                <td class="p-2"><?= htmlspecialchars($r['member_number']) ?></td>
                <td class="p-2">
                    <span class="px-2 py-1 rounded text-xs <?= $r['membership_type']==='Premium' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700' ?>">
                        <?= $r['membership_type'] ?>
                    </span>
                </td>
                <td class="p-2"><?= htmlspecialchars($r['phone_number']) ?></td>
                <td class="p-2 text-center">
                    <span class="px-2 py-1 rounded text-xs <?= $r['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $r['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                </td>
                <td class="p-2 text-center">
                    <a href="members.php?id=<?= $r['id'] ?>" class="text-blue-600 text-xs">Lihat Detail</a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php if ($members->num_rows === 0): ?>
            <tr><td colspan="6" class="p-4 text-center text-gray-500">Tidak ada data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>