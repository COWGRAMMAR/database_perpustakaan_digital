<?php
require_once '../auth/check_session.php';
requireRole(['Admin']);
require_once '../config/database.php';

$pageTitle = 'Manajemen User';
$error = '';
$success = '';

// ===== CREATE / UPDATE USER =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = $_POST['id'] ?? null;

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role_name = $_POST['role_name'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // field profil staff
    $staff_number = trim($_POST['staff_number'] ?? '');
    $staff_full_name = trim($_POST['staff_full_name'] ?? '');
    $staff_phone = trim($_POST['staff_phone'] ?? '');

    // field profil member
    $member_number = trim($_POST['member_number'] ?? '');
    $member_full_name = trim($_POST['member_full_name'] ?? '');
    $member_address = trim($_POST['member_address'] ?? '');
    $member_phone = trim($_POST['member_phone'] ?? '');
    $membership_type = $_POST['membership_type'] ?? 'Free';

    if ($username === '' || $email === '' || $role_name === '') {
        $error = 'Username, Email, dan Role wajib diisi.';
    } elseif ($action === 'create' && $password === '') {
        $error = 'Password wajib diisi untuk user baru.';
    } else {
        $conn->begin_transaction();
        try {
            if ($action === 'create') {
                $hashedPass = md5($password);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, is_active) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('sssi', $username, $email, $hashedPass, $is_active);
                $stmt->execute();
                $userId = $stmt->insert_id;
                $stmt->close();

                $roleRow = $conn->query("SELECT id FROM roles WHERE role_name = '" . $conn->real_escape_string($role_name) . "'")->fetch_assoc();
                $roleId = $roleRow['id'];
                $conn->query("INSERT INTO user_roles (user_id, role_id) VALUES ($userId, $roleId)");

                if ($role_name === 'Staff') {
                    $stmt = $conn->prepare("INSERT INTO staff_profiles (user_id, staff_number, full_name, phone_number) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param('isss', $userId, $staff_number, $staff_full_name, $staff_phone);
                    $stmt->execute();
                    $stmt->close();
                } elseif ($role_name === 'Pembaca') {
                    $stmt = $conn->prepare("INSERT INTO member_profiles (user_id, member_number, full_name, address, phone_number, membership_type) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param('isssss', $userId, $member_number, $member_full_name, $member_address, $member_phone, $membership_type);
                    $stmt->execute();
                    $stmt->close();
                }

                $conn->commit();
                $success = 'User berhasil ditambahkan.';
            } elseif ($action === 'update' && $id) {
                if ($password !== '') {
                    $hashedPass = md5($password);
                    $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=?, is_active=? WHERE id=?");
                    $stmt->bind_param('sssii', $username, $email, $hashedPass, $is_active, $id);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET username=?, email=?, is_active=? WHERE id=?");
                    $stmt->bind_param('ssii', $username, $email, $is_active, $id);
                }
                $stmt->execute();
                $stmt->close();

                // update role (hapus lalu insert ulang, asumsi 1 user = 1 role aktif)
                $conn->query("DELETE FROM user_roles WHERE user_id = $id");
                $roleRow = $conn->query("SELECT id FROM roles WHERE role_name = '" . $conn->real_escape_string($role_name) . "'")->fetch_assoc();
                $roleId = $roleRow['id'];
                $conn->query("INSERT INTO user_roles (user_id, role_id) VALUES ($id, $roleId)");

                if ($role_name === 'Staff') {
                    $exists = $conn->query("SELECT id FROM staff_profiles WHERE user_id = $id")->fetch_assoc();
                    if ($exists) {
                        $stmt = $conn->prepare("UPDATE staff_profiles SET staff_number=?, full_name=?, phone_number=? WHERE user_id=?");
                        $stmt->bind_param('sssi', $staff_number, $staff_full_name, $staff_phone, $id);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO staff_profiles (user_id, staff_number, full_name, phone_number) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param('isss', $id, $staff_number, $staff_full_name, $staff_phone);
                    }
                    $stmt->execute();
                    $stmt->close();
                } elseif ($role_name === 'Pembaca') {
                    $exists = $conn->query("SELECT id FROM member_profiles WHERE user_id = $id")->fetch_assoc();
                    if ($exists) {
                        $stmt = $conn->prepare("UPDATE member_profiles SET member_number=?, full_name=?, address=?, phone_number=?, membership_type=? WHERE user_id=?");
                        $stmt->bind_param('sssssi', $member_number, $member_full_name, $member_address, $member_phone, $membership_type, $id);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO member_profiles (user_id, member_number, full_name, address, phone_number, membership_type) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param('isssss', $id, $member_number, $member_full_name, $member_address, $member_phone, $membership_type);
                    }
                    $stmt->execute();
                    $stmt->close();
                }

                $conn->commit();
                $success = 'User berhasil diperbarui.';
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Gagal menyimpan data: ' . $e->getMessage();
        }
    }
}

// ===== TOGGLE STATUS AKTIF =====
if (isset($_GET['toggle_status'])) {
    $id = (int) $_GET['toggle_status'];
    $conn->query("UPDATE users SET is_active = NOT is_active WHERE id = $id");
    $success = 'Status user berhasil diubah.';
}

// ===== DELETE USER =====
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $success = 'User berhasil dihapus.';
    } else {
        $error = 'Gagal menghapus: user masih punya data transaksi terkait (peminjaman/pembayaran/dll).';
    }
    $stmt->close();
}

