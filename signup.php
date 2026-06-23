<?php
// signup.php
require_once 'auth/check_session.php';
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    redirectToDashboard();
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';
    $full_name  = trim($_POST['full_name'] ?? '');
    $phone      = trim($_POST['phone_number'] ?? '');
    $address    = trim($_POST['address'] ?? '');

    if ($username === '' || $email === '' || $password === '' || $full_name === '') {
        $error = 'Mohon lengkapi semua field wajib (username, email, password, nama lengkap).';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        // Cek username / email sudah dipakai atau belum
        $checkSql = "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1";
        $checkStmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, 'ss', $username, $email);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);

        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            $error = 'Username atau email sudah digunakan.';
        } else {
            mysqli_begin_transaction($conn);
            try {
                // 1. Insert ke users
                $hashedPassword = md5($password);
                $sql1 = "INSERT INTO users (username, email, password, is_active, created_at)
                         VALUES (?, ?, ?, 1, NOW())";
                $stmt1 = mysqli_prepare($conn, $sql1);
                mysqli_stmt_bind_param($stmt1, 'sss', $username, $email, $hashedPassword);
                mysqli_stmt_execute($stmt1);
                $userId = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt1);

                // 2. Ambil role_id untuk 'Pembaca'
                $roleResult = mysqli_query($conn, "SELECT id FROM roles WHERE role_name = 'Pembaca' LIMIT 1");
                $roleRow = mysqli_fetch_assoc($roleResult);
                if (!$roleRow) {
                    throw new Exception('Role Pembaca belum ada di tabel roles.');
                }
                $roleId = $roleRow['id'];

                // 3. Insert ke user_roles
                $sql2 = "INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)";
                $stmt2 = mysqli_prepare($conn, $sql2);
                mysqli_stmt_bind_param($stmt2, 'ii', $userId, $roleId);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);

                // 4. Insert ke member_profiles (member_number sementara, di-update setelah tahu id)
                $sql3 = "INSERT INTO member_profiles (user_id, member_number, full_name, address, phone_number, membership_type)
                         VALUES (?, '-', ?, ?, ?, 'Free')";
                $stmt3 = mysqli_prepare($conn, $sql3);
                mysqli_stmt_bind_param($stmt3, 'isss', $userId, $full_name, $address, $phone);
                mysqli_stmt_execute($stmt3);
                $memberId = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt3);

                // 5. Generate member_number, contoh: MBR-00001
                $memberNumber = 'MBR-' . str_pad($memberId, 5, '0', STR_PAD_LEFT);
                $sql4 = "UPDATE member_profiles SET member_number = ? WHERE id = ?";
                $stmt4 = mysqli_prepare($conn, $sql4);
                mysqli_stmt_bind_param($stmt4, 'si', $memberNumber, $memberId);
                mysqli_stmt_execute($stmt4);
                mysqli_stmt_close($stmt4);

                mysqli_commit($conn);
                $success = true;
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = 'Gagal mendaftar: ' . $e->getMessage();
            }
        }
        mysqli_stmt_close($checkStmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Perpustakaan Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center py-10">

    <div class="bg-white w-full max-w-md p-8 rounded-xl shadow-md">
        <h1 class="text-2xl font-bold text-gray-800 mb-1 text-center">Daftar Akun</h1>
        <p class="text-gray-500 text-sm mb-6 text-center">Akun baru akan terdaftar sebagai Pembaca</p>

        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 text-sm px-4 py-3 rounded-md mb-4">
                Pendaftaran berhasil! Silakan
                <a href="login.php" class="underline font-medium">login di sini</a>.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 text-sm px-4 py-2 rounded-md mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="signup.php" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                <input type="text" name="full_name" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                <input type="text" name="username" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input type="email" name="email" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                <input type="text" name="phone_number"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?= htmlspecialchars($_POST['phone_number'] ?? '') ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <textarea name="address" rows="2"
                          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password *</label>
                <input type="password" name="confirm_password" required minlength="6"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-md transition">
                Daftar
            </button>
        </form>
        <?php endif; ?>

        <p class="text-sm text-gray-500 text-center mt-6">
            Sudah punya akun?
            <a href="login.php" class="text-blue-600 hover:underline">Masuk di sini</a>
        </p>
    </div>

</body>
</html>