// ===== AMBIL DATA UNTUK EDIT =====
$editData = null;
$editRole = '';
$editProfile = [];
$staffProfile = [];
$memberProfile = [];
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $roleRow = $conn->query("
        SELECT r.role_name FROM user_roles ur
        JOIN roles r ON ur.role_id = r.id
        WHERE ur.user_id = $id LIMIT 1
    ")->fetch_assoc();
    $editRole = $roleRow['role_name'] ?? '';

    if ($editRole === 'Staff') {
        $staffProfile = $conn->query("SELECT * FROM staff_profiles WHERE user_id = $id")->fetch_assoc() ?? [];
    } elseif ($editRole === 'Pembaca') {
        $memberProfile = $conn->query("SELECT * FROM member_profiles WHERE user_id = $id")->fetch_assoc() ?? [];
    }
}

// ===== LIST USER =====
$search = trim($_GET['q'] ?? '');
$searchClause = '';
if ($search !== '') {
    $esc = $conn->real_escape_string($search);
    $searchClause = "WHERE u.username LIKE '%$esc%' OR u.email LIKE '%$esc%'";
}

$users = $conn->query("
    SELECT u.*, r.role_name,
        COALESCE(sp.full_name, mp.full_name) AS full_name
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    LEFT JOIN staff_profiles sp ON u.id = sp.user_id
    LEFT JOIN member_profiles mp ON u.id = mp.user_id
    $searchClause
    ORDER BY u.id DESC
");

require_once '../includes/header.php';
?>

<div class="p-6">
    <h1 class="text-xl font-semibold text-gray-800 mb-4">Manajemen User</h1>

    <?php if ($error): ?>
        <div class="mb-4 px-4 py-2 bg-red-100 text-red-700 rounded text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded text-sm"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- FORM -->
        <div class="lg:col-span-1 bg-white p-4 rounded shadow-sm border border-gray-100">
            <h2 class="font-medium text-gray-700 mb-3"><?= $editData ? 'Edit User' : 'Tambah User Baru' ?></h2>
            <form method="POST" action="users.php">
                <input type="hidden" name="action" value="<?= $editData ? 'update' : 'create' ?>">
                <?php if ($editData): ?><input type="hidden" name="id" value="<?= $editData['id'] ?>"><?php endif; ?>

                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($editData['username'] ?? '') ?>"
                        class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                </div>

                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($editData['email'] ?? '') ?>"
                        class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                </div>

                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">Password <?= $editData ? '(kosongkan jika tidak diubah)' : '' ?></label>
                    <input type="password" name="password" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                </div>

                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">Role</label>
                    <select name="role_name" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                        <?php foreach (['Admin', 'Staff', 'Pembaca'] as $r): ?>
                            <option value="<?= $r ?>" <?= $editRole === $r ? 'selected' : '' ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3 flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" <?= ($editData['is_active'] ?? 1) ? 'checked' : '' ?>>
                    <label for="is_active" class="text-sm text-gray-600">Akun Aktif</label>
                </div>

                <hr class="my-3 border-gray-200">
                <p class="text-xs text-gray-400 mb-3">Isi salah satu bagian di bawah sesuai role yang dipilih (field role lain akan diabaikan)</p>

                <p class="text-xs font-medium text-gray-500 mb-2">Profil Staff</p>
                <div class="mb-2">
                    <input type="text" name="staff_number" placeholder="Nomor Induk Staff"
                        value="<?= htmlspecialchars($staffProfile['staff_number'] ?? '') ?>"
                        class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm mb-2">
                    <input type="text" name="staff_full_name" placeholder="Nama Lengkap Staff"
                        value="<?= htmlspecialchars($staffProfile['full_name'] ?? '') ?>"
                        class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm mb-2">
                    <input type="text" name="staff_phone" placeholder="No. Telepon Staff"
                        value="<?= htmlspecialchars($staffProfile['phone_number'] ?? '') ?>"
                        class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                </div>

                <hr class="my-3 border-gray-200">
                <p class="text-xs font-medium text-gray-500 mb-2">Profil Pembaca</p>
                <div class="mb-2">
                    <input type="text" name="member_number" placeholder="Nomor Kartu Anggota"
                        value="<?= htmlspecialchars($memberProfile['member_number'] ?? '') ?>"
                        class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm mb-2">
                    <input type="text" name="member_full_name" placeholder="Nama Lengkap Pembaca"
                        value="<?= htmlspecialchars($memberProfile['full_name'] ?? '') ?>"
                        class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm mb-2">
                    <textarea name="member_address" placeholder="Alamat" rows="2"
                        class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm mb-2"><?= htmlspecialchars($memberProfile['address'] ?? '') ?></textarea>
                    <input type="text" name="member_phone" placeholder="No. Telepon Pembaca"
                        value="<?= htmlspecialchars($memberProfile['phone_number'] ?? '') ?>"
                        class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm mb-2">
                    <select name="membership_type" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                        <?php foreach (['Free', 'Premium'] as $mt): ?>
                            <option value="<?= $mt ?>" <?= ($memberProfile['membership_type'] ?? '') === $mt ? 'selected' : '' ?>><?= $mt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex gap-2 mt-4">
                    <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-1.5 rounded hover:bg-indigo-700">
                        <?= $editData ? 'Update' : 'Simpan' ?>
                    </button>
                    <?php if ($editData): ?>
                        <a href="users.php" class="text-sm px-4 py-1.5 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- LIST -->
        <div class="lg:col-span-2 bg-white p-4 rounded shadow-sm border border-gray-100 overflow-x-auto">
            <form method="GET" class="mb-3">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari username/email..."
                    class="border border-gray-300 rounded px-3 py-1.5 text-sm w-64">
                <button type="submit" class="bg-gray-100 text-gray-600 text-sm px-3 py-1.5 rounded ml-1 hover:bg-gray-200">Cari</button>
            </form>

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-200">
                        <th class="py-2 pr-2">Username</th>
                        <th class="py-2 pr-2">Email</th>
                        <th class="py-2 pr-2">Nama</th>
                        <th class="py-2 pr-2">Role</th>
                        <th class="py-2 pr-2">Status</th>
                        <th class="py-2 pr-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users && $users->num_rows > 0): ?>
                        <?php while ($u = $users->fetch_assoc()): ?>
                            <tr class="border-b border-gray-100">
                                <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($u['username']) ?></td>
                                <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($u['email']) ?></td>
                                <td class="py-2 pr-2 text-gray-700"><?= htmlspecialchars($u['full_name'] ?? '-') ?></td>
                                <td class="py-2 pr-2">
                                    <span class="px-2 py-0.5 rounded text-xs bg-indigo-100 text-indigo-700"><?= htmlspecialchars($u['role_name'] ?? '-') ?></span>
                                </td>
                                <td class="py-2 pr-2">
                                    <a href="?toggle_status=<?= $u['id'] ?>"
                                       class="px-2 py-0.5 rounded text-xs <?= $u['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500' ?>">
                                        <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                    </a>
                                </td>
                                <td class="py-2 pr-2 text-right whitespace-nowrap">
                                    <a href="?edit=<?= $u['id'] ?>" class="text-indigo-600 hover:underline mr-3">Edit</a>
                                    <a href="?delete=<?= $u['id'] ?>" onclick="return confirm('Yakin hapus user ini?')" class="text-red-600 hover:underline">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="py-4 text-center text-gray-400">Belum ada user.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>